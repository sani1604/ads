<?php
// app/Console/Commands/CleanupOldData.php

namespace App\Console\Commands;

use App\Models\ActivityLog;
use App\Models\CustomNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class CleanupOldData extends Command
{
    protected $signature = 'cleanup:old-data {--days=90 : Days to keep}';
    protected $description = 'Clean up old activity logs and notifications';

    public function handle()
    {
        $days = $this->option('days');

        $this->info("Cleaning up data older than {$days} days...");

        // Clean activity logs
        $activityCount = ActivityLog::where('created_at', '<', now()->subDays($days))->delete();
        $this->line("Deleted {$activityCount} activity logs.");

        // Clean read notifications
        $notificationCount = CustomNotification::where('is_read', true)
            ->where('created_at', '<', now()->subDays($days))
            ->delete();
        $this->line("Deleted {$notificationCount} read notifications.");

        // Clean temporary files
        $tempFiles = Storage::files('temp');
        $deletedFiles = 0;

        foreach ($tempFiles as $file) {
            if (Storage::lastModified($file) < now()->subDays(1)->timestamp) {
                Storage::delete($file);
                $deletedFiles++;
            }
        }
        $this->line("Deleted {$deletedFiles} temporary files.");

        $this->info('Cleanup completed.');
    }
}