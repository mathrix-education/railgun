<?php
namespace Tests\Utils\Traits;

use Helmich\JsonAssert\JsonAssertions;
use PHPUnit\Framework\Constraint\IsType;
use Symfony\Component\HttpFoundation\Response;

/**
 * Trait ResponseTrait.
 *
 * @author    Mathieu Bour <mathieu@mathrix.fr>
 * @author    Jérémie Levain <munezero999@live.fr>
 * @since     4.0.3
 * @copyright Mathrix Education SA
 * @package   Tests\Utils\Traits
 */
trait ResponseTrait
{
    use JsonAssertions;

    /**
     * Get the response data.
     *
     * @return array|\stdClass|null
     */
    public function getResponseData()
    {
        if ($this->response instanceof Response) {
            $response = $this->response->getContent();

            return json_decode($response);
        }
    }

    /**
     * @param int|null $page
     * @param int|null $perPage
     * @param int|null $total
     */
    public function assertIsPaginatedResponse(int $page = null, int $perPage = null, int $total = null)
    {
        $this->assertJsonDocumentMatches($this->response->getContent(), [
            "page" => $page ?? new IsType("integer"),
            "per_page" => $perPage ?? new IsType("integer"),
            "total" => $total ?? new IsType("integer"),
        ]);
    }
}
