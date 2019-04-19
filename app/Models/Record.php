<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Mathrix\Lumen\Bases\BaseModel;
use Mathrix\Lumen\Exceptions\Http\Http400BadRequestException;
use Mathrix\Lumen\Exceptions\ValidationException;

/**
 * Class Record.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0-rc1
 *
 * @property string $service
 * @property string $event
 * @property string $status
 * @property array $payload
 * @property int $user_id
 * ---
 * @property User $user
 */
class Record extends BaseModel
{
    protected $fillable = ["service", "event", "status", "payload", "user_id"];
    protected $casts = ["payload" => "array"];
    public $timestamps = true;


    /**
     * @param User $user
     * @param string $service
     * @param string $event
     * @param string $status
     * @param array $payload
     *
     * @return Record
     * @throws Http400BadRequestException
     */
    public static function record(User $user, string $service, string $event, string $status, array $payload)
    {
        $record = new Record([
            "service" => $service,
            "event" => $event,
            "status" => $status,
            "payload" => $payload,
            "user_id" => $user->id
        ]);
        $record->save();

        return $record;
    }


    /**
     * Prevent records to be modified, since they are immutable by essence.
     *
     * @param array $options
     *
     * @return bool
     * @throws Http400BadRequestException
     */
    public function save(array $options = [])
    {
        if (!$this->exists) {
            return parent::save($options);
        } else {
            throw new Http400BadRequestException(null, "Records are immutable by definition.");
        }
    }


    /**
     * The user.
     *
     * @return BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
