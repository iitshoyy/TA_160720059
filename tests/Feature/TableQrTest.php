<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TableQrTest extends TestCase
{
    use RefreshDatabase;

    public function test_generate_qr_renders_a_real_svg_not_the_placeholder(): void
    {
        $admin = $this->makeUser('Admin');
        $table = $this->makeTable();

        $resp = $this->actingAs($admin)->get(route('tables.qr', $table->id));

        $resp->assertOk();
        $resp->assertDontSee('QR code placeholder');                       // the old fake SVG marker is gone
        $resp->assertSee('<svg', false);                                    // a real generated QR
        $resp->assertSee(route('customer.order', ['tableId' => $table->id])); // pointing at the right URL
    }

    public function test_qr_sheet_lists_every_table(): void
    {
        $admin = $this->makeUser('Admin');
        $t1 = $this->makeTable();
        $t2 = $this->makeTable();

        $resp = $this->actingAs($admin)->get(route('tables.qr-sheet'));

        $resp->assertOk();
        $resp->assertSee($t1->name);
        $resp->assertSee($t2->name);
        $resp->assertSee('<svg', false);
    }
}
