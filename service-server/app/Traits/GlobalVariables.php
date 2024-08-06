<?php

namespace App\Traits;

trait GlobalVariables
{
    /** Roles */
    public const adminRole = 10;
    public const approverRole = 'Approver';
    public const engineerRole = 'Engineer';

    /**
     * Declared public variables for Equipment Handling Signatories - Job Order Form.
     */
    public const IT_DEPARTMENT = 1;
    public const APM_NSM_SM = 2;
    public const WIM = 3;
    public const SERVICE_TL = 4;
    public const SERVICE_HEAD_ENGINEER = 5;
    public const BILLING_WIM = 6;
    public const OUTBOUND = 7;
    public const INSTALLATION_TL = 8;
    public const INSTALLATION_ENGINEER = 9;
    public const EH_SIGNATORY_COMPLETE = 15;

    /** 
     * Work Order Status
     */
    public const ONGOING = 1;
    public const PARTIAL_COMPLETE = 2;
    public const COMPLETE = 3;
    public const DISAPPROVED = 4;
    public const RESCHEDULE = 5;
    public const INSTALLING = 6;



    /** 
     * Internal Servicing Status
     */
    public const internalStat = [
        'Delegated' => 1,
        'Accepted' => 2,
        'Declined' => 3,
        'InProgress' => 4,
        'Completed' => 5,
        'Packed' => 6,
        // 'Endorsed' => 7,
        'ConfirmedByWIM' => 8,
    ];
        //  Ongoing = 1;
        //  I_PACKED_ENDORSED = 2;
        //  I_COMPLETE = 3;
        //  I_DISAPPROVED = 4;
        //  I_RESCHEDULE = 5;
    
// Delegated: The request has been assigned to a specific engineer.
// Accepted: The engineer has accepted the request and will start processing it.
// Declined: The engineer has declined the request, and it needs to be re-delegated to another engineer.
// In Progress: The engineer has accepted the request and is currently processing it.
// Completed: The engineer has finished processing the request and has confirmed its completion.
// Packed: The request has been packed and is ready to be endorsed to the WIM personnel.
// Endorsed to WIM: The request has been handed over to the WIM personnel.
// Confirmed by WIM: The WIM personnel have confirmed receipt and completion of the request.

    /**
     * Institution Area
     */
    public const LUZON = 'Luzon';
    public const VISAYAS = 'Visayas';
    public const MINDANAO = 'Mindanao';
    
    public function institutionAreaToWords($institution){
        switch($institution){
            case self::LUZON:
            case 'LUZON':
                return 1;
                break;
            case self::VISAYAS:
            case 'VISAYAS':
                return 2;
                break;
            case self::MINDANAO:
            case 'MINDANAO':
                return 3;
                break;
            default:
                return 0; // Handle default case if necessary
                break;
        }
    }
    

    /**
     * Job Order Form Report Number Prefix.
     */
    public const REPORT_NUMBER_PREFIX = 'JOF';
}
