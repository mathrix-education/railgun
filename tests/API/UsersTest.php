<?php

namespace Tests\API;

use App\Models\Module;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;
use Laravel\Passport\Passport;
use Mathrix\Lumen\JWT\Auth\JWT;
use Mathrix\Lumen\Tests\REST\Standard\StandardGetTest;
use Mathrix\Lumen\Tests\REST\Standard\StandardIndexTest;
use Tests\APITestCase;

/**
 * Class UsersTest.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0-rc1
 */
class UsersTest extends APITestCase
{
    use StandardIndexTest, StandardGetTest;


    /**
     * POST /users
     */
    public function testPost()
    {
        $payload = [
            "email" => $this->faker->email,
            "password" => env("DB_PASSWORD")
        ];

        $this->json("post", "/users", $payload);

        $this->assertResponseOk();
        $this->assertOpenAPIResponse();
        $this->assertInDatabase("users", ["email" => $payload["email"]]);
    }


    /**
     * GET /users/{userId}
     */
    public function testGetAdmin()
    {
        /** @var User $user */
        $user = User::scope("admin")->inRandomOrder()->firstOrFail();
        JWT::actingAs($user);
        $this->json("get", "/users/{$user->id}");

        $this->assertResponseOk();
        $this->assertOpenAPIResponse();
    }


    /**
     * GET /users/{userId} as a simple registered user
     */
    public function testGetSomeoneElse()
    {
        /** @var User $user */
        $user = User::scope("registered")->inRandomOrder()->firstOrFail();
        JWT::actingAs($user);
        $someone = User::random(["id", "!=", $user->id]);

        $this->json("get", "/users/{$someone->id}");

        $this->assertResponseStatus(401);
        $this->assertOpenAPIResponse();
    }


    /**
     * PATCH /users/{userId}
     */
    public function testPatch()
    {
        $newPassword = $this->faker->password(20, 20);
        $payload = array_merge(
            Arr::except(
                $this->getFactoryBuilder([])->raw(),
                ["password", "token", "points", "level", "settings", "created_at", "updated_at"]
            ), [
                "current_password" => env("DB_PASSWORD"),
                "password" => $newPassword
            ]
        );

        /** @var User $user */
        $user = JWT::withScopes("registered");

        $this->json("patch", "/users/{$user->id}", $payload);

        $this->assertResponseOk();
        $this->assertOpenAPIResponse();
        $this->assertInDatabase("users", Arr::except($payload, ["current_password", "password"," settings"]));

        $new = $payload["password"];
        $hashed = $user->refresh()->password;
        $this->assertTrue(Hash::check($new, $hashed), "Hash does not match the new password.");
    }


    /**
     * PATCH /users/{userId} with the current_password
     */
    public function testPatchNoCurrentPassword()
    {
        $newPassword = $this->faker->password(20, 20);
        $payload = array_merge(
            Arr::except(
                $this->getFactoryBuilder([])->raw(),
                ["password", "token", "points", "level", "created_at", "updated_at"]
            ), [
                "password" => $newPassword
            ]
        );

        ;
        /** @var User $user */
        $user = JWT::withScopes("registered");

        $this->json("patch", "/users/{$user->id}", $payload);

        $this->assertResponseStatus(422);
        $this->assertOpenAPIResponse();
        $this->assertNotInDatabase("users", Arr::except($payload, ["current_password", "password"]));

        $hashed = $user->refresh()->password;
        $this->assertTrue(Hash::check(env("DB_PASSWORD"), $hashed), "Hash does not match the old password.");
    }


    /**
     * PATCH /users/{userId}
     * Test while patching settings for last session.
     */
    public function testPatchLastSession()
    {
        /** @var User $user */
        $user = JWT::withScopes("registered");;

        $module = Module::random(["visible", "=", true]);
        $settings = array_merge($user->settings, ["last_session" => [
            "{$module->activity->notion->chapter->discipline->id}" => $module->id
        ]]);

        $this->json("patch", "/users/{$user->id}", ["settings" => $settings]);

        $this->assertResponseOk();
        $this->assertOpenAPIResponse();
    }


    /**
     * POST /users/{userId}/points
     */
    public function testPointsWithModule()
    {
        $user = JWT::withScopes("registered");
        $pointsBefore = $this->requester->points;
        $module = Module::random(["visible", "=", true]);

        $this->json("post", "users/{$user->id}/points", [
            "loggable_type" => "module",
            "loggable_id" => $module->id,
            "points" => 100
        ]);

        $this->assertResponseOk();
        $this->assertOpenAPIResponse();
        $this->assertEquals($pointsBefore + 100, $this->getJsonResponseValue("points"));
    }
}
