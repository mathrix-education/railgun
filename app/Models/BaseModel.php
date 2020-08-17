<?php

declare(strict_types=1);

namespace Railgun\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Railgun\Models\Traits\Randomizable;
use function class_basename;

class BaseModel extends Model
{
    use Randomizable;

    /**
     * @return string The full-qualified key.
     *
     * @example "user_12" for user with id 12.
     */
    public function getGlobalKey(): string
    {
        if (!$this->getKey()) {
            $this->save(); // @¢odeCoverageIgnore
        }

        return Str::of(class_basename($this))->slug()->lower() . '_' . $this->getKey();
    }

    /**
     * Save the model to the database without triggering any event.
     *
     * @param mixed[] $options The save options.
     *
     * @return bool If the save succeeded.
     */
    public function saveWithoutEvents(array $options = []): bool
    {
        return static::withoutEvents(fn() => $this->save($options));
    }
}
