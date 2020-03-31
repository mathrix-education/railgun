<?php

declare(strict_types=1);

namespace Railgun\Models\Traits;

use Illuminate\Database\Eloquent\Concerns\HasAttributes;
use Illuminate\Support\Str;
use function class_basename;
use function isPremium;
use function property_exists;
use function scope;

/**
 * Allow models to be locked.
 *
 * @property string $strip
 * @property bool   $premium
 * @mixin HasAttributes
 */
trait Lockable
{
    /**
     * @return string get the lock mode.
     */
    protected function getLockMode(): string
    {
        return property_exists($this, 'strip') ? $this->strip : 'premium';
    }

    /**
     * @return bool if the model should be stripped
     */
    protected function isLocked(): bool
    {
        if (scope(Str::snake(class_basename($this)) . ':read')) {
            return false;
        }

        switch ($this->getLockMode()) {
            case 'premium':
                return $this->premium && !isPremium();
            case 'product':
                return false; // TODO: implement training strip
            default:
                return true;
        }
    }

    /**
     * @param string $key   The attribute key
     * @param mixed  $strip The stripped value
     *
     * @return mixed
     */
    protected function lock(string $key, $strip)
    {
        $raw    = $this->getAttributeFromArray($key);
        $casted = $this->hasCast($key) ? $this->castAttribute($key, $raw) : $raw;

        return $this->isLocked() ? $strip : $casted;
    }
}
