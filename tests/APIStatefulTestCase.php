<?php

declare(strict_types=1);

namespace Tests;

use App\Mails\BaseMail;
use App\Models\User;
use cebe\openapi\spec\Operation;
use Illuminate\Support\Facades\Mail;
use Mathrix\Lumen\JWT\Auth\JWT;
use Mathrix\Lumen\Zero\Testing\Traits\DebugTrait;
use Mathrix\Lumen\Zero\Testing\Traits\RESTTrait;
use Mathrix\OpenAPI\Assertions\Lumen\LumenOpenAPIAssertions;
use function strtolower;

/**
 * Base class for stateful (with database persistence) tests.
 */
class APIStatefulTestCase extends BaseTestCase
{
    use RESTTrait;
    use LumenOpenAPIAssertions;
    use DebugTrait;

    /** @var User $requester The request user */
    protected $requester;

    public static function setUpBeforeClass(): void
    {
        self::$openAPISpecificationPath = __DIR__ . '/../docs/output.yaml';
        parent::setUpBeforeClass();
    }

    public function setUp(): void
    {
        parent::setUp();

        $this->handler(
            'before.json',
            function (string $method, string $uri) {
                $scopes = $this->getOpenAPIScopes($method, $uri);

                if (empty($scopes)) {
                    return;
                }

                // If route requires a scope, automatically add it.
                $this->requester = JWT::withScopes($scopes[0]);
            }
        );
    }

    /**
     * Automatically mock the scope according to the OpenAPI documentation. This ensure that the documentation is up to
     * date with the current implementation.
     *
     * @param string $method
     * @param string $uri
     * @param string $security
     *
     * @return string[]|null
     */
    public function getOpenAPIScopes(string $method, string $uri, string $security = 'bearer')
    {
        $method = strtolower($method);

        $openAPIUri = self::$reverseRouter->getUri($method, $uri);

        $paths = self::$schema->paths;

        if (!isset($paths[$openAPIUri])) {
            $this->fail("$openAPIUri PathItem does not exist in the OpenAPI specification.");
        }

        $pathItem = $paths[$openAPIUri];

        if (!isset($pathItem->{$method})) {
            $this->fail("$openAPIUri does not support $method method.");
        }

        /** @var Operation $operation */
        $operation = $pathItem->{$method};

        if (!isset($operation->security[0]) || !isset($operation->security[0]->{$security})) {
            return null;
        }

        return $operation->security[0]->{$security};
    }

    /**
     * Assert that a mailable has been sent to an user.
     *
     * @param string|BaseMail $mailClass
     * @param User            $user
     */
    public function assertMailSentTo($mailClass, User $user)
    {
        Mail::assertSent(
            $mailClass,
            static function ($mail) use ($user) {
                /** @var BaseMail $mail The mail which is expected to be sent. */
                return $mail->hasTo($user->email);
            }
        );
    }
}
