<?php

namespace Tests\Integration\Braintree;

use App\Models\User;
use Mathrix\Lumen\Zero\Models\BaseModel;
use Mathrix\Lumen\Exceptions\Http\Http400BadRequestException;
use Mathrix\Lumen\JWT\Auth\JWT;
use Mathrix\Lumen\Tests\OpenAPI\OpenAPITrait;
use Mathrix\Lumen\Tests\REST\RESTTrait;
use Mathrix\Lumen\Tests\Traits\DebugTrait;
use Tests\BaseTestCase;
use Tests\Utils\NotificationsAssertionsTrait;
use Throwable;

/**
 * Class BraintreeTestCase.
 * Base class for Braintree tests.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 4.1.0
 */
abstract class BraintreeTestCase extends BaseTestCase
{
    use DebugTrait, RESTTrait, NotificationsAssertionsTrait;

    /** @var BaseModel The test main model class */
    protected $modelClass = null;
    /** @var string The model table */
    protected $modelTable = null;
    /** @var string The test main model singular */
    protected $modelSingular = null;
    /** @var string The test main model plural (used to build request URLs) */
    protected $baseUri = null;


    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        // Include OpenAPI once
        OpenAPITrait::$SpecPath = realpath(__DIR__ . "/../../../docs/json-schema/index.json");
        self::bootOpenAPI();
    }


    public function setup()
    {
        parent::setUp();

        $this->handler("before.json", function (string $method, string $uri) {
            $this->requestMethod = $method;
            $this->requestUri = "/" . trim($uri, "/");
        });
    }


    public function onNotSuccessfulTest(Throwable $t)
    {
        $this->debug();
        parent::onNotSuccessfulTest($t);
    }


    /**
     * Set the current user.
     *
     * @param User $user
     */
    public function setUser(User $user)
    {
        JWT::actingAs($user);
    }


    /**
     * Get a Braintree customer.
     *
     * @return User
     * @throws Http400BadRequestException
     */
    public function getBraintreeCustomer(): User
    {
        /** @var User $user */
        $user = User::random();

        if (!$user->isBraintreeCustomer()) {
            $user->createBraintreeCustomer("fake-valid-visa-nonce");
        }

        return $user;
    }


    /**
     * Get a pristine customer, which means that user is not already a customer.
     *
     * @return User
     */
    public function getPristineCustomer(): User
    {
        /** @var User $user */
        $user = factory(User::class)->make();
        $user->save();

        return $user;
    }
}
