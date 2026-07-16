<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('floors', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        Schema::table('tables', function (Blueprint $table) {
            // Which floor the table is placed on. Null = unplaced (in the tray).
            $table->foreignId('floor_id')->nullable()->after('status')->constrained('floors');
        });

        // Existing layouts predate floors: move already-placed tables onto a default first floor.
        if (DB::table('tables')->whereNotNull('pos_x')->whereNotNull('pos_y')->exists()) {
            $floorId = DB::table('floors')->insertGetId([
                'name' => 'Lantai 1', 'created_at' => now(), 'updated_at' => now(),
            ]);
            DB::table('tables')->whereNotNull('pos_x')->whereNotNull('pos_y')
                ->update(['floor_id' => $floorId]);
        }
    }

    public function down(): void
    {
        Schema::table('tables', function (Blueprint $table) {
            $table->dropConstrainedForeignId('floor_id');
        });
        Schema::drop('floors');
    }
};
