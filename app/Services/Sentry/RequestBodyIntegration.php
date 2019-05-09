<?php

namespace App\Services\Sentry;

use Psr\Http\Message\ServerRequestInterface;
use Sentry\Event;
use Sentry\Integration\IntegrationInterface;
use Sentry\State\Scope;
use Zend\Diactoros\ServerRequestFactory;

/**
 * Class RequestBodyIntegration.
 *
 * This integration collects information from the request and attaches them to
 * the event.
 *
 * Add request body data
 *
 * @link https://gist.github.com/yoyo930021/799f86a4fac53335639a03a7df2fbbba
 *
 * @author Stefano Arlandini <sarlandini@alice.it>
 * @author yoyo930021 <yoyo930021@gmail.com>
 * @copyright Mathrix Education SA.
 */
final class RequestBodyIntegration implements IntegrationInterface
{
    /**
     * {@inheritdoc}
     */
    public function setupOnce(): void
    {
        Scope::addGlobalEventProcessor(function (Event $event): Event {
            self::applyToEvent($event);

            return $event;
        });
    }


    /**
     * Applies the information gathered by the this integration to the event.
     *
     * @param Event $event The event that will be enriched with a request
     * @param ServerRequestInterface|null $request The Request that will be processed and added to the event
     */
    public static function applyToEvent(Event $event, ?ServerRequestInterface $request = null): void
    {
        if (null === $request) {
            $request = isset($_SERVER["REQUEST_METHOD"]) && PHP_SAPI !== "cli" ? ServerRequestFactory::fromGlobals() : null;
        }

        if (null === $request) {
            return;
        }

        $requestData = [
            "url" => (string)$request->getUri(),
            "method" => $request->getMethod(),
        ];

        if ($request->getUri()->getQuery()) {
            $requestData["query_string"] = $request->getUri()->getQuery();
        }

        if ($request->hasHeader("REMOTE_ADDR")) {
            $requestData["env"]["REMOTE_ADDR"] = $request->getHeaderLine("REMOTE_ADDR");
        }

        $requestData["cookies"] = $request->getCookieParams();
        $requestData["headers"] = $request->getHeaders();
        $requestData["data"] = ($request->getParsedBody()) ? $request->getParsedBody() : json_decode(file_get_contents("php://input"));
        $userContext = $event->getUserContext();

        if (null === $userContext->getIpAddress() && $request->hasHeader("REMOTE_ADDR")) {
            $userContext->setIpAddress($request->getHeaderLine("REMOTE_ADDR"));
        }

        $event->setRequest($requestData);
    }
}
