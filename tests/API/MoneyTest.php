<?php
namespace Tests\API;

use Tests\BaseTestCase;

/**
 * Class MoneyTest
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @copyright Mathrix Education SA
 * @version 4.0.3
 * @package Tests\API
 */
class MoneyTest extends BaseTestCase
{
    /**
     * GET /money/7twanud8v86umjhp8ujhr4y1nv1z7jh60gn96s9q
     */
    public function testLast()
    {
        $this->get("/money/7twanud8v86umjhp8ujhr4y1nv1z7jh60gn96s9q")
            ->assertHttp200Success()
            ->assertJsonKeys(["name", "stripe_plan", "trial_ends_at", "created_at"]);
    }
}
