<?php

namespace Tests\Feature;

use App\Models\Inventory;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QrOrderFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_qr_order_is_created_unpaid_and_occupies_table(): void
    {
        $table     = $this->makeTable();
        $menu      = $this->makeMenuWithRecipe();
        $invBefore = (float) Inventory::first()->quantity_on_hand;

        $resp = $this->postJson(route('customer.order.store'), [
            'table_id' => $table->id,
            'items'    => [
                ['menu_id' => $menu->id, 'quantity' => 3, 'price' => 10000, 'subtotal' => 30000],
            ],
            'notes' => 'no chili',
        ]);

        $resp->assertOk()->assertJson(['success' => true]);

        $order = Order::firstOrFail();
        $this->assertSame('pending', $order->status);
        $this->assertNull($order->payment_date);
        $this->assertNull($order->users_id);
        $this->assertNull($order->users_roles_id);
        $this->assertSame(33300, (int) $order->total_amount);          // 30000 * 1.11
        $this->assertSame('QR Order', $order->customer_name);
        $this->assertSame('occupied', $table->fresh()->status);
        $this->assertEqualsWithDelta($invBefore, (float) Inventory::first()->quantity_on_hand, 0.001); // not deducted yet
        $resp->assertJsonPath('status_url', route('customer.order.status', $order->id));
    }

    public function test_status_page_and_state_endpoint_work(): void
    {
        $table = $this->makeTable();
        $menu  = $this->makeMenuWithRecipe();

        $this->postJson(route('customer.order.store'), [
            'table_id' => $table->id,
            'items'    => [['menu_id' => $menu->id, 'quantity' => 1, 'price' => 10000, 'subtotal' => 10000]],
        ])->assertOk();
        $order = Order::firstOrFail();

        $this->get(route('customer.order.status', $order->id))
            ->assertOk()
            ->assertSee('Order #'.str_pad((string) $order->id, 4, '0', STR_PAD_LEFT))
            ->assertSee('cashier'); // the "pay at the cashier" copy

        $this->getJson(route('customer.order.status.state', $order->id))
            ->assertOk()
            ->assertJsonPath('status', 'pending');
    }

    public function test_kasir_collects_payment_then_order_processes_and_stock_is_deducted(): void
    {
        $kasir = $this->makeUser('Kasir');
        $table = $this->makeTable();
        $menu  = $this->makeMenuWithRecipe();

        $this->postJson(route('customer.order.store'), [
            'table_id' => $table->id,
            'items'    => [['menu_id' => $menu->id, 'quantity' => 2, 'price' => 10000, 'subtotal' => 20000]],
        ])->assertOk();
        $order = Order::firstOrFail(); // total = round(20000 * 1.11) = 22200

        $resp = $this->actingAs($kasir)->patch(route('orders.collect-payment', $order->id), [
            'payment_type' => 'Cash',
            'amount_paid'  => 25000,
        ]);
        $resp->assertRedirect();
        $resp->assertSessionHas('success');

        $order->refresh();
        $this->assertSame('processing', $order->status);
        $this->assertSame('Cash', $order->payment_type);
        $this->assertEquals(25000, (int) $order->amount_paid);
        $this->assertNotNull($order->payment_date);
        $this->assertSame($kasir->id, (int) $order->users_id);
        $this->assertSame($kasir->roles_id, (int) $order->users_roles_id);
        $this->assertEqualsWithDelta(96.0, (float) Inventory::first()->quantity_on_hand, 0.001); // 100 - (2 per menu * 2 qty)
        $this->assertSame('occupied', $table->fresh()->status); // table stays occupied until the order is completed
    }

    public function test_collect_payment_is_rejected_when_amount_is_too_low(): void
    {
        $kasir = $this->makeUser('Kasir');
        $table = $this->makeTable();
        $menu  = $this->makeMenuWithRecipe();
        $this->postJson(route('customer.order.store'), [
            'table_id' => $table->id,
            'items'    => [['menu_id' => $menu->id, 'quantity' => 1, 'price' => 10000, 'subtotal' => 10000]],
        ])->assertOk();
        $order = Order::firstOrFail(); // total 11100

        $this->actingAs($kasir)
            ->patch(route('orders.collect-payment', $order->id), ['payment_type' => 'Cash', 'amount_paid' => 5000])
            ->assertSessionHasErrors('amount_paid');

        $this->assertSame('pending', $order->fresh()->status);
        $this->assertNull($order->fresh()->payment_date);
    }

    public function test_collect_payment_is_rejected_when_already_paid(): void
    {
        $kasir = $this->makeUser('Kasir');
        $table = $this->makeTable();
        $menu  = $this->makeMenuWithRecipe();
        $this->postJson(route('customer.order.store'), [
            'table_id' => $table->id,
            'items'    => [['menu_id' => $menu->id, 'quantity' => 1, 'price' => 10000, 'subtotal' => 10000]],
        ])->assertOk();
        $order = Order::firstOrFail();

        $this->actingAs($kasir)
            ->patch(route('orders.collect-payment', $order->id), ['payment_type' => 'Cash', 'amount_paid' => 20000])
            ->assertRedirect();
        $this->assertSame('processing', $order->fresh()->status);
        $stockAfterFirst = (float) Inventory::first()->quantity_on_hand;

        $this->actingAs($kasir)
            ->patch(route('orders.collect-payment', $order->id), ['payment_type' => 'Cash', 'amount_paid' => 20000])
            ->assertSessionHas('error');
        $this->assertEqualsWithDelta($stockAfterFirst, (float) Inventory::first()->quantity_on_hand, 0.001); // no double deduction
    }

    public function test_collect_payment_requires_kasir_or_admin(): void
    {
        $chef  = $this->makeUser('Chef');
        $table = $this->makeTable();
        $menu  = $this->makeMenuWithRecipe();
        $this->postJson(route('customer.order.store'), [
            'table_id' => $table->id,
            'items'    => [['menu_id' => $menu->id, 'quantity' => 1, 'price' => 10000, 'subtotal' => 10000]],
        ])->assertOk();
        $order = Order::firstOrFail();

        $this->actingAs($chef)
            ->patch(route('orders.collect-payment', $order->id), ['payment_type' => 'Cash', 'amount_paid' => 20000])
            ->assertForbidden();
        $this->assertSame('pending', $order->fresh()->status);
    }
}
