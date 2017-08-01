<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Class BaseModel
 * @package app\Models
 * @method static Builder inRandomOrder()
 * @method static Builder where($column, $operator = null, $value = null, $boolean = 'and')
 */
abstract class BaseModel extends Model
{
    /**
     * Indicates if the model should be timestamped.
     * @var bool
     */
    public $timestamps = false;

    /**
     * Get table name
     * @return string
     */
    public static function getTableName(): string
    {
        return with(new static)->getTable();
    }
}