<?php
namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class Mark.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @since     4.2.0
 * @copyright Mathrix Education SA
 *
 * @property int    $id
 * @property string $status
 * @property int    $parent_id
 * @property int    $user_id
 * @property int    $path_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @property Path   $path
 * @property User   $user
 */
class Mark extends BaseModel
{
    public $timestamps = true;
    protected $fillable = ["status", "user_id", "path_id"];

    /**
     * @param array       $conditions The array containing the key/value
     * @param string|null $status
     *
     * @return Mark
     */
    public static function retrieve(array $conditions, string $status = null)
    {
        $wheres = [];
        foreach ($conditions as $k => $v) {
            $wheres[] = [$k, "=", $v];
        }

        $data = array_merge($conditions, ["status" => $status]);

        /** @var Mark $path */
        $mark = self::where($wheres)->firstOrCreate($data);

        return $mark;
    }

    /**
     * BelongsTo Path.
     *
     * @return BelongsTo
     */
    public function path(): BelongsTo
    {
        return $this->belongsTo(Path::class);
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
