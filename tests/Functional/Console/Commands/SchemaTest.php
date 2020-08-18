<?php

declare(strict_types=1);

namespace Tests\Functional\Console\Commands;

use Illuminate\Support\Facades\Artisan;
use Tests\ApplicationTestCase;

class SchemaTest extends ApplicationTestCase
{
    public function testSchemaIntegrity(): void
    {
        $exit   = Artisan::call('lighthouse:validate-schema');
        $output = Artisan::output();
        $this->assertEquals(
            0,
            $exit,
            "Schema validation failed: lighthouse:validate-schema exited with code $exit\n$output",
        );
    }
}
