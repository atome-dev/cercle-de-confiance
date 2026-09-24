<?php

namespace App\Console\Commands;

use App\Models\Thread;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:purge-archived-threads')]
#[Description('Delete dossiers archived for more than 18 months, per the RGPD retention policy')]
class PurgeArchivedThreads extends Command
{
    public const RETENTION_MONTHS = 18;

    /**
     * Execute the console command.
     *
     * Cascades to thread_messages, thread_key_grants and thread_reads via
     * their foreign keys (onDelete('cascade')) — deleting the thread is
     * enough to erase the whole dossier, encrypted content included.
     */
    public function handle(): void
    {
        $count = Thread::query()
            ->where('status', 'archive')
            ->whereNotNull('archived_at')
            ->where('archived_at', '<=', now()->subMonths(self::RETENTION_MONTHS))
            ->delete();

        $this->info("Purged {$count} dossier(s) archived for more than ".self::RETENTION_MONTHS.' months.');
    }
}
