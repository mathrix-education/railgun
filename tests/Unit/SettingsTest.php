<?php
namespace Tests\Unit;

use App\Models\Setting;
use Tests\BaseTestCase;
use Tests\Utils\Traits\Assertions\DatabaseAssertionsTrait;
use Tests\Utils\Traits\Assertions\HttpAssertionsTrait;
use Tests\Utils\Traits\Assertions\JsonAssertionsTrait;
use Tests\Utils\Traits\Assertions\NotificationsAssertionsTrait;

/**
 * Class SettingsUtilsTest
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @copyright Mathrix Education SA
 * @version 4.0.3
 * @package Tests\Utils
 */
class SettingsTest extends BaseTestCase
{
    use DatabaseAssertionsTrait, HttpAssertionsTrait, JsonAssertionsTrait, NotificationsAssertionsTrait;


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
