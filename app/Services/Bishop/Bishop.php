<?php

namespace App\Services\Bishop;

use Carbon\Carbon;
use DateTime;
use Illuminate\Support\Str;

abstract class Bishop implements \JsonSerializable
{
    /** @var array The bishop attributes. */
    protected $attributes;
    /** @var array The dates */
    protected $dates = [];
    /** @var string The date format */
    protected $dateFormat = "c";
    protected $virtual = [];


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


    public function getRawAttribute(string $key)
    {
        if (isset($this->attributes[$key])) {
            return $this->attributes[$key];
        } else {
            return null;
        }
    }


    public function castAttribute($key, $value)
    {
        if ($this->isDate($key)) {
            return $this->toCarbon($value);
        } else if (is_array($value)) {
            $yield = [];

            foreach ($value as $subKey => $subValue) {
                $yield[$subKey] = $this->castAttribute("$key.$subKey", $subValue);
            }

            return $yield;
        } else {
            return $value;
        }
    }


    public function isDate($key)
    {
        return in_array($key, $this->dates);
    }


    /**
     * Create a Carbon instance from a value
     *
     * @param DateTime|string|integer $value
     *
     * @return Carbon|null
     */
    public function toCarbon($value): ?Carbon
    {
        if ($value instanceof DateTime) {
            return Carbon::instance($value);
        } else if (is_integer($value)) {
            return Carbon::createFromTimestamp($value);
        } else {
            try {
                return new Carbon($value);
            } catch (\Exception $e) {
                return null;
            }
        }
    }


    public function serializeAttribute($key, $value)
    {
        $casted = $this->castAttribute($key, $value);

        if ($this->isDate($key)) {
            /** @var Carbon $casted */
            return $this->serializeDate($casted);
        } else if (is_array($value)) {
            $yield = [];

            foreach ($casted as $subKey => $subValue) {
                $yield[$subKey] = $this->serializeAttribute("$key.$subKey", $subValue);
            }

            return $yield;
        } else if ($value instanceof Bishop) {
            return $value->jsonSerialize();
        } else {
            return $casted;
        }
    }


    /**
     * Prepare a date for array / JSON serialization.
     *
     * @param Carbon $date
     *
     * @return string
     */
    protected function serializeDate(Carbon $date)
    {
        return $date->format($this->dateFormat);
    }


    public function toArray(): array
    {
        $array = [];

        foreach ($this->attributes as $key => $value) {
            $array[$key] = $this->serializeAttribute($key, $value);
        }

        return $array;
    }


    public function jsonSerialize()
    {
        return $this->toArray();
    }
}
