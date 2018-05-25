<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Builder;

/**
 * Model Section
 *
 * @package App\Models
 * @since 1.0.1
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @copyright Mathrix Education SA
 *
 * @property int $id
 * @property string $key
 * @property string $value
 * @property bool $visible
 * @method static Builder|Section whereId($value)
 * @method static Builder|Section whereKey($value)
 * @method static Builder|Section whereValue($value)
 * @method static Builder|Section whereVisible($value)
 */
class Setting extends BaseModel
{
    /** @var array The guarded attributes */
    protected $fillable = ["id", "key", "value"];
    /** @var array The validation rules on create */
    protected $rules = [
        "name" => "required|max:255",
        "value" => "required",
        "visible" => "required|boolean",
    ];
    /** @var array The attributes that should be cast to native types */
    protected $casts = [
        "visible" => "boolean"
    ];


    /**
     * Read a setting
     *
     * @param string $key the setting key
     *
     * @return mixed
     */
    public static function read(string $key)
    {
        /** @var Setting $setting */
        $setting = Setting::whereKey($key)->firstOrFail();
        return $setting->value;
    }


    /**
     * Write a setting
     *
     * @param string $key the setting key
     * @param mixed $value the setting value
     * @param bool $visible the setting visibility (true for public visibility, 0 for admin-only)
     *
     * @return bool
     */
    public static function write(string $key, $value, bool $visible = true)
    {
        $setting = Setting::whereKey($key)->firstOrCreate([
            "key" => $key,
            "value" => $value,
            "visible" => $visible
        ]);

        return $setting instanceof Setting;
    }


    /**
     * Delete a setting
     *
     * @param string $key the setting key
     *
     * @return bool|null
     */
    public static function forgot(string $key)
    {
        return Setting::whereKey($key)->firstOrFail()->delete();
    }


    /**
     * Un-serialize value attribute
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
     * Serialize value attribute
     *
     * @param $value
     *
     * @return string
     */
    public function setValueAttribute($value)
    {
        return serialize($value);
    }
}
