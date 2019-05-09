<?php

namespace App\Services\Facebook;

use Facebook\Exceptions\FacebookSDKException;
use Facebook\Http\GraphRawResponse;
use Facebook\HttpClients\FacebookHttpClientInterface;
use GuzzleHttp\Client as Guzzle6HttpClient;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException as GuzzleRequestException;
use GuzzleHttp\Psr7\Request as GuzzlePsr7Request;

/**
 * Class FacebookGuzzle6HttpClient.
 * Guzzle6 HttpClient adapter for Facebook SDK, as described by Sammy Kaye Powers
 *
 * @link https://www.sammyk.me/how-to-inject-your-own-http-client-in-the-facebook-php-sdk-v5#writing-a-guzzle-6-http-client-implementation-from-scratch
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.1.1-rc1
 */
class FacebookGuzzle6HttpClient implements FacebookHttpClientInterface
{
    private $client;


    public function __construct(Guzzle6HttpClient $client)
    {
        $this->client = $client;
    }


    public function send($url, $method, $body, array $headers, $timeOut)
    {
        $request = new GuzzlePsr7Request($method, $url, $headers, $body);

        try {
            $response = $this->client->send($request, ["timeout" => $timeOut, "http_errors" => false]);
        } catch (GuzzleException | GuzzleRequestException $e) {
            throw new FacebookSDKException($e->getMessage(), $e->getCode());
        }

        $httpStatusCode = $response->getStatusCode();
        $responseHeaders = $response->getHeaders();

        foreach ($responseHeaders as $key => $values) {
            $responseHeaders[$key] = implode(", ", $values);
        }

        $responseBody = $response->getBody()->getContents();

        return new GraphRawResponse(
            $responseHeaders,
            $responseBody,
            $httpStatusCode
        );
    }
}
