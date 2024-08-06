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

        $scheduledDates = implode(', ', array_map(function ($date) {
            return $date->format('Y-m-d');
        }, $scheduled_dates));

        return $scheduledDates;
    }
    public function calculateSchedFrequency($date_installed, $frequency)
    {
        switch ($frequency) {
            case self::monthly:
                return $date_installed->addMonth();
                break;
            case self::quarterly:
                return $date_installed->addMonths(3);
                break;
            case self::semiAnnually:
                return $date_installed->addMonths(6);
                break;
            case self::annually:
                return $date_installed->addYear();
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
        foreach ($equipments as $equipment) {
            $frequency = $equipment['schedule'] ?? null;
            $scheduled_at = $this->calculateSchedFrequency($dateInstalled, $frequency);
            $allSched = $this->calculateAllSchedFrequency($dateInstalled, $frequency, $expiration_date);
            PM::create([
                'service_id' => $equipment['service_id'],
                'list_scheduled' => $allSched,
                'scheduled_at' => $scheduled_at,
                'item_id' => $equipment['item_id'],
                // 'serial_number' => $equipment['serial_number'],
                'status' => is_null($frequency) ? PM::NotSet : PM::Scheduled,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }
    }

    /** Generate PM Sched */
    public function generatePMSchedule($service_id)
    {
        $get_equipments  = EquipmentPeripherals::select('equipment_peripherals.*', 'm.category')
            ->leftJoin(DB::connection('mysqlSecond')->getDatabaseName() . '.master_data as m', 'equipment_peripherals.item_id', '=', 'm.id')
            ->leftJoin('equipment_handling as e', 'equipment_peripherals.service_id', '=', 'e.id')
            ->where([
                'equipment_peripherals.service_id' => $service_id,
                'equipment_peripherals.category' => 'Equipment',
            ])
            ->get();
    }
}
