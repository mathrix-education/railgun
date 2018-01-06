<?php
namespace App\Models;

use App\Exceptions\Http422ModelSaveException;
use App\Exceptions\Http422ModelValidationException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Validation\Validator;
use Validator as ValidatorFacade;

/**
 * Class BaseModel
 * Base for all models, implement validation on save useless manual disable
 *
 * @mixin Builder
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
    /** @var string Validation rules to use */
    protected $useRules = "rules";
    /** @var bool|array Validation errors */
    protected $errors = false;


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

        if (!empty($this->fillable)) {
            $this->fillable = array_merge($this->fillable, $attributes);
        } else {
            $this->guarded = array_diff($this->guarded, $attributes);
        }

        return $this;
    }


    /**
     * Get a random model from the database
     *
     * @param int $count The set size; if set to 1, just return the model
     *
     * @return Collection|BaseModel
     */
    public static function random($count = 1)
    {
        $query = self::inRandomOrder();

        if ($count == 1) {
            return $query->firstOrFail();
        } else {
            return $query->get();
        }
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
        $query = $this->newQueryWithoutScopes();

        // If the "saving" event returns false we'll bail out of the save and return
        // false, indicating that the save failed. This provides a chance for any
        // listeners to cancel save operations if validations fail or whatever.
        if ($this->fireModelEvent('saving') === false) {
            return false;
        }

        if ($this->validateOnSave() && !$this->validate()) {
            throw new Http422ModelValidationException(null, 422, null, $this->errors);
        }

        // If the model already exists in the database we can just update our record
        // that is already in this database using the current IDs in this "where"
        // clause to only update this model. Otherwise, we'll just insert them.
        if ($this->exists) {
            $saved = $this->isDirty() ?
                $this->performUpdate($query) : true;
        }

        // If the model is brand new, we'll insert it into our database and set the
        // ID attribute on the model to the value of the newly inserted row's ID
        // which is typically an auto-increment value managed by the database.
        else {
            $saved = $this->performInsert($query);

            if (!$this->getConnectionName() &&
                $connection = $query->getConnection()) {
                $this->setConnection($connection->getName());
            }
        }

        // If the model is successfully saved, we need to do a few more things once
        // that is done. We will call the "saved" method here to run any actions
        // we need to happen after a model gets successfully saved right here.
        if ($saved) {
            $this->finishSave($options);
        }

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
    public function validate($rules = "rules")
    {
        if (is_string($rules)) {
            $this->useRules = $rules;
        }
        $rules = array_intersect_key($this->{$this->useRules}, $this->getDirty());

        $validator = ValidatorFacade::make($this->getDirty(), $rules);

        $validator = $this->injectInValidator($validator);

        if ($validator->fails()) {
            $this->errors = $validator->errors()->getMessages();

            var_dump($this->errors);
            dd($this->toArray());


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


    /**
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getTableStructure()
    {
        $table = $this->getConnection()->getTablePrefix() . $this->getTable();
        $schema = $this->getConnection()->getDoctrineSchemaManager();
        $databasePlatform = $schema->getDatabasePlatform();
        $databasePlatform->registerDoctrineTypeMapping('enum', 'string');

        $database = null;
        if (strpos($table, '.')) {
            list($database, $table) = explode('.', $table);
        }

        $columns = $schema->listTableColumns($table, $database);

        $structure = [];

        if ($columns) {
            foreach ($columns as $column) {
                $name = $column->getName();

                $type = $column->getType()->getName();
                switch ($type) {
                    case 'string':
                    case 'text':
                    case 'date':
                    case 'time':
                    case 'guid':
                    case 'datetimetz':
                    case 'datetime':
                        $type = 'string';
                        break;
                    case 'integer':
                    case 'bigint':
                    case 'smallint':
                        $type = 'int';
                        break;
                    case 'boolean':
                        $type = 'bool';
                        break;
                    case 'decimal':
                    case 'float':
                        $type = 'float';
                        break;
                    default:
                        $type = 'mixed';
                        break;
                }

                $structure[] = [
                    "name" => $name,
                    "type" => $type,
                    "nullable" => !$column->getNotnull(),
                    "default" => $column->getDefault() === null && $column->getNotnull() ? "required" : $column->getDefault()
                ];
            }
        }

        return $structure;
    }
}
