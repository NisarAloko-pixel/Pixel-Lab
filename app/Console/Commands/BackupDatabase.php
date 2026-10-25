<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class BackupDatabase extends Command
{
    protected $signature = 'backup:run {--path= : Override the configured backup path} {--label=scheduled : Filename label}';

    protected $description = 'Copy the SQLite database file to the configured backup folder.';

    public function handle(): int
    {
        $sourcePath = config('database.connections.sqlite.database');

        if (! $sourcePath || ! File::exists($sourcePath)) {
            $this->error('Source database file not found: '.$sourcePath);

            return self::FAILURE;
        }

        $destinationDir = $this->option('path') ?: \App\Models\Setting::get('backup_path', '');

        if (! $destinationDir) {
            $this->error('No backup path configured.');

            return self::FAILURE;
        }

        if (! File::isDirectory($destinationDir)) {
            File::makeDirectory($destinationDir, 0755, true);
        }

        $filename = 'pixel-backup-'.$this->option('label').'-'.now()->format('Y-m-d_His').'.sqlite';
        $destinationPath = rtrim($destinationDir, '\\/').DIRECTORY_SEPARATOR.$filename;

        File::copy($sourcePath, $destinationPath);

        // Retention: keep the 30 most recent backups in this folder, delete the rest.
        $files = collect(File::files($destinationDir))
            ->filter(fn ($f) => str_starts_with($f->getFilename(), 'pixel-backup-'))
            ->sortByDesc(fn ($f) => $f->getMTime())
            ->values();

        $files->skip(30)->each(fn ($f) => File::delete($f->getPathname()));

        $this->info('Backup created: '.$destinationPath);

        return self::SUCCESS;
    }
}