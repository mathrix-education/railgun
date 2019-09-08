<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Mathrix\Lumen\Zero\Console\Commands\BaseCommand;

/**
 * Class CleanCacheCommand.
 * Purge outdated cache.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA
 * @since 6.0.0
 */
class CleanCacheCommand extends BaseCommand
{
    protected $signature = "cache:clean";
    protected $description = "Purge expired cache";


    public function handle()
    {
        Log::info("Starting cache cleaning");
        $cacheItemCount = DB::table("cache")
            ->where("expiration", "<", Carbon::now()->getTimestamp())
            ->delete();
        Log::info("$cacheItemCount cache item were purged");
    }
}
