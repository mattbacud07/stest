<?php

namespace App\Console\Commands;

use App\Models\PreventiveMaintenance\PreventiveMaintenance as PM;
use Illuminate\Console\Command;

class PMCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:monitor-pm';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        /**Start Command here */
        PM::

        PM::where()->update($data);
    }
}
