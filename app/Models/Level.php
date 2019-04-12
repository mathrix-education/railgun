<?php

namespace App\Models;

use Mathrix\Lumen\Bases\BaseModel;

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
     * @return Level
     */
    public static function getLevelByPoints(int $points): self
    {
        /** @var Level $level */
        $level = self::query()
            ->where("objective", "<=", $points)
            ->orderByDesc("objective")
            ->firstOrFail();

        return $level;
    }
}
