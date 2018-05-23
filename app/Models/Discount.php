<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;

/**
 * App\Models\Discount
 *
 * @property string $code
 * @property int $counter
 * @property int $id
 * @property string $type
 * @property int $value
 * @method static Builder|Discount whereId($value)
 * @method static Builder|Discount whereCode($value)
 * @method static Builder|Discount whereType($value)
 * @method static Builder|Discount whereValue($value)
 * @method static Builder|Discount whereCounter($value)
 */
class Discount extends BaseModel
{
    public const TYPES = ["absolute", "percentage"];


    /** @var array The attributes that are mass assignable */
    protected $fillable = ["id", "code", "type", "value", "counter"];
}
