<?php

namespace Database\Seeders;

use App\Models\InternalExternalRequest as ModelsInternalExternalRequest;
use Illuminate\Database\Seeder;

class InternalExternalRequest extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // External
        ModelsInternalExternalRequest::create([
            'name' => 'For Demonstration',
        ]);
        ModelsInternalExternalRequest::create([
            'name' => 'Reagent Tie-up',
        ]);
        ModelsInternalExternalRequest::create([
            'name' => 'Purchased',
        ]);
        ModelsInternalExternalRequest::create([
            'name' => 'Shipment/Delivery',
        ]);
        ModelsInternalExternalRequest::create([
            'name' => 'Service Unit',
        ]);

        // Internal
        ModelsInternalExternalRequest::create([
            'name' => 'For Corrective',
        ]);
        ModelsInternalExternalRequest::create([
            'name' => 'For Refurbishment',
        ]);
        ModelsInternalExternalRequest::create([
            'name' => 'For Quality Control',
        ]);
        ModelsInternalExternalRequest::create([
            'name' => 'Training Purposes',
        ]);
        ModelsInternalExternalRequest::create([
            'name' => 'For Disposal',
        ]);

        // Others
        ModelsInternalExternalRequest::create([
            'name' => 'Others',
        ]);
    }
}
