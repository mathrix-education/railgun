<?php

declare(strict_types=1);

namespace Railgun\Console;

use Illuminate\Console\Command;
use Railgun\Models\Chapter;

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
        $chapters = Chapter::query()
            ->whereHas('theme.grade', static fn($q) => $q->where('grades.slug', '=', 'postbac'))
            ->get();

        dd($chapters->toArray());
    }
}
