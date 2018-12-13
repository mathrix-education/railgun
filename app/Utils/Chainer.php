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
    private $bearerToken;

    /**
     * Chainer constructor.
     *
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->bearerToken = $request->header("Authorization");
    }

    /**
     * @throws Http400InvalidRequestException
     *
     * @return JsonResponse
     */
    public function process()
    {
        $this->subRequests = $this->request->input("requests", []);

        $this->orderRequests();

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
        $request->headers->set("Authorization", $this->bearerToken);

        $response = app()->dispatch($request);

        $this->responseData[$key] = [
            "method" => $subRequest["method"],
            "url" => $this->resolve($subRequest["url"]),
            "body" => json_decode($response->getContent(), true)
        ];
    }

    private function orderRequests()
    {
        $keys = array_keys($this->subRequests);
        $table = array_fill_keys($keys, 1);

        foreach ($this->subRequests as $key => $subRequest) {
            $url = $subRequest["url"];

            $matches = [];
            $result = preg_match_all("/\{(\w+\.[\.\w+]+\w+)\}/", $url, $matches);

            if ($result !== 0) {
                $param = $matches[1][0];

                $depends = str_replace("{$param}", $param, $param);
                $depends = explode(".", $depends);
                $depends = $depends[0];

                $table[$depends] += $table[$key];
            }
        }

        uasort($this->subRequests, function($request1, $request2) use ($table) {
            $key1 = array_search($request1, $this->subRequests);
            $key2 = array_search($request2, $this->subRequests);

            return $table[$key2] <=> $table[$key1];
        });
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

    /**
     * @param string $key
     *
     * @return array
     * @throws Http400InvalidRequestException
     */
    private function resolveParam(string $key)
    {
        $keys = explode(".", $key);
        $array = $this->responseData;

        $subKey = array_shift($keys);
        $array = $array[$subKey]["body"];

        foreach ($keys as $subKey) {
            if (!empty($array[$subKey])) {
                $array = $array[$subKey];
            } else {
                throw new Http400InvalidRequestException("Unable to find key `$key`.", 400, null, [
                    "data" => $this->responseData
                ]);
            }
        }

        return $array;
    }
}
