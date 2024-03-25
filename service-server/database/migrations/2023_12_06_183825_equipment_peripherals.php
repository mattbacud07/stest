<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('equipment_peripherals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('service_id');
            $table->unsignedBigInteger('item_id');
            $table->string('item_code')->nullable();
            $table->string('serial_number');
            $table->string('description')->nullable();
            $table->string('category')->nullable();
            $table->foreign('service_id')->references('id')->on('equipment_handling')->onDelete('CASCADE');
            $table->foreign('item_id')->references('id')->on(DB::connection('mysqlSecond')->getDatabaseName().'.master_data');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('equipment_peripherals');
    }
};
