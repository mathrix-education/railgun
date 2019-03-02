<?php
namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class Score.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @author    Jérémie Levain <munezero999@live.fr>
 * @since     4.1.0
 * @copyright Mathrix Education SA
 * @package   App\Models
 *
 * @property string      $game
 * @property int         $id
 * @property int|null    $score
 * @property int         $rank
 * @property mixed       $data
 * @property int         $user_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property User        $user
 */
class Score extends BaseModel
{
    /** @var array The guarded attributes */
    protected $fillable = ["game", "score", "data", "user_id", "created_at", "updated_at"];
    /** @var array The validation rules on create */
    protected $rules = [
        "game" => "required",
        "score" => "nullable|integer|min:0",
        "data" => "nullable|json"
    ];
    /** @var array The attributes that should be cast to native types */
    protected $casts = ["data" => "array"];
    public $timestamps = true;

    /**
     * BelongsTo User.
     *
     * @return BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
