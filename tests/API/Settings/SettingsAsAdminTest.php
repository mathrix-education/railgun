<?php
namespace Tests\API\Settings;

use App\Models\Setting;

/**
 * Class SettingsAsAdminTest
 * Test settings as admin
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @copyright Mathrix Education SA
 * @version 4.0.3
 * @package Tests\Settings
 */
class SettingsAsAdminTest extends SettingsAsModeratorTest
{
    /**
     * GET /settings
     *
     * @throws \Exception
     */
    public function all()
    {
        $this->call("get", "/settings/all");
        $responseData = $this->getResponseData();

        $this->assertHttp200Success();
        $this->assertNull(Setting::whereVisible(false)
            ->whereIn("key", array_keys((array)$responseData))
            ->first());
    }


    /**
     * GET /settings/{key} with visible setting
     */
    public function testReadVisible()
    {
        /** @var Setting $setting */
        $setting = Setting::whereVisible(true)->inRandomOrder()->firstOrFail();

        $this->call("get", "/settings/{$setting->key}");
        $this->assertHttp200Success();
    }


    /**
     * GET /settings/{key} with not visible setting
     */
    public function testReadNotVisible()
    {
        /** @var Setting $setting */
        $setting = Setting::whereVisible(false)->inRandomOrder()->firstOrFail();

        $this->call("get", "/settings/{$setting->key}");

        $this->assertHttp200Success();
    }


    /**
     * POST /settings
     *
     * @throws \Exception
     */
    public function testWrite()
    {
        $this->genericAddSuccess();
    }


    /**
     * DELETE /settings/{key}
     *
     * @throws \Exception
     */
    public function testForgot()
    {
        $setting = Setting::random();

        $this->call("delete", "/settings/{$setting->key}");

        $this->assertHttp200Success();
        $this->assertNotInDatabase("settings", ["id" => $setting->id, "key" => $setting->key]);
    }
}
