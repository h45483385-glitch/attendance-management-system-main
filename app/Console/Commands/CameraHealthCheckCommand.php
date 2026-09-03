<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\CheckCameraHealthJob;

class CameraHealthCheckCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'camera:health-check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Dispatches the asynchronous job to check health of all cameras';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Dispatching Camera Health Check Job...');
        CheckCameraHealthJob::dispatch();
        $this->info('Job dispatched successfully.');
        
        return 0;
    }
}
