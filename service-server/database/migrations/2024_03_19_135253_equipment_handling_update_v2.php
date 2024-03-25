<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('equipment_handling', function (Blueprint $table) {
            $table->removeColumn('other_request_details');
            $table->tinyInteger('occular')->nullable()->after('proposed_delivery_date');
            $table->tinyInteger('bypass')->nullable()->after('proposed_delivery_date');
            $table->tinyInteger('ship')->nullable()->after('proposed_delivery_date');
            $table->tinyInteger('attach_gate')->nullable()->after('proposed_delivery_date');
            $table->tinyInteger('with_contract')->nullable()->after('proposed_delivery_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('equipment_handling');
    }
};
