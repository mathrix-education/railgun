<?php

namespace Tests\API\Settings;

use App\Models\Setting;
use Tests\API\ScopeTestCase;

/**
 * Class SettingsAsPublicTest
 * Test settings as public (non-logged user).
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @copyright Mathrix Education SA
 *
 * @version 4.0.3
 */
class SettingsAsPublicTest extends ScopeTestCase
{
    public function castData(array $data)
    {
        if (!empty($data["value"])) {
            $data["value"] = serialize($data["value"]);
        }

        return parent::castData($data);
    }

    /**
     * GET /settings.
     *
     * @throws \Exception
     */
    public function testAll()
    {
        $this->call("get", "/settings");
        $responseData = $this->getResponseData();
        $settingKeys = [];

        foreach ($responseData as $setting) {
            $settingKeys[] = $setting->key;
        }

        $this->assertHttp200Success();
        $this->assertNull(Setting::whereVisible(false)
            ->whereIn("key", $settingKeys)
            ->first());
    }

    /**
     * GET /settings/{key} with visible setting.
     */
    public function testReadVisible()
    {
        /** @var Setting $setting */
        $setting = Setting::whereVisible(true)->inRandomOrder()->firstOrFail();

        $this->call("get", "/settings/{$setting->key}");
        $this->assertHttp200Success();
    }

    /**
     * GET /settings/{key} with not visible setting.
     */
    public function testReadNotVisible()
    {
        /** @var Setting $setting */
        $setting = Setting::whereVisible(false)->inRandomOrder()->firstOrFail();

        $this->call("get", "/settings/{$setting->key}");

        $this->assertHttp404ModelNotFound();
    }

    /**
     * POST /settings.
     *
     * @throws \Exception
     */
    public function testWrite()
    {
        $this->genericAddUnauthorized();
    }

    /**
     * DELETE /settings/{key}.
     *
     * @throws \Exception
     */
    public function testForgot()
    {
        $setting = Setting::random();

        $this->call("delete", "/settings/{$setting->key}");

        $this->assertHttp401Unauthorized();
        $this->assertInDatabase("settings", ["id" => $setting->id, "key" => $setting->key]);
    }
}
