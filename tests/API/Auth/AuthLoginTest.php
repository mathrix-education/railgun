<?php

namespace Tests\API\Auth;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Tests\APIStatelessTestCase;

/**
 * Class AuthTest.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0-rc1
 */
class AuthLoginTest extends APIStatelessTestCase
{
    private const NEW_TOKEN = "Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJFUzUxMiJ9.eyJpc3MiOiJNYXRocml4IEVkdWNhdGlvbiBBUEkiLCJz"
    . "dWIiOjM3LCJhdWQiOiJNYXRocml4IEVkdWNhdGlvbiBBUEkiLCJleHAiOjE1NjQ4NTE3MjcsIm5iZiI6MTU1NjkwMjkyNywiaWF0IjoxNTU2"
    . "OTAyOTI3LCJqdGkiOiIyNzExOTNlNy01NjIxLTQzZDctYjQxMi04NDY5YjVlNjljNTIifQ.AQiFnaawtWC2fbwn9UXFyUHV2zmK2Kl7KUSD4"
    . "I7T2-ePO_UL0gQ9Akx2LSYdfeJbRX69JHlEcaxy1gR-5c4_htmPALn2k9-iIErBDRrw-8ZBk39ow1Jr51jEdatH9uRvX84xDjS3crPUliHhH"
    . "z9kzYZl_Tv8MnEKdPQPE0fDBE1x_gVT";

    private const OLD_TOKEN = "Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6ImJlZDRhMWMyM2VkMzJmNGNjNjFhZDRhMDVjN"
    . "TJjNWMzOWMxNTVmZmI2Yjc1MjM3OGQ3YzE5Mzk1YTc5YTVmNzVhNzhjYjNkNTA1YzAwYzljIn0.eyJhdWQiOiIyIiwianRpIjoiYmVkNGExY"
    . "zIzZWQzMmY0Y2M2MWFkNGEwNWM1MmM1YzM5YzE1NWZmYjZiNzUyMzc4ZDdjMTkzOTVhNzlhNWY3NWE3OGNiM2Q1MDVjMDBjOWMiLCJpYXQiO"
    . "jE1NTY4MjEwNjEsIm5iZiI6MTU1NjgyMTA2MSwiZXhwIjoxNTU5NDk5NDYxLCJzdWIiOiIxMDY0MTciLCJzY29wZXMiOlsicmVnaXN0ZXJlZ"
    . "CJdfQ.pNNSgaTeKae_Wbst9rQ8-T9lEQQQojSn0uFc-km9sZmRKfO67WaGJnBMFCPv8FhlTPwXj-2xW-C9UZ3Iuukb4brbQPceIchF7VkmdR"
    . "Z1Z2PtxtrBl7FLoJv51_3uw47PIuJGWjg3NG3NN_73_lh4ESaOB_d-KxK0q0QEw0HSyxMBUNesOHaBLuxjjgyKEL2Ivx0iJ27evr3oAvDTvV"
    . "ImwJ-yrB5YY3t5AlNR2U-QV6f6trQ3EYrxL6R9Rcc_oqCfcxC0EYwbJ1rtU65NPHMqOINiAa3drzW9V-Z5PXvFiYnm5dq1TwKY9teUPSo6HJ"
    . "npNorl7wtyjCtjRJdffckUGiKwL8_6rAPefxDVsaSzwBFgDEeXM54vtcWeVM-Xsgxt27FAR4EuThTxt8mxfpPl1G1852jjq2J2al-2M1uZ98"
    . "EF2vNoyzIIFH-ANZTFc2f98YlEudNEOKDX9s5uNM_8Cay58HMlXi_I6uVax_Gt8d78WoYjtGq1n1DLb24TqYwsbFEsNh9o_c0Vnbi9XBhWk3"
    . "Fqf_yBxlWH2pk_hUphgV3yDYC9RuVM0q4wOPxBVbOhQ1EDw_LIqEeAzdQHgBx6LGetVMkxv0nzbwPh_3eW8isGMadipcFM--kAPAsit2ot3c"
    . "A4jn6EQjMMZqz0NXN-_WvD7r2SCyiz97w--uWTWlo";


    /**
     * POST /auth/login
     */
    public function testLogin()
    {
        $user = User::random();
        $oldHash = $user->password; // Save the current hash for revert after the test
        $newPassword = $this->faker->password;
        $user->password = $newPassword;
        $user->save();

        $this->json("post", "/auth/login", [
            "email" => $user->email,
            "password" => $newPassword
        ]);

        $this->assertResponseOk();
        $this->assertOpenAPIResponse();

        // Revert password change
        DB::update("UPDATE users SET password = ? WHERE id = ?", [$oldHash, $user->id]);
    }


    /**
     * POST /auth/login
     */
    public function testLoginFailed()
    {
        $user = User::random();

        $this->json("post", "/auth/login", [
            "email" => $user->email,
            "password" => "incorrect-password"
        ]);

        $this->assertOpenAPIResponse();
    }
}
