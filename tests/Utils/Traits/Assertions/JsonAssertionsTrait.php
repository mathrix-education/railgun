<?php
namespace Tests\Utils\Traits\Assertions;

/**
 * Trait JsonAssertionsTrait.
 *
 * @author    Mathieu Bour <mathieu@mathrix.fr>
 * @author    Jérémie Levain <munezero999@live.fr>
 * @since     4.0.3
 * @copyright Mathrix Education SA
 * @package   Tests\Utils\Traits\Assertions
 */
trait JsonAssertionsTrait
{
    /**
     * Assert the response has the right error and status code.
     *
     * @param string $error  the error name
     * @param int    $status the status code
     */
    public function assertJsonError(string $error, int $status): void
    {
        $this->seeJsonContains(["error" => $error]); // Check for error
        $this->assertResponseStatus($status); // Check for code
    }

    /**
     * Assert the response has the right length.
     *
     * @param int $expectedLength
     */
    public function assertJsonLength(int $expectedLength): void
    {
        $content = json_decode($this->response->getContent(), true);
        $this->assertEquals($expectedLength, \count($content));
    }

    /**
     * Assert the response has the right length.
     *
     * @param int $minLength
     */
    public function assertJsonLengthMin(int $minLength): void
    {
        $content = json_decode($this->response->getContent(), true);
        $this->assertTrue($minLength <= \count($content));
    }

    /**
     * Assert the response has the right length.
     *
     * @param int $maxLength
     */
    public function assertJsonLengthMax(int $maxLength): void
    {
        $content = json_decode($this->response->getContent(), true);
        $this->assertTrue(
            $maxLength >= \count($content),
            sprintf(
                "Failing asserting that json item count %d is less or equal than %s",
                \count($content),
                $maxLength
            )
        );
    }

    /**
     * Assert the response has the keys.
     *
     * @param array $keys
     */
    public function assertJsonKeys(array $keys): void
    {
        $content = json_decode($this->response->getContent(), true);
        $contentKeys = array_keys($content);

        $testPassed = true;

        foreach ($keys as $key) {
            $testPassed = $testPassed && \in_array($key, $contentKeys, true);
        }

        $this->assertTrue($testPassed, "Failed asserting that\n" . json_encode($content, JSON_PRETTY_PRINT) .
            "\nhas keys " . json_encode($keys));
    }
}
