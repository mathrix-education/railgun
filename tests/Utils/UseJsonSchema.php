<?php

namespace Tests\Utils;

use App\Services\OpenAPI\Parser;
use Helmich\JsonAssert\JsonAssertions;
use Laravel\Lumen\Testing\Concerns\MakesHttpRequests;
use PHPUnit\Framework\TestCase;

/**
 * Class UseJsonSchema.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0
 *
 * @mixin TestCase
 * @mixin JsonAssertions
 * @mixin MakesHttpRequests
 */
trait UseJsonSchema
{
    public function assertResponseMatchesJsonSchema(string $schemaName, bool $allowAdditionalProperties = false)
    {
        $parser = new Parser();
        $json = $this->response->getContent();
        $schema = $parser->getSchema($schemaName);

        if (
            $allowAdditionalProperties !== true ||
            !isset($schema["additionalProperties"]) ||
            $schema["additionalProperties"] !== true
        ) {
            $this->fail("JSON Schema additionalProperties value is not equal to false");
        }

        $this->assertJsonDocumentMatchesSchema($json, $schema);
    }
}
