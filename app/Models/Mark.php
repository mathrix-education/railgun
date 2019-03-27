<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Mathrix\Lumen\Bases\BaseModel;

/**
 * Class Mark.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 4.2.0
 *
 * @property int $id
 * @property string $status
 * @property int $user_id
 * @property int $markable_id
 * @property int $markable_type
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * ---
 * @property User $user
 * @property Activity|Module $markable
 */
class Mark extends BaseModel
{
    public $timestamps = true;
    protected $fillable = ["status", "user_id", "markable", "markable_id"];

    /**
     * Retrieve all models linked with a given user.
     *
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


    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */
    /**
     * BelongsTo User.
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }


    /**
     * @return MorphTo
     */
    public function markable()
    {
        return $this->morphTo();
    }

}
