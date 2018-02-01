<?php
namespace App\Controllers;

use App\Exceptions\Http400InvalidRequestException;
use App\Exceptions\Http401UnauthorizedException;
use App\Exceptions\Http422ModelSaveException;
use App\Exceptions\Http422ModelValidationException;
use App\Models\BaseModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Laravel\Lumen\Routing\Controller;

/**
 * Class BaseController
 * Add a few methods to all controllers
 *
 * @author Mathrix Back-End Team
 * @package App\Controllers
 * @since 1.0.0
 * @copyright Mathrix SA
 */
abstract class BaseController extends Controller
{
    /** @var BaseModel The model class associated with the controller */
    public $modelClass = null;
    /** @var array The model map to handle cancerous models name */
    public $modelMap = ["Class" => "Class_"];


    /**
     * BaseController constructor
     * Build model class
     *
     * @throws Http400InvalidRequestException
     * @throws \Exception
     */
    public function __construct()
    {
        $modelName = str_replace(["App\\Controllers\\", "Controller"], "", get_class($this));
        $modelClass = $this->resolveModelClass($modelName, false);

        if ($modelClass !== false) {
            $this->modelClass = $modelClass;
        }
    }


    /**
     * @param string $name the model name
     * @param bool $fail if true, raise and exception
     *
     * @return BaseModel|bool
     * @throws Http400InvalidRequestException
     * @throws \Exception
     */
    protected function resolveModelClass($name, $fail = true)
    {
        $potentialModel = Str::studly(Str::singular($name));

        if (in_array($potentialModel, array_keys($this->modelMap))) {
            $potentialModel = $this->modelMap[$potentialModel];
        }

        /** @var BaseModel $potentialModelClass */
        $potentialModelClass = "App\\Models\\$potentialModel";

        if (!class_exists($potentialModelClass) && $fail) {
            throw new Http400InvalidRequestException("$potentialModelClass does not exist");
        } else if (!class_exists($potentialModelClass) && !$fail) {
            return false;
        } else {
            return $potentialModelClass;
        }
    }


