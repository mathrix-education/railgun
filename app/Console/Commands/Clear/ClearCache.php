<?php
namespace App\Console\Commands\Clear;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

/**
 * Class ClearCache
 *
 * @author    Mathieu Bour <mathieu.tin.bour@ŋmail.com>
 * @package   App\Console\Commands\Clear
 * @since
 * @copyright Mathrix Education SA
 */
class ClearCache extends Command
{
    protected $signature = "clear:cache";

    public function handle()
    {
        Cache::clear();
    }
}
