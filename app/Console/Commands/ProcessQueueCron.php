<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ProcessQueueCron extends Command
{
    /**
     * The trailing {ignored?*} argument exists so that shell redirection tokens
     * survive being passed straight through as arguments. Hostinger runs cron
     * commands without a shell, so a line ending in ">> /dev/null 2>&1" hands
     * those three tokens to artisan; queue:work rejects them with "Too many
     * arguments" and the queue silently stops draining. Swallowing them here
     * keeps the cron entry working no matter how it is written.
     */
    protected $signature = 'queue:cron
                            {--max-time=50 : Seconds to keep processing before exiting}
                            {ignored?* : Discarded, absorbs stray tokens from the cron line}';

    protected $description = 'Drain the queue from a per-minute cron tick';

    public function handle(): int
    {
        $lockPath = storage_path('app/queue-cron.lock');
        $lock = fopen($lockPath, 'c');

        if ($lock === false) {
            $this->error('Unable to open queue cron lock file.');

            return self::FAILURE;
        }

        // Skip this tick rather than stacking a second worker on the same jobs
        // when the previous run is still going.
        if (! flock($lock, LOCK_EX | LOCK_NB)) {
            fclose($lock);

            return self::SUCCESS;
        }

        try {
            $this->call('queue:work', [
                '--stop-when-empty' => true,
                '--max-time' => (int) $this->option('max-time'),
                '--tries' => 3,
                '--backoff' => 10,
                '--sleep' => 3,
            ]);
        } finally {
            flock($lock, LOCK_UN);
            fclose($lock);
        }

        return self::SUCCESS;
    }
}
