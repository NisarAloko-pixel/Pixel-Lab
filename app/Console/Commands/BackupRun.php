<?php

namespace App\Console\Commands;

use App\Models\Setting;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class BackupRun extends Command
{
    /**
     * php artisan backup:run
     * php artisan backup:run --label=pre-restore
     */
    protected $signature = 'backup:run {--label=scheduled}';

    protected $description = 'Copy the live sqlite database into the configured backup folder.';

    public function handle(): int
    {
        $sourcePath = config('database.connections.sqlite.database');

        if (! $sourcePath || ! File::exists($sourcePath)) {
            $this->error('Database file not found at: '.$sourcePath);

            return self::FAILURE;
        }

        $backupPath = Setting::get('backup_path', '');

        if (! $backupPath) {
            $this->warn('No backup_path configured in Settings — skipping (nothing to do).');

            return self::SUCCESS;
        }

        if (! File::isDirectory($backupPath)) {
            File::makeDirectory($backupPath, 0755, true);
        }

        $label = $this->option('label');
        $filename = "pixel-backup-{$label}-".now()->format('Y-m-d_His').'.sqlite';
        $destination = rtrim($backupPath, '\\/').DIRECTORY_SEPARATOR.$filename;

        File::copy($sourcePath, $destination);

        $this->info("Backup saved to: {$destination}");

        return self::SUCCESS;
    }
}