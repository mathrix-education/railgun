<?php
namespace App\Controllers;

use App\Exceptions\Http400InvalidRequestException;
use App\Exceptions\Http401UnauthorizedException;
use App\Exceptions\Http422ModelSaveException;
use App\Exceptions\Http422ModelValidationException;
use App\Models\BaseModel;
use App\Models\User;
use App\Responses\PaginationJsonResponse;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Laravel\Lumen\Routing\Controller;

/**
 * Class BaseController.
 * Add a few methods to all controllers.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @author    Jérémie Levain <munezero999@live.fr>
 * @since     1.0.0
 * @copyright Mathrix Education SA
 * @package   App\Controllers
 */
abstract class BaseController extends Controller
{
    /** @var BaseModel The model class associated with the controller */
    public $modelClass = null;
    /** @var array The model map to handle cancerous models name */
    public $modelMap = ["Class" => "Class_"];

    /**
     * BaseController constructor
     * Build model class.
     *
     * @throws Http400InvalidRequestException
     */
    public function __construct()
    {
        $modelName = str_replace(["App\\Controllers\\", "Controller"], "", \get_class($this));
        $modelClass = $this->resolveModelClass($modelName, false);

        if ($modelClass !== false) {
            $this->modelClass = $modelClass;
        }
    }

    /**
     * Generic paginated index method.
     *
     * @param Request $request The request
     * @param int     $page
     * @param int     $perPage
     *
     * @throws Http401UnauthorizedException
     *
     * @return JsonResponse
     */
    public function index(Request $request, int $page = 1, int $perPage = 100): JsonResponse
    {
        // Check permission
        if ($request->user() !== null && $request->user()
                ->cant("index", $this->modelClass)) {
            throw new Http401UnauthorizedException();
        }

        $response = new PaginationJsonResponse($this->modelClass::newQuery(), $page, $perPage);

        return $response;
    }

    /**
     * Generic get action.
     *
     * @param Request    $request The request
     * @param string|int $key     The field or the id
     * @param null       $value   If set, the field value to search
     *
     * @throws Http401UnauthorizedException
     *
     * @return JsonResponse
     */
    public function get(Request $request, $key, $value = null): JsonResponse
    {
        if (is_numeric($key) && $value === null) {
            $value = $key;
            $key = "id";
        }

        $model = $this->modelClass::where($key, $value)
            ->firstOrFail();

        if ($request->user() !== null && $request->user()
                ->cant("get", $model)) {
            throw new Http401UnauthorizedException();
        }

        $response = new JsonResponse($model);

        return $response;
    }

    /*
    |--------------------------------------------------------------------------
    | Generic actions
    |--------------------------------------------------------------------------
    */
    /**
     * Generic add action.
     *
     * @param Request $request The request
     *
     * @throws Http401UnauthorizedException
     * @throws Http422ModelSaveException
     * @throws Http422ModelValidationException
     *
     * @return JsonResponse
     */
    public function add(Request $request): JsonResponse
    {
        if ($request->user() !== null && $request->user()
                ->cant("add", $this->modelClass)) {
            throw new Http401UnauthorizedException();
        }

        /** @var BaseModel $model */
        $model = new $this->modelClass($request->all());
        $model->save();

        $response = new JsonResponse($model);

        return $response;
    }

    /**
     * Generic edit action.
     *
     * @param Request $request The request
     * @param int     $id
     *
     * @throws Http401UnauthorizedException
     *
     * @return JsonResponse
     */
    public function edit(Request $request, int $id): JsonResponse
    {
        /** @var BaseModel $model */
        $model = $this->modelClass::where("id", $id)
            ->firstOrFail();

        if ($request->user() !== null && $request->user()
                ->cant("edit", $model)) {
            throw new Http401UnauthorizedException();
        }

        $model->update($request->all());

        $response = new JsonResponse($model);

        return $response;
    }

    /**
     * Generic delete action.
     *
     * @param Request $request The request
     * @param int     $id
     *
     * @throws Http401UnauthorizedException
     * @throws Http422ModelSaveException
     *
     * @return JsonResponse
     */
    public function delete(Request $request, int $id): JsonResponse
    {
        /** @var BaseModel $activity */
        $model = $this->modelClass::where("id", $id)
            ->firstOrFail();

        if ($request->user() !== null && $request->user()
                ->cant("delete", $model)) {
            throw new Http401UnauthorizedException();
        }

        $model->delete();

        $response = new JsonResponse([
            "success" => true,
            "message" => ucfirst(str_singular($this->modelClass::getTableName())) . " id $id was successfully deleted.",
        ]);

        return $response;
    }

