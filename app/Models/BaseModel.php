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
            $this->save();
        }

        return Str::of(class_basename($this))->slug()->lower() . '_' . $this->getKey();
    }
}
