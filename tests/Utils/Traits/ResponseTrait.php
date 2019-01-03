<?php
namespace Tests\Utils\Traits;

use Symfony\Component\HttpFoundation\Response;

/**
 * Trait ResponseTrait.
 *
 * @author    Mathieu Bour <mathieu@mathrix.fr>
 * @author    Jérémie Levain <munezero999@live.fr>
 * @version   4.0.3
 * @copyright Mathrix Education SA
 * @package   Tests\Utils\Traits
 */
trait ResponseTrait
{
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
}
