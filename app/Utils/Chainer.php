<?php

namespace App\Utils;

use App\Exceptions\Http400InvalidRequestException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Class Chainer.
 *
 * @author    Mathrix Back-End Team
 *
 * @since     4.1.0
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

        $this->responseData[$key] = json_decode($response->getContent(), true);
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
        $result = preg_match_all("/\{(\w+\.[\.\w+]+\w+)\}/", $toParse, $matches);

        if ($result === 0) {
            return $toParse;
        } else {
            $keys = $matches[1];

            foreach ($keys as $key) {
                $toParse = str_replace("{" . $key . "}", $this->resolveParam($key), $toParse);
            }

            return $toParse;
        }
    }

    private function resolveParam(string $key)
    {
        $keys = explode(".", $key);
        $array = $this->responseData;

        foreach ($keys as $subKey) {
            if (!empty($array[$subKey])) {
                $array = $array[$subKey];
            } else {
                throw new Http400InvalidRequestException("Unable to find key `$key`.");
            }
        }

        return $array;
    }
}
