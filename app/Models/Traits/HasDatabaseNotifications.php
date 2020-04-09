<?php

declare(strict_types=1);

namespace Railgun\Models\Traits;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasRelationships;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Railgun\Models\Notification;

/**
 * Overridden version of {@see \Illuminate\Notifications\HasDatabaseNotifications} to implement the custom
 * {@see Notification} model.
 *
 * @property-read Collection|Notification[] $notifications
 * @property-read Collection|Notification[] $unreadNotifications
 * @property-read Collection|Notification[] $readNotifications
 * @mixin HasRelationships
 */
trait HasDatabaseNotifications
{
    /**
     * Get the entity's notifications.
     */
    public function notifications(): MorphMany
    {
        return $this->morphMany(Notification::class, 'notifiable')
            ->orderBy('created_at', 'desc');
    }

    /**
     * Get the entity's read notifications.
     */
    public function readNotifications(): MorphMany
    {
        return $this->notifications()->whereNotNull('read_at');
    }

    /**
     * Get the entity's unread notifications.
     */
    public function unreadNotifications(): MorphMany
    {
        return $this->notifications()->whereNull('read_at');
    }
}
