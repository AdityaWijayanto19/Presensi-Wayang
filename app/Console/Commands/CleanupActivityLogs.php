<?php

namespace App\Console\Commands;

use App\Models\AdminActivityLog;
use Illuminate\Console\Command;

class CleanupActivityLogs extends Command
{
    protected $signature = 'activitylog:cleanup {--days=90 : Hapus log yang lebih lama dari N hari}';

    protected $description = 'Hapus log aktivitas administrator yang sudah kedaluwarsa';

    public function handle(): int
    {
        $days = max(1, (int) $this->option('days'));

        $deleted = AdminActivityLog::where('created_at', '<', now()->subDays($days))->delete();

        $this->info("Menghapus {$deleted} log aktivitas lebih tua dari {$days} hari.");

        return self::SUCCESS;
    }
}
