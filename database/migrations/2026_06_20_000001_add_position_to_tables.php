<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tables', function (Blueprint $table) {
            // Grid cell on the floor plan. Null = not yet placed (shown in the "unplaced" tray).
            $table->integer('pos_x')->nullable()->after('status');
            $table->integer('pos_y')->nullable()->after('pos_x');
        });
    }

    public function down(): void
    {
        Schema::table('tables', function (Blueprint $table) {
            $table->dropColumn(['pos_x', 'pos_y']);
        });
    }
};
