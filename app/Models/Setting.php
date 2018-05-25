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
 */
class Setting extends BaseModel
{
    protected $fillable = ["id", "key", "value"];


    public static function read(string $key)
    {
        /** @var Setting $setting */
        $setting = Setting::whereKey($key)->firstOrFail();
        return $setting->value;
    }


    public static function write(string $key, $value, bool $visible = true)
    {
        Setting::whereKey($key)->firstOrCreate([
            "key" => $key,
            "value" => $value,
            "visible" => $visible
        ]);
    }


    public static function forgot(string $key)
    {
        Setting::whereKey($key)->firstOrFail()->delete();
    }


    public function getValueAttribute($value)
    {
        return unserialize($value);
    }


    public function setValueAttribute($value)
    {
        return serialize($value);
    }
}
