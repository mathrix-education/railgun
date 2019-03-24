<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Mathrix\Lumen\Bases\BaseModel;

/**
 * Class Mark.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @author    Jérémie Levain <munezero999@live.fr>
 * @since     4.2.0
 * @copyright Mathrix Education SA
 * @package   App\Models
 *
 * @property int $id
 * @property string $status
 * @property int $user_id
 * @property int $path_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @property Path $path
 * @property User $user
 */
class Mark extends BaseModel
{
    public $timestamps = true;
    protected $fillable = ["status", "user_id", "path_id"];


    /**
     * @param Activity|Module $model The markable model
     * @param User|int $user The user
     *
     * @return Mark[]|\Illuminate\Database\Eloquent\Collection
     */
    public static function retrieveAll($model, $user)
    {
        $modelClass = get_class($model);
        $userId = is_int($user) ? $user : $user->id;

        $conditions = [
            "markable_type" => $modelClass,
            "markable_id" => $model->id,
            "user_id" => $userId
        ];

        $wheres = [];
        foreach ($conditions as $k => $v) {
            $wheres[] = [$k, "=", $v];
        }

        /** @var Mark $path */
        $marks = self::query()->where($wheres)->get();

        if ($marks->count() === 0) {
            $marks = new \Illuminate\Database\Eloquent\Collection(new Mark($conditions));
        }

        return $marks;
    }


    /**
     * @param Activity|Module $model The markable model
     * @param User|int $user The user
     *
     * @return Mark
     */
    public static function retrieve($model, $user)
    {
        return self::retrieveAll($model, $user)->first();
    }


    public function markable()
    {
        return $this->morphTo();
    }


    /**
     * BelongsTo User.
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
