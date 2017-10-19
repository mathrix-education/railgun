<?php
namespace App\Models;

use App\Exceptions\Http422ModelSaveException;
use App\Exceptions\Http422ModelValidationException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator as ValidatorFacade;
use Illuminate\Validation\Validator;

/**
 * Class BaseModel
 * Base for all models, implement validation on save useless manual disable
 *
 * @package App\Models
 */
abstract class BaseModel extends Model
{
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
     * Override to inject systematic validation and exception
     *
     * @param array $options
     *
     * @return bool
     * @throws Http422ModelSaveException
     * @throws Http422ModelValidationException
     */
    public function save(array $options = [])
    {
        if ($this->validateOnSave() && !$this->validate()) {
            throw new Http422ModelValidationException(null, 422, null, $this->errors);
        }

        $saved = parent::save($options);

        if (!$saved) {
            throw new Http422ModelSaveException();
        }

        return $saved;
    }


    /**
     * Override to inject exception
     *
     * @return bool|null
     * @throws Http422ModelSaveException
     * @throws \Exception
     */
    public function delete()
    {
        $deleted = parent::delete();

        if (!$deleted) {
            throw new Http422ModelSaveException();
        }

        return $deleted;
    }

    /*
    |--------------------------------------------------------------------------
    | Response
    |--------------------------------------------------------------------------
    */

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
        }
        $rules = array_intersect_key($this->{$this->useRules}, $this->getDirty());

        $validator = ValidatorFacade::make($this->getDirty(), $rules);

        $validator = $this->injectInValidator($validator);

        if ($validator->fails()) {
            $this->errors = $validator->errors()->getMessages();

            return false;
        }

        return true;
    }


    /**
     * Inject custom rules in the validator before save
     *
     * @param Validator $validator
     *
     * @return Validator
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
}
