<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\Relation;
use Mathrix\Lumen\Zero\Models\BaseModel;
use function array_search;
use function get_class;
use function is_int;

/**
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
    public const STATUSES = ['to_revise', 'completed'];

    protected $fillable = [
        'status',
        'user_id',
        'markable_type',
        'markable_id',
    ];

    /**
     * @param Activity|Module $model The markable model
     * @param User|int        $user  The user
     *
     * @return Mark
     */
    public static function retrieve($model, $user) : Mark
    {
        return self::retrieveAll($model, $user)->first();
    }

    /**
     * Retrieve all models linked with a given user.
     *
     * @param Activity|Module $model The markable model
     * @param User|int        $user  The user
     *
     * @return Mark[]|Collection
     */
    public static function retrieveAll($model, $user)
    {
        $modelClass = get_class($model);
        $userId     = is_int($user) ? $user : $user->id;

        $conditions = [
            'markable_type' => array_search($modelClass, Relation::$morphMap),
            'markable_id' => $model->id,
            'user_id' => $userId,
        ];

        $wheres = [];
        foreach ($conditions as $k => $v) {
            $wheres[] = [$k, '=', $v];
        }

        /** @var Mark $path */
        $marks = self::query()->where($wheres)->get();

        if ($marks->isEmpty()) {
            $marks = new Collection([new Mark($conditions)]);
        }

        return $marks;
    }

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    /**
     * BelongsTo User.
     */
    public function user() : BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function markable() : MorphTo
    {
        return $this->morphTo();
    }
}