    /**
     * Order a collection of models by their order key if it exists. If not, just return the collection.
     *
     * @param Collection $collection
     *
     * @return Collection
     */
    protected function orderCollection(Collection $collection): Collection
    {
        if (!empty($collection->first()->order)) {
            $collection = $collection->sortBy(function ($model) {
                return $model->order;
            })->values();
        }

        return $collection;
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
     * @throws \Exception
     */
    protected function getUserByRequestOrUnauthorized(Request $request, $minimumScope = "admin", $key = "user_id")
    {
        /** @var User $user */
        $user = $request->user();
        $userId = $request->input($key);

        // Check the the user key content is an integer
        if ($userId !== null && !is_int($userId)) {
            throw new Http400InvalidRequestException("`$key` has to be a positive integer");
        }

        // Check authorization
        if ($user === null || ($userId !== null && $user->id != $userId && !$user->tokenCan($minimumScope))) {
            throw new Http401UnauthorizedException();
        }

        return $userId === null ? $request->user() : User::where("id", "=", $userId)->firstOrFail();
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
     * @throws Http401UnauthorizedException
     * @throws \Exception
     */
    public function index(Request $request, int $page = 1, int $perPage = 10): JsonResponse
    {
        // Check permission
        if ($request->user() !== null && $request->user()->cant("index", $this->modelClass)) {
            throw new Http401UnauthorizedException();
        }

        return response()->json(
            $this->orderCollection(
                $this->modelClass::limit($perPage)
                    ->offset(($page - 1) * $perPage)
                    ->get()
            )
        );
    }


    /**
     * Generic get action
     *
     * @param Request $request
     * @param string|int $key the field or the id
     * @param null $value if set, the field value to search
     *
     * @return JsonResponse
     * @throws Http401UnauthorizedException
     * @throws Http400InvalidRequestException
     * @throws \Exception
     */
    public function get(Request $request, $key, $value = null): JsonResponse
    {
        if (!is_numeric($key) && $value === null) {
            throw new Http400InvalidRequestException("The given id \"$key\" is not numeric");
        }
        if (is_numeric($key) && $value === null) {
            $value = $key;
            $key = "id";
        }

        $model = $this->modelClass::where($key, $value)->firstOrFail();

        if ($request->user() !== null && $request->user()->cant("get", $model)) {
            throw new Http401UnauthorizedException();
        }

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
     * @throws \Exception
     */
    public function add(Request $request): JsonResponse
    {
        if ($request->user() !== null && $request->user()->cant("add", $this->modelClass)) {
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
     * @throws \Exception
     */
    public function edit(Request $request, int $id): JsonResponse
    {
        /** @var BaseModel $model */
        $model = $this->modelClass::where("id", $id)->firstOrFail();

        if ($request->user() !== null && $request->user()->cant("edit", $model)) {
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
     * @throws \Exception
     */
    public function delete(Request $request, int $id): JsonResponse
    {
        /** @var BaseModel $activity */
        $model = $this->modelClass::where("id", $id)->firstOrFail();

        if ($request->user() !== null && $request->user()->cant("delete", $model)) {
            throw new Http401UnauthorizedException();
        }

        $model->delete();

        return response()->json();
    }


    /**
     * Generic search action
     *
     * @param Request $request
     * @param int $page
     * @param int $perPage
     *
     * @return JsonResponse
     * @throws Http400InvalidRequestException
     * @throws Http401UnauthorizedException
     * @throws \Exception
     */
    public function search(Request $request, $page = 1, $perPage = 10): JsonResponse
    {
        if ($request->user() !== null && $request->user()->cant("search", $this->modelClass)) {
            throw new Http401UnauthorizedException();
        }

        $inputs = $request->except(["order", "having"]);
        $order = array_merge([
            "column" => "id",
            "dir" => "asc"
        ], $request->input("order", []));

        $conditions = [];
        $having = [];

        // Check if order dir is valid
        if (!empty($inputs["order"]) && !in_array($order["dir"], ["asc", "desc"])) {
            throw new Http400InvalidRequestException("order allowed values: ['asc', 'desc']");
        }

        // Convert request form data to valid eloquent conditions
        foreach ($inputs as $column => $value) {
            $operator = "=";
            if (strstr($value, "%") !== false) {
                $operator = "LIKE";
            }
            $conditions[] = [$column, $operator, $value];
        }

        foreach ($request->input("having", []) as $relation => $relationConditions) {
            foreach ($relationConditions as $column => $value) {
                $operator = "=";
                if (strstr($value, "%") !== false) {
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

        $queryBuilder = $queryBuilder->limit($perPage)
            ->offset(($page - 1) * $perPage);

        $count = $queryBuilder->count();
        $data = $queryBuilder->get();

        return response()->json([
            "perPage" => $perPage,
            "page" => $page,
            "count" => $count,
            "data" => $data
        ]);
    }


    /**
     * Get the models which satisfy a given relation
     *
     * @param Request $request
     * @param string $what
     * @param int|string $key
     * @param int|string $value
     *
     * @return JsonResponse
     * @throws Http400InvalidRequestException
     * @throws Http401UnauthorizedException
     * @throws \Exception
     */
    public function by(Request $request, string $what, $key, $value = null)
    {
        if (!is_numeric($key) && $value === null) {
            throw new Http400InvalidRequestException("The given id \"$key\" is not numeric");
        }
        if (is_numeric($key) && $value === null) {
            $value = $key;
            $key = "id";
        }

        $modelClass = $this->resolveModelClass($what);
        $ucName = ucfirst($what);


        /** @var BaseModel $model */
        $model = $modelClass::where($key, $value)->firstOrFail();

        $potentialRelation = Str::lower($this->modelClass::getTableName());

        if (!method_exists($model, $potentialRelation)) {
            throw new Http400InvalidRequestException("$modelClass::$potentialRelation() does not exist");
        }

        if ($request->user() !== null && $request->user()->cant("by$ucName", $this->modelClass)) {
            throw new Http401UnauthorizedException();
        }

        return response()->json(
            $this->orderCollection($model->{$potentialRelation}()->get())
        );
    }


    /**
     * Get the model ids which satisfy a given relation
     *
     * @param Request $request
     * @param string $what
     * @param int $id
     *
     * @return JsonResponse
     * @throws Http400InvalidRequestException
     * @throws Http401UnauthorizedException
     * @throws \Exception
     */
    public function ids(Request $request, string $what, int $id)
    {
        $modelClass = $this->resolveModelClass($what);

        /** @var BaseModel $model */
        $model = $modelClass::where("id", $id)->firstOrFail();

        $potentialRelation = Str::lower($this->modelClass::getTableName());

        if (!method_exists($model, $potentialRelation)) {
            throw new Http400InvalidRequestException("$modelClass::$potentialRelation() does not exist");
        }

        if ($request->user() !== null && $request->user()->cant("by$modelClass", $this->modelClass)) {
            throw new Http401UnauthorizedException();
        }

        return response()->json(
            $this->orderCollection($model->{$potentialRelation}()
                ->get(["id"])
                ->pluck("id"))
        );
    }
}
