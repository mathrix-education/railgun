<?php
namespace Tests\Utils;

use App\Models\Setting;
use Tests\BaseTestCase;

/**
 * Class SettingsUtilsTest
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @copyright Mathrix Education SA
 * @version 4.0.3
 * @package Tests\Utils
 */
class SettingsUtilsTest extends BaseTestCase
{
    public function testRead()
    {
        $settingKey = Setting::random()->key;
        $value = Setting::read($settingKey);

        $this->assertInDatabase("settings", [
            "key" => $settingKey,
            "value" => serialize($value)
        ]);
    }


    public function testWrite()
    {
        $setting = factory(Setting::class)->raw();

        Setting::write($setting["key"], $setting["value"], $setting["visible"]);

        $setting["value"] = serialize($setting["value"]);

        $this->assertInDatabase("settings", $setting);
    }
}
