<?php

namespace Tests\API;

use App\Models\Setting;
use Mathrix\Lumen\JWT\Auth\JWT;
use Tests\APIStatelessTestCase;

/**
 * Class SettingsTest.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0-rc1
 */
class SettingsStatelessTest extends APIStatelessTestCase
{
    /**
     * GET /settings
     */
    public function testAll()
    {
        JWT::autoScope("get", "/settings");
        $this->json("get", "/settings");

        $this->assertResponseOk();
        $this->assertOpenAPIResponse();
    }


    /**
     * GET /settings/{key}
     */
    public function testRead()
    {
        $setting = Setting::random(["visible", "=", true]);

        $this->json("get", "/settings/{$setting->key}");

        $this->assertResponseOk();
        $this->assertOpenAPIResponse();
    }


    /**
     * PATCH /settings/{key}
     */
    public function testWrite()
    {
        $setting = Setting::random(["visible", "=", true]);
        $payload = [
            "color" => $this->faker->rgbColorAsArray,
            "email" => $this->faker->email
        ];

        $uri = "/settings/{$setting->key}";
        JWT::autoScope("patch", $uri);
        $this->json("patch", $uri, [
            "value" => $payload,
            "visible" => true
        ]);

        $this->assertResponseOk();
        $this->assertEquals($payload, (array)$this->getJsonResponseValue("value"));
        $this->assertOpenAPIResponse();
    }


    /**
     * DELETE /settings/{key}
     */
    public function testForgot()
    {
        $setting = Setting::random(["visible", "=", true]);

        $uri = "/settings/{$setting->key}";
        JWT::autoScope("delete", $uri);
        $this->json("delete", $uri);

        $this->assertResponseOk();
        $this->assertOpenAPIResponse();
        $this->assertNotInDatabase("settings", ["key" => $setting->key]);
    }
}
