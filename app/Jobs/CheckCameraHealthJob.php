<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\CameraHealthCheckService;
use Illuminate\Support\Facades\DB;

class CheckCameraHealthJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 120; // 2 minutes max
    public $tries = 3;

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(CameraHealthCheckService $healthCheckService)
    {
        $healthCheckService->checkAllCameras();
    }
}
