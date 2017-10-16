<?php
namespace App\Models;

use App\Http\HttpJsonResponseBuilderTrait;
use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Expression;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

/**
 * Class BaseModel
 * Base for all models, implement validation on save useless manual disable
 * @method static Builder select($columns = ['*'])
 * @method static Builder selectRaw($expression, array $bindings = [])
 * @method static Builder selectSub($query, $as)
 * @method static Builder addSelect($column)
 * @method static Builder distinct()
 * @method static Builder from()
 * @method static Builder join($table, $first, $operator = null, $second = null, $type = 'inner', $where = false)
 * @method static Builder joinWhere($table, $first, $operator, $second, $type = 'inner')
 * @method static Builder leftJoin($table, $first, $operator = null, $second = null)
 * @method static Builder leftJoinWhere($table, $first, $operator, $second)
 * @method static Builder rightJoin($table, $first, $operator = null, $second = null)
 * @method static Builder rightJoinWhere($table, $first, $operator, $second)
 * @method static Builder crossJoin($table, $first = null, $operator = null, $second = null)
 * @method static Builder tap($callback)
 * @method static Builder where($column, $operator = null, $value = null, $boolean = 'and')
 * @method static Builder whereColumn($first, $operator = null, $second = null, $boolean = 'and')
 * @method static Builder whereRaw($sql, $bindings = [], $boolean = 'and')
 * @method static Builder whereIn($column, $values, $boolean = 'and', $not = false)
 * @method static Builder whereNotIn($column, $values, $boolean = 'and')
 * @method static Builder whereNull($column, $boolean = 'and', $not = false)
 * @method static Builder whereNotNull($column, $boolean = 'and')
 * @method static Builder whereBetween($column, array $values, $boolean = 'and', $not = false)
 * @method static Builder whereNotBetween($column, array $values, $boolean = 'and')
 * @method static Builder whereDate($column, $operator, $value = null, $boolean = 'and')
 * @method static Builder whereTime($column, $operator, $value, $boolean = 'and')
 * @method static Builder whereDay($column, $operator, $value = null, $boolean = 'and')
 * @method static Builder whereMonth($column, $operator, $value = null, $boolean = 'and')
 * @method static Builder whereYear($column, $operator, $value = null, $boolean = 'and')
 * @method static Builder whereNested(Closure $callback, $boolean = 'and')
 * @method static Builder forNestedWhere()
 * @method static Builder addNestedWhereQuery($query, $boolean = 'and')
 * @method static Builder whereExists(Closure $callback, $boolean = 'and', $not = false)
 * @method static Builder whereNotExists(Closure $callback, $boolean = 'and')
 * @method static Builder dynamicWhere($method, $parameters)
 * @method static Builder orderBy($column, $direction = 'asc')
 * @method static Builder orderByDesc($column)
 * @method static Builder latest($column = 'created_at')
 * @method static Builder oldest($column = 'created_at')
 * @method static Builder inRandomOrder($seed = '')
 * @method static Builder orderByRaw($sql, $bindings = [])
 * @method static Builder skip($value)
 * @method static Builder offset($value)
 * @method static Builder take($value)
 * @method static Builder limit($value)
 * @method static Builder forPage($page, $perPage = 15)
 * @method static Builder forPageAfterId($perPage = 15, $lastId = 0, $column = 'id')
 * @method static Builder union($query, $all = false)
 * @method static Builder unionAll($query)
 * @method static Builder lock($value = true)
 * @method static Builder lockForUpdate()
 * @method static Builder sharedLock()
 * @method static Builder toSql()
 * @method static Builder find($id, $columns = ['*'])
 * @method static Builder value($column)
 * @method static Builder get($columns = ['*'])
 * @method static Builder paginate($perPage = 15, $columns = ['*'], $pageName = 'page', $page = null)
 * @method static Builder simplePaginate($perPage = 15, $columns = ['*'], $pageName = 'page', $page = null)
 * @method static Builder getCountForPagination($columns = ['*'])
 * @method static Builder cursor()
 * @method static Builder chunkById($count, callable $callback, $column = 'id', $alias = null)
 * @method static Builder pluck($column, $key = null)
 * @method static Builder implode($column, $glue = '')
 * @method static Builder count($columns = '*')
 * @method static Builder min($column)
 * @method static Builder max($column)
 * @method static Builder sum($column)
 * @method static Builder avg($column)
 * @method static Builder average($column)
 * @method static Builder aggregate($function, $columns = ['*'])
 * @method static Builder numericAggregate($function, $columns = ['*'])
 * @method static Builder insert(array $values)
 * @method static Builder insertGetId(array $values, $sequence = null)
 * @method static Builder updateOrInsert(array $attributes, array $values = [])
 * @method static void truncate()
 * @method static Expression raw($value)
 *
 * @package App\Models
 */
