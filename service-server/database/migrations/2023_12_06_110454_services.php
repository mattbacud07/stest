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
        Schema::create('equipment_handling', function(Blueprint $table){
            $table->id();
            $table->string('report_number')->unique();
            $table->string('requested_by');
            $table->string('institution');
            $table->string('address');
            $table->timestamp('proposed_delivery_date');
            $table->string('other_request_details');
            $table->string('external_request');
            $table->string('internal_request');
            $table->text('endorsement');
            $table->text('additional_remarks');
            $table->timestamps();
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
