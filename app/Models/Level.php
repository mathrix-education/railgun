<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Builder;


/**
 * Model Level
 *
 * @package App\Models
 * @since 1.0.0
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @copyright Mathrix Education SA
 * @property int $id
 * @property int $level
 * @property int $objective
 * @property string|null $youtube_id
 * @method static Builder|Level whereId($value)
 * @method static Builder|Level whereLevel($value)
 * @method static Builder|Level whereObjective($value)
 * @method static Builder|Level whereYoutubeId($value)
 */
class Level extends BaseModel
{
    /** @var array The attributes that are mass assignable */
    public $fillable = ["id", "level", "objective", "youtube_id"];
    /** @var array The validation rules */
    protected $rules = [
        "level" => "required|integer",
        "objective" => "required|integer"
    ];


    /**
     * Get the level associated with the given points amount
     *
     * @param int $points
     *
     * @return Level
     */
    public static function getLevelByPoints(int $points): Level
    {
        /** @var Level $level */
        $level = Level::where("objective", "<=", $points)
            ->orderByDesc("objective")
            ->firstOrFail();

        return $level;
    }
}
