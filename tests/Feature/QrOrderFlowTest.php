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
}
