<?php

declare(strict_types=1);

namespace Railgun\Models\Traits;

use Illuminate\Database\Eloquent\Model;
use function is_string;
use function uuid_create;
use function uuid_is_valid;

/**
 * @mixin Model
 */
trait UUID
{
    /**
     * On creating, set the primary key as a new uuid.
     *
     * @noinspection PhpUnused
     */
    public static function bootUUID(): void
    {
        static::creating(static function (Model $model): void {
            if (is_string($model->getKey()) && uuid_is_valid($model->getKey())) {
                return;
            }

            $model->setAttribute($model->getKeyName(), uuid_create());
        });
    }

    /**
     * Initialize the model properties to be compatible with the uuid column type.
     *
     * @link https://laravel.com/docs/7.x/eloquent Section "Primary Keys".
     *
     * @noinspection PhpUnused
     */
    public function initializeUUID(): void
    {
        $this->keyType      = 'string';
        $this->incrementing = false;
    }
}
