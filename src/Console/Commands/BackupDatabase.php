<?php

namespace DatabaseBackupManager\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class BackupDatabase extends Command
{
    protected $signature = 'backup:database {--format=sql}';
    protected $description = 'Backup the database in specified format';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $format = $this->option('format');
        $database = config('database.connections.mysql.database');
        $filename = $database . '-' . date('Y-m-d-H-i-s') . '.' . $format;
        $filepath = storage_path('app/backups/' . $filename);

        // Supported formats for backup
        $supportedFormats = ['sql', 'csv', 'json'];
        if (!in_array($format, $supportedFormats)) {
            $this->error('Unsupported format: ' . $format);
            return 1;
        }

        // DATABASE backup process
        if ($format === 'sql') {
            $this->backupAsSql($filepath);
        } elseif ($format === 'csv') {
            $this->backupAsCsv($filepath);
        } elseif ($format === 'json') {
            $this->backupAsJson($filepath);
        }

        $this->info('Database backup completed: ' . $filepath);
    }

    protected function backupAsSql($filepath)
    {
        // SQL backup creation process
        $command = "mysqldump --user=" . env('DB_USERNAME') . " --password=" . env('DB_PASSWORD') . " --host=" . env('DB_HOST') . " " . config('database.connections.mysql.database') . " > " . $filepath;
        $result = null;
        $returnVar = null;

        exec($command, $result, $returnVar);

        if ($returnVar !== 0) {
            $this->error('Error creating SQL backup: ' . implode("\n", $result));
            return 1;
        }
    }

    protected function backupAsCsv($filepath)
    {
        // CSV backup creation process
        $command = "mysqldump --tab=" . dirname($filepath) . " --fields-terminated-by=',' --fields-enclosed-by='\"' --fields-escaped-by='\\' --lines-terminated-by='\n' --user=" . env('DB_USERNAME') . " --password=" . env('DB_PASSWORD') . " --host=" . env('DB_HOST') . " " . config('database.connections.mysql.database');
        $result = null;
        $returnVar = null;

        exec($command, $result, $returnVar);

        if ($returnVar !== 0) {
            $this->error('Error creating CSV backup: ' . implode("\n", $result));
            return 1;
        }
    }

    protected function backupAsJson($filepath)
    {
        // JSON backup creation process
        $command = "mysqldump --user=" . env('DB_USERNAME') . " --password=" . env('DB_PASSWORD') . " --host=" . env('DB_HOST') . " --tab=" . dirname($filepath) . " --fields-terminated-by=',' --fields-enclosed-by='\"' --fields-escaped-by='\\' --lines-terminated-by='\n' " . config('database.connections.mysql.database');
        $result = null;
        $returnVar = null;

        exec($command, $result, $returnVar);

        if ($returnVar !== 0) {
            $this->error('Error creating JSON backup: ' . implode("\n", $result));
            return 1;
        }
    }
}