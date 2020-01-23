<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Purge outdated cache.
 */
class CacheCleanCommand extends Command
{
    /** @var string The command signature */
    protected $signature = 'cache:clean';
    /** @var string The command description */
    protected $description = 'Purge expired cache';

    /**
     * Clean the cache using the expiration date.
     */
    public function handle(): void
    {
        Log::info('Starting cache cleaning');
        $nowTimestamp   = Carbon::now()
                                ->getTimestamp();
        $cacheItemCount = DB::table('cache')
                            ->where('expiration', '<', $nowTimestamp)
                            ->delete();
        Log::info("$cacheItemCount cache item were purged");
    }
}
