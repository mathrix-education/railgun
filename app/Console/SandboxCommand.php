<?php

declare(strict_types=1);

namespace Railgun\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Railgun\Models\Course;

/**
 * Dummy Command which is only used to test behavioral code.
 *
 * @codeCoverageIgnore
 */
class SandboxCommand extends Command
{
    /**
     * @var string $signature The command signature.
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    protected $signature = 'sandbox';

    public function handle(): void
    {
        // Implement sandbox command
        $model = Course::findOrFail(217);
        $sql   = DB::table('clones')
            ->where('type', '=', resolveMorph($model))
            ->where(static function ($q) use ($model) {

                $q->where('source_id', '=', $model->getKey())
                    ->orWhere('destination_id', '=', $model->getKey());
            })
            ->toSql();
        dd($sql);
    }
}
