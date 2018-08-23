<?php

namespace Tests\Utils\Traits;

use Symfony\Component\HttpFoundation\Response;

/**
 * Trait ResponseTrait.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @copyright Mathrix Education SA
 *
 * @version   4.0.3
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
