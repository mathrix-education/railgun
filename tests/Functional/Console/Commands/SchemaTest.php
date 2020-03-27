<?php

declare(strict_types=1);

namespace Tests\Functional\Console\Commands;

use Illuminate\Support\Facades\Artisan;
use Tests\BaseTestCase;

/**
 * @testdox [Functional] Schema
 */
class SchemaTest extends BaseTestCase
{
    /**
     * @testdox is validated with lighthouse:validate-schema command
     */
    public function testSchemaIntegrity(): void
    {
        $exit   = Artisan::call('lighthouse:validate-schema');
        $output = Artisan::output();
        $this->assertEquals(
            0,
            $exit,
            "Schema validation failed: lighthouse:validate-schema exited with code $exit\n$output"
        );
    }
}
