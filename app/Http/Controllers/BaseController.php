<?php
namespace App\Http\Controllers;

use App\Http\HttpJsonResponseBuilderTrait;
use Illuminate\Http\Request;
use Illuminate\Validation\Validator;
use Laravel\Lumen\Routing\Controller;

/**
 * Class BaseController
 * Add a few methods to all controllers
 *
 * @author Mathrix Team
 * @package App\Http\Controllers
 */
abstract class BaseController extends Controller
{
    use HttpJsonResponseBuilderTrait;

    /** @var string The model class associated with the controller */
    public $modelClass = null;
    public $modelMap = ['Class' => 'Class_'];


    public function __construct()
    {
        // Try to guess the model class
        $controllerClass = get_class($this);

        // Remove namespace to avoid replace colliding
        $guessedModelClass = str_replace('App\\Http\\Controllers\\', '', $controllerClass);
        $guessedModelClass = str_replace('Controller', '', $guessedModelClass);

        // Handle cancer model name (i.e. Class_)
        if (in_array($guessedModelClass, array_keys($this->modelMap))) {
            $guessedModelClass = $this->modelMap[$guessedModelClass];
        }

        // Re-add models namespace
        $guessedModelClass = 'App\\Models\\' . $guessedModelClass;

        if (class_exists($guessedModelClass) && $this->modelClass === null) {
            $this->modelClass = $guessedModelClass;
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Utility methods
    |--------------------------------------------------------------------------
    */
    /**
     * Check if a request contains the needed inputs
     *
     * @param Request $request the request
     * @param array $requiredInputs the required inputs
     */
    public function validateRequire(Request $request, array $requiredInputs): void
    {
        $this->validate($request, array_fill_keys($requiredInputs, 'required'));
    }


    public function index(Request $request, int $page = 1, int $perPage = 10)
    {
        // Check permission
        if ($request->user() !== null && $request->user()->cant('index', $this->modelClass)) {
            return $this->http401Unauthorized();
        }

        if ($page < 1 || $perPage < 1) {
            return $this->http400InvalidRequest('page and perPage have to be strictly positive');
        }

        return $this->http200Success(
            $this->modelClass::limit($perPage)
                ->offset(($page - 1) * $perPage)
                ->get()
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Generic actions
    |--------------------------------------------------------------------------
    */


    /**
     * Override validation response format
     *
     * @param Validator $validator
     *
     * @return array
     */
    protected function formatValidationErrors(Validator $validator)
    {
        return [
            'error' => 'validation',
            'message' => [
                $validator->getMessageBag()->toArray()
            ]
        ];
    }
}
