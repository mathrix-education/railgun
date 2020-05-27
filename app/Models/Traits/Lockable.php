<?php

declare(strict_types=1);

namespace Railgun\Models\Traits;

use Illuminate\Database\Eloquent\Concerns\HasAttributes;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Railgun\Support\PermissionGate;
use function class_basename;
use function isPremium;
use function property_exists;

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

    /**
     * @return bool if the model should be stripped
     */
    protected function isLocked(): bool
    {
        $permission = (string)Str::of(class_basename($this))->plural()->lower()->append(':read');

        if (Gate::allows(PermissionGate::NAME, $permission)) {
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
     * @return string get the lock mode.
     */
    protected function getLockMode(): string
    {
        return property_exists($this, 'strip') ? $this->strip : 'premium';
    }
}
