<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Services\AuditLogger;

class PruneExpiredVisitorsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'visitors:prune-expired {--days=30 : Number of retention days}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically deletes visitor data older than 30 days to enforce privacy and data lifecycle rules';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $days = (int) $this->option('days');
        $cutoffDate = Carbon::now()->subDays($days);

        $this->info("Scanning for visitor records older than {$days} days (created before {$cutoffDate->toDateTimeString()})...");

        $count = DB::table('visitors')
            ->where('created_at', '<', $cutoffDate)
            ->count();

        if ($count === 0) {
            $this->info("No expired visitor records found. Database is clean.");
            return 0;
        }

        $deleted = DB::table('visitors')
            ->where('created_at', '<', $cutoffDate)
            ->delete();

        AuditLogger::log(
            'VISITOR_DATA_LIFECYCLE_PRUNE',
            'Visitor Management',
            "Automated lifecycle cleanup: Permanently deleted {$deleted} visitor records older than {$days} days."
        );

        $this->info("Successfully deleted {$deleted} expired visitor records.");
        return 0;
    }
}
