<?php
namespace App\Http\Controllers;

use App\Exceptions\Http400InvalidRequestException;
use App\Exceptions\Http401UnauthorizedException;
use App\Exceptions\Http404ModelNotFoundException;
use App\Exceptions\Http422ModelSaveException;
use App\Exceptions\Http422ModelValidationException;
use App\Models\BaseModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Validator;
use Laravel\Lumen\Routing\Controller;

/**
 * Class BaseController
 * Add a few methods to all controllers
 *
 * @author Mathrix Back-End Team
 * @package App\Http\Controllers
 * @since 0.8.1
 * @copyright Mathrix
 */
abstract class BaseController extends Controller
{
    /** @var BaseModel The model class associated with the controller */
    public $modelClass = null;
    /** @var array The model map to handle cancerous models name */
    public $modelMap = ['Class' => 'Class_'];


    public function __construct()
    {
        $this->guessModelClass();
    }


    /**
     * Guess the model class associated with the controller
     */
    protected function guessModelClass()
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


    /**
     * Get the user according to a optional request key and its permission
     *
     * @param Request $request the request
     * @param string $minimumScope the minimum permission accepted
     * @param string $key the key in request corresponding the user id
     *
     * @return User
     * @throws Http400InvalidRequestException
     * @throws Http401UnauthorizedException
     */
    protected function getUserByRequestOrUnauthorized(Request $request, $minimumScope = 'admin', $key = 'user_id')
    {
        /** @var User $user */
        $user = $request->user();
        $userId = $request->input($key);

        // Check the the user key content is an integer
        if ($userId !== null && !is_int($userId)) {
            throw new Http400InvalidRequestException($key . ' has to be a positive integer');
        }

        // Check authorization
        if ($user === null || ($userId !== null && $user->id != $userId && !$user->tokenCan($minimumScope))) {
            throw new Http401UnauthorizedException();
        }

        return $userId === null ? $request->user() : User::where('id', '=', $userId)->firstOrFail();
    }


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


    /*
    |--------------------------------------------------------------------------
    | Generic actions
    |--------------------------------------------------------------------------
    */
    /**
     * Generic paginated index method
     *
     * @param Request $request
     * @param int $page
     * @param int $perPage
     *
     * @return JsonResponse
     * @throws Http400InvalidRequestException
     * @throws Http401UnauthorizedException
     */
    public function index(Request $request, int $page = 1, int $perPage = 10): JsonResponse
    {
        // Check permission
        if ($request->user() !== null && $request->user()->cant('index', $this->modelClass)) {
            throw new Http401UnauthorizedException();
        }

        if ($page < 1 || $perPage < 1) {
            throw new Http400InvalidRequestException('page and perPage have to be strictly positive');
        }

        return response()->json(
            $this->modelClass::limit($perPage)
                ->offset(($page - 1) * $perPage)
                ->get()
        );
    }


    /**
     * Generic search action
     *
     * @param Request $request
     *
     * @return JsonResponse
     * @throws Http400InvalidRequestException
     *
     */
    public function search(Request $request): JsonResponse
    {
        $inputs = $request->except(['order', 'having']);
        $order = array_merge([
            'column' => 'id',
            'dir' => 'asc'
        ], $request->input('order', []));

        $conditions = [];
        $having = [];

        if (!empty($inputs['order']) && !in_array($order['dir'], ['asc', 'desc'])) {
            throw new Http400InvalidRequestException('order allowed values: ["asc", "desc"]');
        }

        foreach ($inputs as $column => $value) {
            $operator = '=';
            if (strstr($value, '%') !== false) {
                $operator = 'LIKE';
            }
            $conditions[] = [$column, $operator, $value];
        }

        foreach ($request->input('having', []) as $relation => $relationConditions) {
            foreach ($relationConditions as $column => $value) {
                $operator = '=';
                if (strstr($value, '%') !== false) {
                    $operator = 'LIKE';
                }
                $having[$relation][] = [$column, $operator, $value];
            }
        }

        // Build query
        $queryBuilder = $this->modelClass::where($conditions);

        foreach ($having as $relation => $havingConditions) {
            $queryBuilder = $queryBuilder->whereHas($relation, function (Builder $query) use ($havingConditions) {
                return $query->where($havingConditions);
            });
        }

        $queryBuilder = $queryBuilder->orderBy($order['column'], $order['dir']);

        return response()->json($queryBuilder->get());
    }


    /**
     * Generic get action
     *
     * @param int $id the model id
     *
     * @return JsonResponse
     * @throws Http404ModelNotFoundException
     */
    public function get(int $id): JsonResponse
    {
        $model = $this->modelClass::where('id', $id)->firstOrFail();

        return response()->json($model);
    }


    /**
     * Generic add action
     *
     * @param Request $request
     *
     * @return JsonResponse
     * @throws Http401UnauthorizedException
     * @throws Http422ModelSaveException
     * @throws Http422ModelValidationException
     */
    public function add(Request $request): JsonResponse
    {
        if ($request->user() !== null && $request->user()->cant('add', $this->modelClass)) {
            throw new Http401UnauthorizedException();
        }

        /** @var BaseModel $model */
        $model = new $this->modelClass($request->all());
        $model->save();

        return response()->json($model);
    }


    /**
     * Generic edit action
     *
     * @param Request $request
     * @param int $id
     *
     * @return JsonResponse
     * @throws Http401UnauthorizedException
     * @throws Http422ModelSaveException
     * @throws Http422ModelValidationException
     */
    public function edit(Request $request, int $id): JsonResponse
    {
        /** @var BaseModel $model */
        $model = $this->modelClass::where('id', $id)->firstOrFail();

        if ($request->user() !== null && $request->user()->cant('edit', $model)) {
            throw new Http401UnauthorizedException();
        }

        $model->update($request->all());

        return response()->json($model);
    }


    /**
     * Generic delete action
     *
     * @param Request $request
     * @param int $id
     *
     * @return JsonResponse
     * @throws Http401UnauthorizedException
     * @throws Http422ModelSaveException
     * @throws \Exception
     */
    public function delete(Request $request, int $id)
    {
        /** @var BaseModel $activity */
        $model = $this->modelClass::where('id', $id)->firstOrFail();

        if ($request->user() !== null && $request->user()->cant('delete', $model)) {
            throw new Http401UnauthorizedException();
        }

        $model->delete();

        return response()->json();
    }

}
