<?php

declare(strict_types=1);

namespace Railgun\Buster\Commands;

/**
 * Initialize the migrations tables.
 */
class InitializationCommand extends V7Command
{
    /**
     * @var string $signature The command signature.
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    protected $signature = 'buster:initialization';

    public function handle(): void
    {
        $this->migrateTo('2020_01_17_040959_create_interactions_table');
    }
}
