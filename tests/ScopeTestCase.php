<?php
namespace Tests;

use App\Models\Log;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Laravel\Passport\Passport;
use Mathrix\Lumen\Models\BaseModel;
use Mathrix\Lumen\Tests\BaseTestCase;
use Tests\Generics\Generics;

/**
 * Class ScopeTestCase
 * Base test case for actions which rely on permissions
 *
 * @author Mathrix Team
 * @package Tests
 */
class ScopeTestCase extends BaseTestCase
{
    use HttpAssertions, Generics;

    /** @var User the user used in the test */
    protected $user;
    /** @var array available scopes */
    protected $scopes = ['admin', 'moderator', 'premium', 'registered'];
    /** @var string the effective scope */
    protected $scope = 'public';

    /** @var BaseModel the test main model class */
    protected $modelClass = null;
    /** @var string the test main model singular */
    protected $modelSingular = null;
    /** @var string the test main model plural (used to build request URLs) */
    protected $modelPlural = null;

    /** @var array Model map; will override model guessing process */
    protected $modelsMap = [
        'Class' => 'Class_'
    ];

    /**
     * 1. Try to guess the model assiated with test class
     * Ex: QuotesAsRegisteredTest -> \App\Models\Quotes
     * 2. Initialize user if needed using the test name
     * Ex: testExampleAsAdmin -> admin
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $className = get_class($this);
        $classPath = explode('\\', get_class($this));
        $classNameWithoutNameSpace = array_pop($classPath);

        // Check if the test class if matching the scope convention (i.e. contain 'As')
        $matchingScopeConventions = strstr($className, 'As');

        /*
        |--------------------------------------------------------------------------
        | Model guessing
        |--------------------------------------------------------------------------
        */
        $guessedModelPlural = false;

        if ($matchingScopeConventions) {
            // Ex: QuotesAsRegisteredTest
            $indexOfAsSuffix = strpos($classNameWithoutNameSpace, 'As');
            if ($indexOfAsSuffix !== false) {
                $guessedModelPlural = substr($classNameWithoutNameSpace, 0, $indexOfAsSuffix); // Ex: Quotes
            }
        } else {
            // Ex: QuotesTest
            $indexOfTestSuffix = strpos($classNameWithoutNameSpace, 'Test');
            if ($indexOfTestSuffix !== false) {
                $guessedModelPlural = substr($classNameWithoutNameSpace, 0, $indexOfTestSuffix); // Ex: Quotes
            }
        }

        // If model plural has been guessed...
        if ($guessedModelPlural) {
            // Try to guess the associated model class
            $guessedModelName = str_singular($guessedModelPlural);

            if (!empty($this->modelsMap[$guessedModelName])) {
                $guessedModelClass = 'App\\Models\\' . $this->modelsMap[$guessedModelName];
            } else {
                $guessedModelClass = 'App\\Models\\' . $guessedModelName;
            }

            if (class_exists($guessedModelClass)) {
                // If the model class exists, define a few useful vars
                $this->modelPlural = strtolower($guessedModelPlural);
                $this->modelSingular = strtolower($guessedModelName);
                $this->modelClass = $guessedModelClass;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Scope
        |--------------------------------------------------------------------------
        */
        $potentialScope = 'public'; // Default to public, not in available scopes

        // Try to guess scope from class name
        if ($matchingScopeConventions) {
            $potentialScope = strtolower(substr($matchingScopeConventions, 2, -4));
        }

        // Scope may be overridden by test name
        $testName = $this->getName();
        $potentialSubstringScopeFromTestName = strstr($testName, 'As');


        if ($potentialSubstringScopeFromTestName) {
            $potentialScope = strtolower(substr($potentialSubstringScopeFromTestName, 2));
        }

        // Try to find the right scope
        if (in_array($potentialScope, $this->scopes)) {
            $this->scope = $potentialScope;
            $this->user = User::where('type', '=', $this->scope)->first();

            // No user could be found, thje database is probably not seeded
            if (!$this->user instanceof User) {
                throw new ModelNotFoundException('Could not find user with type `' . $this->scope .
                    '`. Is database seeded?');
            } else {
                Passport::actingAs($this->user, [$this->user->type]); // Set Passport behavior
            }
        }
    }


    /**
     * Destroy database connection after each test to avoid the Too Many Connections error
     */
    public function tearDown()
    {
        $this->beforeApplicationDestroyed(function () {
            DB::disconnect();
        });

        parent::tearDown();
    }


    /**
     * Call a test from a different test class of the same model
     *
     * @see QuotesAsRegisteredTest::testIndex() for a concrete example
     *
     * @param string $scope the scope
     * @param null|string $testName the test name, default the same as the called
     *
     * @return void
     * @throws \Exception
     */
    public function sameAs($scope, $testName = null)
    {
        $classNameToInstantiate = str_replace(
            'As' . ucfirst($this->scope),
            'As' . ucfirst($scope),
            get_class($this)
        );

        // Check if class exists
        if (!class_exists($classNameToInstantiate)) {
            throw new \Exception($classNameToInstantiate . ' does not exists');
        }

        /** @var ScopeTestCase $testClassObject */
        $testClassObject = new $classNameToInstantiate();
        $testName = $testName ?? $this->getName();

        $testClassObject->setup();

        // Check if method exists the class
        if (!method_exists($testClassObject, $testName)) {
            throw new \Exception('Test ' . $classNameToInstantiate . '::' . $testName . '() does not exists');
        }

        // Finally run the distant test
        $testClassObject->{$testName}();
    }

    /*
    |--------------------------------------------------------------------------
    | Database assertions; unlock access
    |--------------------------------------------------------------------------
    */
    /**
     * Cast data to its right MySQL values
     *
     * @param array $data
     *
     * @return array
     */
    public function castData(array $data)
    {
        foreach ($data as $field => $value) {
            if ($value instanceof \DateTime) {
                $data[$field] = $value->format('Y-m-d H:i:s');
            }
        }

        return $data;
    }

    /**
     * Assert that a given where condition exists in the database.
     *
     * @param  string $table
     * @param  array $data
     * @param  string|null $onConnection
     */
    public function assertInDatabase(string $table, array $data, $onConnection = null)
    {
        $this->seeInDatabase($table, $this->castData($data), $onConnection);
    }

    /**
     * Assert that a given where condition does not exist in the database.
     *
     * @param  string $table
     * @param  array $data
     * @param  string|null $onConnection
     */
    public function assertNotInDatabase(string $table, array $data, $onConnection = null)
    {
        $this->notSeeInDatabase($table, $this->castData($data), $onConnection);
    }

    /**
     * Assert that an event is logged in the database
     *
     * @param string $event The event type
     * @param User|int|null $user The user as object, the user_id or null if not relying on user
     * @param array $data The additional data to filter the event (associative or Eloquent mode)
     */
    public function assertLogged(string $event, $user = null, array $data = [])
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[] = [$key, $value];
                unset($data[$key]);
            }
        }

        $data = array_merge([
            ['event', $event],
            ['user_id', $user === null || is_int($user) ? $user : $user->id]
        ], $data);

        $count = Log::where($data)
            ->whereBetween('created_at', [Carbon::now()->subSeconds(10), Carbon::now()]) // 10 seconds
            ->count();

        $this->assertGreaterThan(0, $count, sprintf(
            'Unable to find row in database table [%s] that matched attributes [%s].', 'logs', json_encode($data)
        ));
    }
}