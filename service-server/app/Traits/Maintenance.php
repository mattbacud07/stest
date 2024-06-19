<?php

namespace App\Traits;

trait Maintenance{
    /** 
 * Preventive Maintenance
 */
/** Work Type */
public const pm = 1;
public const cm = 2;

/** Status */
public const waiting_engineer = 1;
public const backlog = 2;
public const backjob = 3;
}