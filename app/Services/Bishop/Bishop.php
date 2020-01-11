<?php

declare(strict_types=1);

namespace App\Services\Bishop;

use Carbon\Carbon;
use DateTime;
use JsonSerializable;
use RuntimeException;
use Throwable;
use function in_array;
use function is_array;
use function is_int;

abstract class Bishop implements JsonSerializable
{
    /** @var array The bishop attributes. */
    protected array $attributes = [];
    /** @var array The dates. */
    protected array $dates = [];
    /** @var string The date format. */
    protected string $dateFormat = 'c';
    /** @var array The virtual attributes. */
    protected array $virtual = [];

    public function __construct(array $attributes)
    {
        foreach ($attributes as $key => $value) {
            $this->setRawAttribute($key, $value);
        }
    }

    public function setRawAttribute(string $key, $value): void
    {
        $this->attributes[$key] = $value;
    }

    public function toArray(): array
    {
        $array = [];

        foreach ($this->attributes as $key => $value) {
            $array[$key] = $this->serializeAttribute($key, $value);
        }

        return $array;
    }

    public function serializeAttribute($key, $value)
    {
        $casted = $this->castAttribute($key, $value);

        if ($this->isDate($key)) {
            /** @var Carbon $casted */
            return $this->serializeDate($casted);
        }

        if (is_array($value)) {
            $yield = [];

            foreach ($casted as $subKey => $subValue) {
                $yield[$subKey] = $this->serializeAttribute("$key.$subKey", $subValue);
            }

            return $yield;
        }

        if ($value instanceof Bishop) {
            return $value->jsonSerialize();
        }

        return $casted;
    }

    public function castAttribute($key, $value)
    {
        if ($this->isDate($key)) {
            return $this->toCarbon($value);
        }

        if (is_array($value)) {
            $yield = [];

            foreach ($value as $subKey => $subValue) {
                $yield[$subKey] = $this->castAttribute("$key.$subKey", $subValue);
            }

            return $yield;
        }

        return $value;
    }

    public function isDate($key): bool
    {
        return in_array($key, $this->dates, true);
    }

    /**
     * Create a Carbon instance from a value
     *
     * @param DateTime|string|int $value
     *
     * @return Carbon|null
     */
    public function toCarbon($value): ?Carbon
    {
        if ($value instanceof DateTime) {
            return Carbon::instance($value);
        }

        if (is_int($value)) {
            return Carbon::createFromTimestamp($value);
        }

        try {
            return new Carbon($value);
        } catch (Throwable $e) {
            return null;
        }
    }

    /**
     * Prepare a date for array / JSON serialization.
     *
     * @param Carbon $date
     *
     * @return string
     */
    protected function serializeDate(Carbon $date): string
    {
        return $date->format($this->dateFormat);
    }

    public function jsonSerialize()
    {
        return $this->toArray();
    }

    public function __get($key)
    {
        return $this->getAttribute($key);
    }

    public function getAttribute($key)
    {
        return $this->castAttribute($key, $this->getRawAttribute($key));
    }

    public function getRawAttribute(string $key)
    {
        return $this->attributes[$key] ?? null;
    }

    public function __set($name, $value): void
    {
        throw new RuntimeException(static::class . ' is immutable');
    }

    public function __isset($name): bool
    {
        return isset($this->{$name}) || isset($this->attributes[$name]);
    }
}
