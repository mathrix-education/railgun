<?php

declare(strict_types=1);

namespace Tests\GraphQL;

use Illuminate\Support\Facades\Artisan;
use Tests\BaseTestCase;

/**
 *
 */
class SchemaTest extends BaseTestCase
{
    public function testSchemaIntegrity()
    {
        $exit   = Artisan::call('lighthouse:validate-schema');
        $output = Artisan::output();
        $this->assertEquals(0, $exit, <<<MESSAGE
        Schema validation failed: lighthouse:validate-schema exited with code $exit
        $output
        MESSAGE
        );
    }
}
