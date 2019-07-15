<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Mathrix\Lumen\Zero\Models\BaseModel;

/**
 * Class Level.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 1.0.0
 *
 * @property int $id
 * @property int $level
 * @property int $objective
 * @property string|null $youtube_id
 */
class Level extends BaseModel
{
    /** @var array The attributes that are mass assignable */
    public $fillable = ["id", "level", "objective", "youtube_id"];
    /** @var array The validation rules */
    protected $rules = [
        "level" => "required|integer",
        "objective" => "required|integer",
    ];


    /**
     * Get the level associated with the given points amount.
     *
     * @param int $points
     *
     * @return Level|Builder|Model
     */
    public static function getLevelByPoints(int $points)
    {
        return self::query()
            ->where("objective", "<=", $points)
            ->orderByDesc("objective")
            ->firstOrFail();
    }
}
