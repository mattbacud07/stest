<?php

namespace App\Services;

use App\Models\WorksDone;

class ActionsDoneService
{
    public function declare_actions_done($data)
    {
        // $data = WorksDone::insert($data);

        // return $data;
        // $inserted = [];

        foreach ($data as $record) {
            WorksDone::create($record); // Uses Eloquent and triggers events
        }
        return true;
    }
}
