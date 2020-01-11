<?php

declare(strict_types=1);

namespace App\Services\Chainer;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Mathrix\Lumen\Zero\Exceptions\Http\Http400BadRequestException;
use Symfony\Component\HttpFoundation\ParameterBag;
use function app;
use function array_shift;
use function explode;
use function json_decode;
use function preg_match_all;
use function str_replace;
use const JSON_THROW_ON_ERROR;

class Chainer
{
    private Request $request;
    private array   $subRequests;
    private array   $responseData = [];
    private ?string $bearerToken;

    /**
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request     = $request;
        $this->bearerToken = $request->header('Authorization');
    }

    /**
     * @return JsonResponse
     *
     * @throws Http400BadRequestException
     */
    public function process(): JsonResponse
    {
        $this->subRequests = $this->request->input('requests', []);
        foreach ($this->subRequests as $subRequest) {
            $this->processIndividualRequest($subRequest);
        }

        return new JsonResponse($this->responseData);
    }

    /**
     * @param array $subRequest
     *
     * @throws Http400BadRequestException
     */
    private function processIndividualRequest(array $subRequest): void
    {
        $key     = $subRequest['name'];
        $request = Request::create(
            $this->resolve($subRequest['url']),
            $subRequest['method'],
            $subRequest['data'] ?? []
        );
        $request->setJson(new ParameterBag($subRequest['data'] ?? []));
        $request->headers->set('Authorization', $this->bearerToken);
        app()->offsetSet('request', $request);
        $response                 = app()->handle($request);
        $this->responseData[$key] = [
            'method' => $subRequest['method'],
            'url'    => $this->resolve($subRequest['url']),
            'body'   => json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR),
        ];
        app()->offsetSet('request', $this->request);
    }

    /**
     * @param string $toParse
     *
     * @return mixed|string
     *
     * @throws Http400BadRequestException
     */
    private function resolve(string $toParse)
    {
        $matches = [];
        $result  = preg_match_all('/{(\w+\.[.\w+]+\w+)}/', $toParse, $matches);
        if ($result === 0) {
            return $toParse;
        }

        $keys = $matches[1];
        foreach ($keys as $key) {
            $toParse = str_replace('{' . $key . '}', $this->resolveParam($key), $toParse);
        }

        return $toParse;
    }

    /**
     * @param string $key
     *
     * @return string|int
     *
     * @throws Http400BadRequestException
     */
    private function resolveParam(string $key)
    {
        $keys   = explode('.', $key);
        $array  = $this->responseData;
        $subKey = array_shift($keys);
        $array  = $array[$subKey]['body'];
        foreach ($keys as $subKey) {
            if (empty($array[$subKey])) {
                throw new Http400BadRequestException($this->responseData, "Unable to find key `$key`.");
            }

            $array = $array[$subKey];
        }

        return $array;
    }
}
