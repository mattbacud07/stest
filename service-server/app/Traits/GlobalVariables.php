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
    public const INSTALLATION_TL = 7;
    public const INSTALLATION_ENGINEER = 8;
    public const EH_SIGNATORY_COMPLETE = 9;

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
    public const I_ONGOING = 1;
    public const I_PARTIAL_COMPLETE = 2;
    public const I_COMPLETE = 3;
    public const I_DISAPPROVED = 4;
    public const I_RESCHEDULE = 5;


    /**
     * Institution Area
     */
    public const LUZON = 'Luzon';
    public const VISAYAS = 'Visayas';
    public const MINDANAO = 'Mindanao';

    /**
     * Job Order Form Report Number Prefix.
     */
    public const REPORT_NUMBER_PREFIX = 'JOF';
}
