<?php

namespace Tests;

use App\Models\User;
use Laravel\Lumen\Testing\DatabaseTransactions;
use Mathrix\Lumen\Zero\Models\BaseModel;
use Mathrix\Lumen\JWT\Auth\JWT;
use Mathrix\Lumen\Tests\OpenAPI\OpenAPITrait;
use Mathrix\Lumen\Tests\REST\RESTTrait;
use Mathrix\Lumen\Tests\Traits\DebugTrait;
use Throwable;

/**
 * Class APITestCase.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0-rc1
 */
class APIStatefulTestCase extends BaseTestCase
{
    use RESTTrait, DebugTrait;

    /** @var BaseModel The test main model class */
    protected $modelClass = null;
    /** @var string The model table */
    protected $modelTable = null;
    /** @var string The test main model singular */
    protected $modelSingular = null;
    /** @var string The test main model plural (used to build request URLs) */
    protected $baseUri = null;

    /** @var User $requester The request user */
    protected $requester;


    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        // Include OpenAPI once
        OpenAPITrait::$SpecPath = __DIR__ . "/../docs/json-schema/index.json";
        self::bootOpenAPI();
    }


    /**
     * Automatically mock the scope according to the OpenAPI documentation. This ensure that the documentation is up to
     * date with the current implementation.
     *
     * @param string $method
     * @param string $uri
     * @param string $security
     *
     * @return mixed
     */
    public function getOpenAPIScopes(string $method, string $uri, string $security = "bearer")
    {
        $method = strtolower($method);

        $openAPIUri = $this->getOpenAPIUri($method, $uri);
        $path = self::$schema->getPathSchema($openAPIUri);
        $allowedMethods = self::$schema->getAllowedMethods($openAPIUri);

        if (!in_array(strtoupper($method), $allowedMethods)) {
            $this->fail("$method is not allowed for uri $openAPIUri");
        } else if (!$this->requester && isset($path->{$method}->security[0]->{$security})) {
            return $path->{$method}->security[0]->{$security};
        }

        return null;
    }


    public function setUp()
    {
        parent::setUp();

        $this->initializeREST();
        $this->handler("before.request", function (string $method, string $uri) {
            $scopes = $this->getOpenAPIScopes($method, $uri);

            if (empty($scopes)) {
                $this->requester = User::random();
                JWT::actingAs($this->requester);
            } else {
                $this->requester = JWT::withScopes($scopes[0]);

                if (!in_array("logged", $this->requester->scopes)) {
                    $this->requester->scopes = array_merge($this->requester->scopes, ["logged"]);
                    $this->requester->save();
                }
            }
        });
    }


    public function onNotSuccessfulTest(Throwable $t)
    {
        $this->debug();
        parent::onNotSuccessfulTest($t);
    }
}
