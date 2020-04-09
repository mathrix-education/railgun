<?php

declare(strict_types=1);

namespace Railgun\Models;

use Carbon\Carbon;
use Illuminate\Notifications\DatabaseNotification;
use JsonException;
use function is_serialized;
use function json_decode;
use function unserialize;
use const JSON_THROW_ON_ERROR;

/**
 * Overridden version of the {@see DatabaseNotification} model which supports the Eloquent models serialization.
 *
 * @property-read string     $id
 * @property-read string     $type
 * @property-read string     $notifiable_type
 * @property-read int|string $notifiable_id
 * @property-read mixed      $data
 * @property-read Carbon     $read_at
 * @property-read Carbon     $created_at
 * @property-read Carbon     $updated_at
 */
class Notification extends DatabaseNotification
{
    /**
     * Parse the notification body.
     *
     * @return mixed[] The notification data.
     *
     * @throws JsonException
     */
    public function getDataAttribute(): array
    {
        $serialized = json_decode($this->attributes['data'], true, 512, JSON_THROW_ON_ERROR);
        $data       = [];

        foreach ($serialized as $k => $v) {
            /** @noinspection UnserializeExploitsInspection */
            $data[$k] = is_serialized($v) ? unserialize($v) : $v;
        }

        return $data;
    }
}
