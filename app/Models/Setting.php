<?php

namespace App\Models;

use Mathrix\Lumen\Bases\BaseModel;

/**
 * Class Setting.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 1.0.1
 *
 * @property int $id
 * @property string $key
 * @property string $value
 * @property bool $visible
 */
class Setting extends BaseModel
{
    /** @var array The guarded attributes */
    protected $fillable = ["id", "key", "value", "visible"];
    /** @var array The validation rules on create */
    protected $rules = [
        "name" => "required|max:255",
        "value" => "required",
        "visible" => "required|boolean",
    ];
    /** @var array The attributes that should be cast to native types */
    protected $casts = [
        "visible" => "boolean",
    ];


    /**
     * Read a setting.
     *
     * @param string $key the setting key
     *
     * @return mixed
     */
    public static function read(string $key)
    {
        /** @var Setting $setting */
        $setting = self::query()
            ->where("key", "=", $key)
            ->firstOrFail();

        return $setting->value;
    }


    /**
     * Write a setting.
     *
     * @param string $key the setting key
     * @param mixed $value the setting value
     * @param bool $visible the setting visibility (true for public visibility, 0 for admin-only)
     *
     * @return mixed
     * @throws \Mathrix\Lumen\Exceptions\ValidationException
     */
    public static function write(string $key, $value, bool $visible = true)
    {
        /** @var Setting $setting */
        $setting = self::query()
            ->where("key", "=", $key)
            ->firstOrNew(["key" => $key]);
        $setting->fill([
            "value" => $value,
            "visible" => $visible
        ]);
        $setting->save();

        return $setting->value;
    }


    /**
     * Delete a setting.
     *
     * @param string $key the setting key
     *
     * @return bool|null
     * @throws \Exception
     */
    public static function forgot(string $key)
    {
        return self::query()
            ->where("key", "=", $key)
            ->firstOrFail()
            ->delete();
    }


    /**
     * Un-serialize value attribute.
     *
     * @param $value
     *
     * @return mixed
     */
    public function getValueAttribute($value)
    {
        return unserialize($value);
    }


    /**
     * Serialize value attribute.
     *
     * @param $value
     */
    public function setValueAttribute($value)
    {
        $this->attributes["value"] = serialize($value);
    }
}
