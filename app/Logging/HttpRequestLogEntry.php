<?php

declare(strict_types=1);

namespace Railgun\Logging;

use Illuminate\Container\Container;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use function mb_strlen;
use function microtime;
use function str_replace;

class HttpRequestLogEntry implements Arrayable
{
    private ?Request  $request;
    private ?Response $response;

    /**
     * @param Request|null  $request  The Illuminate HTTP request.
     * @param Response|null $response The Illuminate HTTP response.
     *
     * @throws BindingResolutionException
     */
    public function __construct(?Request $request = null, ?Response $response = null)
    {
        $this->request  = $request ?? Container::getInstance()->make('request');
        $this->response = $response;

        if ($this->request->userAgent() === 'Symfony') {
            $this->request = null; // @codeCoverageIgnore
        }
    }

    /**
     * Format the Lumen request to a Google Cloud Logging-compatible httpRequest entry.
     *
     * @link https://cloud.google.com/logging/docs/reference/v2/rest/v2/LogEntry#httprequest
     *
     * @return mixed[] The Google Cloud LogEntry httpRequest field as an array.
     */
    public function toArray(): ?array
    {
        if ($this->request === null) {
            return null; // @codeCoverageIgnore
        }

        return [
            'requestMethod' => $this->request->getMethod(),
            'requestUrl'    => str_replace($this->request->getBaseUrl(), '', $this->request->fullUrl()),
            'requestSize'   => mb_strlen($this->request->getContent(), '8bit'),
            'status'        => $this->response ? $this->response->status() : 200,
            'userAgent'     => $this->request->userAgent(),
            'remoteIp'      => $this->request->getClientIp(),
            'referer'       => $this->request->header('Referer'),
            'latency'       => (microtime(true) - (float)$this->request->server('REQUEST_TIME_FLOAT')) . 's',
            'protocol'      => $this->request->getProtocolVersion(),
        ];
    }
}
