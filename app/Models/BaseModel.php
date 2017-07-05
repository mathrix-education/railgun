<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class BaseModel
 * @package app\Models
 */
abstract class BaseModel extends Model
{
    /**
     * Get table name
     * @return string
     */
    public static function getTableName()
    {
        return with(new static)->getTable();
    }
}