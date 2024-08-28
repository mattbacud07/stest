<?php

namespace App\Services\PM;

use App\Models\PreventiveMaintenance\PM_Setting;
use App\Models\PreventiveMaintenance\PreventiveMaintenance as PM;
use App\Models\WorkOrder\EquipmentPeripherals;
use App\Traits\Maintenance;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class GeneratePMSched
{
    use Maintenance;

    /** Get all the Equipments for generation of PM Sched */
    public function getAllEquipmentPeripherals($service_id, $item_id)
    {
        $equipments = EquipmentPeripherals::where([
            'service_id' => $service_id,
            'category' => 'Equipment'
        ])->get();

        return $equipments;
    }

    public function calculateAllSchedFrequency($date_installed, $frequency, $expiration_date)
    {
        // $dateInstalled = Carbon::parse($date_installed);
        $scheduled_dates = [];
        $currentDate = $date_installed->copy();


        while ($currentDate->lessThanOrEqualTo($expiration_date)) {
            $scheduled_dates[] = $currentDate->copy();
            switch ($frequency) {
                case self::monthly:
                    $currentDate->addMonth();
                    break;
                case self::quarterly:
                    $currentDate->addMonths(3);
                    break;
                case self::semiAnnually:
                    $currentDate->addMonths(6);
                    break;
                case self::annually:
                    $currentDate->addYear();
                    break;
                default:
                    return null;
                    break;
            }
        }

        array_shift($scheduled_dates);

        $scheduledDates = implode(', ', array_map(function ($date) {
            return $date->format('Y-m-d');
        }, $scheduled_dates));

        return $scheduledDates;
    }
    public function calculateSchedFrequency($date_installed, $frequency)
    {
        $currentDate = $date_installed->copy();
        switch ($frequency) {
            case self::monthly:
                return $currentDate->addMonth();
                break;
            case self::quarterly:
                return $currentDate->addMonths(3);
                break;
            case self::semiAnnually:
                return $currentDate->addMonths(6);
                break;
            case self::annually:
                return $currentDate->addYear();
                break;
            default:
                return null;
                break;
        }
    }

    /** Calculate Next PM Schedule */
    public function calculateNextPMSchedule($date_installed, $equipments)
    {
        $expiration_date = Carbon::now()->endOfYear();
        $dateInstalled = Carbon::parse($date_installed);
        $date= [];
        foreach ($equipments as $equipment) {
            $frequency = $equipment['schedule'] ?? null;
            $scheduled_at = $this->calculateSchedFrequency($dateInstalled, $frequency);
            $allSched = $this->calculateAllSchedFrequency($dateInstalled, $frequency, $expiration_date);
            $date[] = [$dateInstalled];
            PM::create([
                'equipment_peripheral_id' => $equipment['id'],
                'service_id' => $equipment['service_id'],
                'list_scheduled' => $allSched,
                'scheduled_at' => $scheduled_at,
                'item_id' => $equipment['item_id'],
                'date_installed' => $date_installed,
                'status' => is_null($frequency) ? PM::NotSet : PM::Scheduled,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }

        // dd($date_installed, $dateInstalled, $scheduled_at, $allSched);
    }




}
