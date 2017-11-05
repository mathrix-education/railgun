<?php
namespace App\Models;

use App\Exceptions\Http400InvalidRequestException;
use App\Exceptions\Http422ModelSaveException;
use App\Exceptions\Http422ModelValidationException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;


/**
 * App\Models\Log
 *
 * @property int $id
 * @property string $event
 * @property int|null $user_id
 * @property string|null $value
 * @property string $created_at
 * @property string|null $loggable_type
 * @property int|null $loggable_id
 * @property-read User|null $user
 * @property-read Activity|Module $loggable
 * @method static Builder|Log whereCreatedAt($value)
 * @method static Builder|Log whereEvent($value)
 * @method static Builder|Log whereId($value)
 * @method static Builder|Log whereLoggableId($value)
 * @method static Builder|Log whereLoggableType($value)
 * @method static Builder|Log whereUserId($value)
 * @method static Builder|Log whereValue($value)
 * @mixin \Eloquent
 */
class Log extends BaseModel
{
    /**
     * Available events
     */
    public const EVENTS = [
        // User-related
        "user_registered",
        "user_logged",
        "user_edited_profile",
        "user_changed_password",
        "user_requested_new_password",
        // Points-related
        "user_gathered_points",
        "user_passed_level",
        "user_set_daily",
        "user_achieved_daily",
        // Content-related
        "user_added_favorite",
        "user_deleted_favorite",
        "user_marked_activity_as",
        // Premium-related
        "user_payment_succeed_for_premium",
        "user_payment_failed_for_premium",
        "user_cancelled_premium"
    ];

    /** @var bool If timestamps (created_at and updated_at) should be used */
    public $timestamps = true;

    /** @var array */
    public $fillable = ["event", "user_id", "value", "created_at", "loggable_type", "loggable_id"];

    /** @var array The attributes that should be cast to native types */
    protected $casts = ["value" => "array"];


    /**
     * Disable updated_at field update; seems to be a hack
     *
     * @param mixed $value
     *
     * @return $this
     */
    public function setUpdatedAt($value)
    {
        return $this;
    }


    /**
     * Add a log line the the database
     *
     * @param string $event the event name
     * @param User|int|null $user the user as model, its id or njull for system log
     * @param array $data the optional data to associate with the log, such as the loggable type and id
     *
     * @return bool
     * @throws Http400InvalidRequestException
     * @throws Http422ModelSaveException
     * @throws Http422ModelValidationException
     */
    public static function log(string $event, $user = null, $data = [])
    {
        throw_if(!in_array($event, self::EVENTS), Http400InvalidRequestException::class,
            $event . " is not a valid event");

        $data = array_merge([
            "event" => $event,
            "user_id" => $user === null || is_int($user) ? $user : $user->id,
        ], $data);

        $log = new Log($data);

        return $log->save();
    }

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */
    /**
     * BelongsTo User
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }


    /**
     * MorphTo Loggable
     *
     * @return MorphTo
     */
    public function loggable(): MorphTo
    {
        return $this->morphTo();
    }
}