<?php
namespace App\Models;

use App\Exceptions\Http400InvalidRequestException;
use App\Exceptions\Http422ModelSaveException;
use App\Exceptions\Http422ModelValidationException;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Class Log.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @author    Jérémie Levain <munezero999@live.fr>
 * @since     1.0.0
 * @copyright Mathrix Education SA
 * @package   App\Models
 *
 * @property int             $id
 * @property string          $event
 * @property int|null        $user_id
 * @property string|null     $value
 * @property Carbon          $created_at
 * @property string|null     $loggable_type
 * @property int|null        $loggable_id
 * @property User|null       $user
 * @property Activity|Module $loggable
 */
class Log extends BaseModel
{
    /**
     * Available events.
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
        "user_cancelled_premium",
        // Trainings-related
        "user_payment_succeed_for_training",
        "user_payment_failed_for_training",
        // Sections-related
        "user_payment_succeed_for_section",
        "user_payment_failed_for_section",
    ];

    /** @var bool If timestamps (created_at and updated_at) should be used */
    public $timestamps = true;
    /** @var array */
    public $fillable = ["id", "event", "user_id", "value", "created_at", "loggable_type", "loggable_id"];
    /** @var array The attributes that should be cast to native types */
    protected $casts = ["value" => "array"];

    /**
     * Add a log line the the database.
     *
     * @param string        $event the event name
     * @param User|int|null $user  the user as model, its id or njull for system log
     * @param array         $data  the optional data to associate with the log, such as the loggable type and id
     *
     * @throws Http400InvalidRequestException
     * @throws Http422ModelSaveException
     * @throws Http422ModelValidationException
     * @throws \Exception
     *
     * @return bool
     */
    public static function log(string $event, $user = null, $data = [])
    {
        return self::build($event, $user, $data)
            ->save();
    }

    /**
     * Build a log line.
     *
     * @param string        $event the event name
     * @param User|int|null $user  the user as model, its id or njull for system log
     * @param array         $data  the optional data to associate with the log, such as the loggable type and id
     *
     * @throws Http400InvalidRequestException
     * @throws \Exception
     *
     * @return Log
     */
    public static function build(string $event, $user = null, $data = []): self
    {
        if (!\in_array($event, self::EVENTS, true)) {
            throw new Http400InvalidRequestException("`$event` is not a valid event");
        }

        $logData = [
            "event" => $event,
            "user_id" => $user === null || \is_int($user) ? $user : $user->id,
        ];

        if (!empty($data["loggable_id"]) && !empty($data["loggable_type"])) {
            $logData["loggable_id"] = $data["loggable_id"];
            $logData["loggable_type"] = $data["loggable_type"];
            unset($data["loggable_id"], $data["loggable_type"]);
        }

        if (!empty($data["created_at"])) {
            $logData["created_at"] = $data["created_at"];
            unset($data["created_at"]);
        }

        $logData["value"] = !empty($data) ? $data : null;

        return new self($logData);
    }

    /**
     * Disable updated_at field update; seems to be a hack.
     *
     * @param mixed $value
     *
     * @return $this
     */
    public function setUpdatedAt($value)
    {
        return $this;
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
     * MorphTo Loggable.
     *
     * @return MorphTo
     */
    public function loggable(): MorphTo
    {
        return $this->morphTo();
    }
}
