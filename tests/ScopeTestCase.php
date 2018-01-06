<?php
namespace Tests;

use App\Models\BaseModel;
use App\Models\User;
use DB;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Laravel\Passport\Passport;
use Tests\Generics\Generics;

/**
 * Class ScopeTestCase
 * Base test case for actions which rely on permissions
 *
 * @author Mathrix Team
 * @package Tests
 */
abstract class ScopeTestCase extends BaseTestCase
{
    use Generics;

    /** @var User the user used in the test */
    public $user = null;
    /** @var array available scopes */
    protected $scopes = ["admin", "moderator", "registered"];
    /** @var string the effective scope */
    protected $scope = "public";

    /** @var BaseModel|string|null the test main model class */
    protected $modelClass = null;
    /** @var string the test main model singular */
    protected $modelSingular = null;
    /** @var string the test main model plural (used to build request URLs) */
    protected $modelPlural = null;

    /** @var array Model map; will override model guessing process */
    protected $modelsMap = [
        "Class" => "Class_"
    ];


    /**
     * 1. Try to guess the model associated with test class
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
        $classPath = explode("\\", get_class($this));
        $classNameWithoutNameSpace = array_pop($classPath);

        // Check if the test class if matching the scope convention (i.e. contain "As")
        $matchingScopeConventions = strstr($className, "As");

        /*
        |--------------------------------------------------------------------------
        | Model guessing
        |--------------------------------------------------------------------------
        */
        $guessedModelPlural = false;

        if ($matchingScopeConventions) {
            // Ex: QuotesAsRegisteredTest
            $indexOfAsSuffix = strpos($classNameWithoutNameSpace, "As");
            if ($indexOfAsSuffix !== false) {
                $guessedModelPlural = substr($classNameWithoutNameSpace, 0, $indexOfAsSuffix); // Ex: Quotes
            }
        } else {
            // Ex: QuotesTest
            $indexOfTestSuffix = strpos($classNameWithoutNameSpace, "Test");
            if ($indexOfTestSuffix !== false) {
                $guessedModelPlural = substr($classNameWithoutNameSpace, 0, $indexOfTestSuffix); // Ex: Quotes
            }
        }

        // If model plural has been guessed...
        if ($guessedModelPlural) {
            // Try to guess the associated model class
            $guessedModelName = str_singular($guessedModelPlural);

            if (!empty($this->modelsMap[$guessedModelName])) {
                $guessedModelClass = "App\\Models\\{$this->modelsMap[$guessedModelName]}";
            } else {
                $guessedModelClass = "App\\Models\\$guessedModelName";
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
        $potentialScope = "public"; // Default to public, not in available scopes

        // Try to guess scope from class name
        if ($matchingScopeConventions) {
            $potentialScope = strtolower(substr($matchingScopeConventions, 2, -4));
        }

        // Scope may be overridden by test name
        $testName = $this->getName();
        $potentialSubstringScopeFromTestName = strstr($testName, "As");


        if ($potentialSubstringScopeFromTestName !== false) {
            $potentialScope = strtolower(substr($potentialSubstringScopeFromTestName, 2));
        }

        // Try to find the right scope
        if (in_array($potentialScope, $this->scopes)) {
            $this->scope = $potentialScope;

            // User has NOT be manually defined from a sameAs() method
            if ($this->user === null) {
                $this->user = User::inRandomOrder()->where("type", "=", $this->scope)->first();
            }

            // No user could be found, the database is probably not seeded
            if (!$this->user instanceof User) {
                throw new ModelNotFoundException("Could not find user with type `{$this->scope}`. Is database seeded?");
            } else {
                Passport::actingAs($this->user, [$this->user->type]); // Set Passport behavior
            }
        }
    }


    /**
     * Get a stranger
     *
     * @param string|null $type null for same as user
     *
     * @return User
     */
    public function stranger($type = null)
    {
        $type = $type !== null ? $type : $this->user !== null ? $this->user->type : "registered";
        $conditions = [["type", "=", $type]];

        if ($this->user !== null) {
            $conditions[] = ["id", "!=", $this->user->id];
        }

        return User::inRandomOrder()
            ->where($conditions)
            ->firstOrFail();
    }
}
