<?php

namespace App\Traits;

trait GlobalVariables
{
    /**
     * Declared public variables for Equipment Handling Signatories - Job Order Form.
     */
    public const IT_DEPARTMENT = 1;
    public const APM_NSM_SM = 2;
    public const WIM = 3;
    public const SERVICE_TL = 4;
    public const SERVICE_HEAD_ENGINEER = 5;
    public const BILLING_WIM = 6;

    /** 
     * Work Order Status
     */
    public const ONGOING = 1;
    public const PARTIAL_COMPLETE = 2;
    public const COMPLETE = 3;
    public const DISAPPROVED = 4;
    public const RESCHEDULE = 5;

    /**
     * Job Order Form Report Number Prefix.
     */
    public const REPORT_NUMBER_PREFIX = 'JOF';
}
