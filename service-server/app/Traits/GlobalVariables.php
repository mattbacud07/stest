<?php

namespace App\Traits;

trait GlobalVariables
{

    /** Main Statuses */
    public const PENDING = 'Pending';
    public const REJECTED = 'Rejected';
    public const COMPLETED = 'Completed';
    public const APPROVED = 'Approved';
    public const DISAPPROVED = 'Disapproved';
    public const DECLINED = 'Declined';
    public const ACCEPTED = 'Accepted';
    public const AGREED = 'Agreed';
    public const IN_PROGRESS = 'In Progress';
    public const CANCELLED = 'Cancelled';
    public const DELEGATED = 'Delegated';
    public const INSTALLING = 'Installing';
    public const UNINSTALLING = 'Uninstalling';
    public const RETURNEDHEAD = 'Returned to Head';
    public const SENTWAREHOUSE = 'Sent to Warehouse';





    /** Type of Request use in equipment_peripherals table*/
    public const EH = 'eh';
    public const PULLOUT = 'pullout';
    public const IS = 'is';
    public const PM = 'pm';
    public const CM = 'cm';
    

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
    


    /**
     * Job Order Form Report Number Prefix.
     */
    public const REPORT_NUMBER_PREFIX = 'JOF';
    public const REPORT_NUMBER_SR = 'SR';
    public const REPORT_NUMBER_PR = 'PR';


}