    /**
     * Generic search action.
     *
     * @param Request $request The request
     * @param int     $page
     * @param int     $perPage
     *
     * @throws Http400InvalidRequestException
     *
     * @return JsonResponse
     */
    public function search(Request $request, $page = 1, $perPage = 100): JsonResponse
    {
        $request->user()
            ->canOrFail("search", $this->modelClass);

        $inputs = $request->except(["order", "having"]);

        foreach ($inputs as $key => $value) {
            // Remove the URL as input
            if (Str::contains($key, "/")) {
                unset($inputs[$key]);
            }
        }

        $order = array_merge([
            "column" => "id",
            "dir" => "asc",
        ], $request->input("order", []));

        $conditions = [];
        $having = [];

        // Check if order dir is valid
        if (!\in_array($order["dir"], ["asc", "desc"], true)) {
            throw new Http400InvalidRequestException("order allowed values: ['asc', 'desc']");
        }

        // Convert request form data to valid eloquent conditions
        foreach ($inputs as $column => $value) {
            $operator = "=";
            if (mb_strstr($value, "%") !== false) {
                $operator = "LIKE";
            }
            $conditions[] = [$column, $operator, $value];
        }

        foreach ($request->input("having", []) as $relation => $relationConditions) {
            foreach ($relationConditions as $column => $value) {
                $operator = "=";
                if (mb_strstr($value, "%") !== false) {
                    $operator = "LIKE";
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

        $queryBuilder = $queryBuilder->orderBy($order["column"], $order["dir"]);

        $response = new PaginationJsonResponse($queryBuilder, $page, $perPage);

        return $response;
    }

    /**
     * Get the models which satisfy a given relation.
     *
     * @param Request $request The request
     * @param string  $what    the linked model
     * @param int     $id      the linked model id
     * @param int     $page    the query page
     * @param int     $perPage the query per page
     *
     * @throws Http400InvalidRequestException
     * @throws Http401UnauthorizedException
     *
     * @return JsonResponse
     */
    public function by(Request $request, string $what, int $id, int $page = 1, int $perPage = 100): JsonResponse
    {
        $query = $this->getByQuery($request, $what, $id, $page, $perPage);
        $response = new PaginationJsonResponse($query, $page, $perPage);

        return $response;
    }

    /**
     * Get the models ids which satisfy a given relation.
     *
     * @param Request $request The request
     * @param string  $what    the linked model
     * @param int     $id      the linked model id
     * @param int     $page    the query page
     * @param int     $perPage the query per page
     *
     * @throws Http400InvalidRequestException
     * @throws Http401UnauthorizedException
     *
     * @return JsonResponse
     */
    public function ids(Request $request, string $what, int $id, int $page = 1, int $perPage = 100)
    {
        $ids = $this->getByQuery($request, $what, $id, $page, $perPage)
            ->get()
            ->pluck("id");
        $response = new JsonResponse($ids);

        return $response;
    }

    /**
     * @param string $name the model name
     * @param bool   $fail if true, raise and exception
     *
     * @throws Http400InvalidRequestException
     *
     * @return BaseModel|bool
     */
    protected function resolveModelClass($name, $fail = true)
    {
        $potentialModel = Str::studly(Str::singular($name));

        if (\in_array($potentialModel, array_keys($this->modelMap), true)) {
            $potentialModel = $this->modelMap[$potentialModel];
        }

        /** @var BaseModel $potentialModelClass */
        $potentialModelClass = "App\\Models\\$potentialModel";

        if (!class_exists($potentialModelClass) && $fail) {
            throw new Http400InvalidRequestException("$potentialModelClass does not exist"); // @codeCoverageIgnore
        } elseif (!class_exists($potentialModelClass) && !$fail) {
            return false;
        }

        return $potentialModelClass;
    }

    /**
     * Handle foreign models queries.
     *
     * @param Request $request The request
     * @param string  $what
     * @param int     $id
     * @param int     $page
     * @param int     $perPage
     *
     * @throws Http400InvalidRequestException
     * @throws Http401UnauthorizedException
     *
     * @return Relation
     */
    protected function getByQuery(Request $request, string $what, int $id, int $page = 1, int $perPage = 100)
    {
        $modelClass = $this->resolveModelClass($what);
        $ucName = ucfirst($what);

        /** @var BaseModel $model */
        $model = $modelClass::where("id", $id)
            ->firstOrFail();

        $potentialRelation = Str::lower($this->modelClass::getTableName());

        if (!method_exists($model, $potentialRelation)) {
            throw new Http400InvalidRequestException("$modelClass::$potentialRelation() does not exist");
        }

        if ($request->user() !== null && $request->user()
                ->cant("by$ucName", $this->modelClass)) {
            throw new Http401UnauthorizedException();
        }

        return $model->{$potentialRelation}();
    }

    /**
     * Get the user according to a optional request key and its permission.
     *
     * @param Request $request      the request
     * @param string  $minimumScope the minimum permission accepted
     * @param string  $key          the key in request corresponding the user id
     *
     * @throws Http400InvalidRequestException
     * @throws Http401UnauthorizedException
     *
     * @return User
     */
    protected function getUserByRequestOrUnauthorized(Request $request, $minimumScope = "admin", $key = "user_id")
    {
        /** @var User $user */
        $user = $request->user();
        $userId = $request->input($key);

        // Check the the user key content is an integer
        if ($userId !== null && !\is_int($userId)) {
            throw new Http400InvalidRequestException("`$key` has to be a positive integer");
        }

        // Check authorization
        if ($user === null || ($userId !== null && $user->id !== $userId && !$user->tokenCan($minimumScope))) {
            throw new Http401UnauthorizedException();
        }

        return $userId === null ? $request->user() : User::where("id", $userId)
            ->firstOrFail();
    }
}