abstract class BaseModel extends Model
{
    use HttpJsonResponseBuilderTrait {
        HttpJsonResponseBuilderTrait::__call as buildResponse;
    }

    /** @var bool Use appends */
    public static $withAppends = true;
    /** @var bool Indicates if the model should be timestamped */
    public $timestamps = false;
    /** @var bool Validate before save */
    protected $validate = true;
    /** @var array Default validation rules */
    protected $rules = [];
    /** @var string the validation rules to use */
    protected $useRules = 'rules';
    /** @var bool if model has validation errors */
    protected $errors = false;

    /**
     * The response which have to be sent if something goes wrong
     *
     * @var null|JsonResponse
     */
    protected $response = null;


    /**
     * Get the table name
     *
     * @return string the table name
     */
    public static function getTableName(): string
    {
        return with(new static)->getTable();
    }


    /**
     * Use appends
     */
    public static function withAppends()
    {
        self::$withAppends = true;

        return (new static);
    }


    /**
     * Don't use appends
     */
    public static function withoutAppends()
    {
        self::$withAppends = false;

        return (new static);
    }


    /**
     * Unguard some attributes
     *
     * @param string|array $attributes
     *
     * @return $this
     */
    public function unguardAttributes($attributes)
    {
        if (is_string($attributes)) {
            $attributes = [$attributes];
        }

        $this->guarded = array_diff($this->guarded, $attributes);

        return $this;
    }


    /**
     * Handle response
     *
     * @param JsonResponse|null $default
     *
     * @return JsonResponse|null
     */
    public function handleResponse(JsonResponse $default = null)
    {
        if ($this->hasResponse()) {
            return $this->getResponse();
        } else if (!is_null($default)) {
            return $default;
        } else {
            return $this->http500Model();
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Response
    |--------------------------------------------------------------------------
    */

    /**
     * Check if the model as a response
     *
     * @return bool
     */
    public function hasResponse()
    {
        return !is_null($this->response);
    }


    /**
     * Set the model response
     *
     * @return JsonResponse|null
     */
    public function getResponse()
    {
        return $this->response;
    }


    /**
     * Set the model response
     *
     * @param JsonResponse $response
     */
    public function setResponse(JsonResponse $response)
    {
        $this->response = $response;
    }


    /**
     * @return bool If model need to be validated before save
     */
    public function validateOnSave()
    {
        return $this->validate;
    }


    /*
    |--------------------------------------------------------------------------
    | Validation
    |--------------------------------------------------------------------------
    */

    /**
     * Enable the model validation
     */
    public function enableValidation()
    {
        $this->validate = true;
    }


    /**
     * Disable the model validation
     */
    public function disableValidation()
    {
        $this->validate = false;
    }


    /**
     * Validate model data
     *
     * @param string $rules
     *
     * @return bool
     */
    public function validate($rules = 'rules')
    {
        if (is_string($rules)) {
            $this->useRules = $rules;
            $rules = $this->{$this->useRules};
        }

        $validator = Validator::make($this->attributes, $rules);

        $validator = $this->injectInValidator($validator);

        if ($validator->fails()) {
            $this->errors = $validator->errors();

            return false;
        }

        return true;
    }


    /**
     * Inject custom rules in the validator before save
     *
     * @param \Illuminate\Validation\Validator $validator
     *
     * @return \Illuminate\Validation\Validator
     */
    public function injectInValidator($validator)
    {
        return $validator;
    }


    /**
     * Returns the validation errors
     *
     * @return bool|array
     */
    public function errors()
    {
        return $this->errors;
    }


    /**
     * Resolve __call conflict
     *
     * @param string $name
     * @param array $arguments
     *
     * @return JsonResponse|mixed
     */
    public function __call($name, $arguments)
    {
        if ($this->isHttpJsonResponse($name)) {
            return $this->buildResponse($name, $arguments);
        } else {
            return parent::__call($name, $arguments);
        }
    }


    /**
     * Override to support appends togglers
     *
     * @return array
     */
    protected function getArrayableAppends()
    {
        if (!self::$withAppends) {
            return [];
        }

        return parent::getArrayableAppends();
    }
}
