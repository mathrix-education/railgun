<?php

namespace App\Utils;

use App\Exceptions\Http400InvalidRequestException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Class Chainer.
 *
 * @author Mathrix Back-End Team
 *
 * @since 4.1.0
 *
 * @copyright Mathrix SA
 */
class Chainer
{
    private $request;
    private $subRequests;
    private $responseData = [];

    /**
     * Chainer constructor.
     *
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * @throws Http400InvalidRequestException
     *
     * @return JsonResponse
     */
    public function process()
    {
        $this->subRequests = $this->request->input("requests", []);

        foreach ($this->subRequests as $key => $subRequest) {
            $this->processIndividualRequest($key, $subRequest);
        }

        return new JsonResponse($this->responseData);
    }

    /**
     * @param string $key
     * @param array  $subRequest
     *
     * @throws Http400InvalidRequestException
     */
    private function processIndividualRequest(string $key, array $subRequest)
    {
        $request = Request::create(
            $this->resolve($subRequest["url"]),
            $subRequest["method"],
            $subRequest["data"] ?? []
        );

        $response = app()->dispatch($request);

        $this->responseData[$key] = json_decode($response->getContent());
    }

    /**
     * @param string $toParse
     *
     * @throws Http400InvalidRequestException
     *
     * @return mixed|string
     */
    private function resolve(string $toParse)
    {
        $matches = [];
        $result = preg_match("/\{[a-z\_]+.[a-z\_]+\}/", $toParse, $matches);

        if ($result === 0) {
            return $toParse;
        }

        foreach ($matches as $match) {
            $params = [];
            preg_match("/\{([a-z\_]+).([a-z\_]+)\}/", $match, $params);
            $responseKey = $params[1];
            $responseSubKey = $params[2];

            if (empty($this->responseData[$responseKey]->{$responseSubKey})) {
                throw new Http400InvalidRequestException(
                    "Unable to find the key $responseKey.$responseSubKey",
                    400,
                    null,
                    $this->responseData
                );
            }

            $toParse = str_replace($match, $this->responseData[$responseKey]->{$responseSubKey}, $toParse);
        }

        return $toParse;
    }
}
