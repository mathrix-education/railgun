<?php
namespace Tests\Activities;

use App\Models\Module;
use App\Models\Notion;
use Tests\ScopeTestCase;

/**
 * Class ActivitiesAsModeratorTest
 * Test activities as moderator
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @copyright Mathrix
 * @version 1.0.0
 * @package Tests\Activities
 */
class ActivitiesAsModeratorTest extends ScopeTestCase
{
    public function testIndex()
    {
        $this->genericIndexSuccess();
    }


    public function testAdd()
    {
        $this->genericAddSuccess();
    }


    public function testEdit()
    {
        $this->genericEditSuccess();
    }


    public function testEditWithLink()
    {
        $link = [
            "notions" => $this->faker->randomElements(
                Notion::inRandomOrder()->pluck("id")->toArray(),
                $this->faker->numberBetween(3, 5)
            ),
            "modules" => $this->faker->randomElements(
                Module::inRandomOrder()->pluck("id")->toArray(),
                $this->faker->numberBetween(3, 5)
            )
        ];

        $this->genericEditSuccess(["link" => $link]);

        foreach($link["notions"] as $order => $id) {
            $this->assertInDatabase("activity_notion", [
                "activity_id" => $this->editModelId,
                "notion_id" => $id,
                "order" => $order
            ]);
        }

        foreach($link["modules"] as $order => $id) {
            $this->assertInDatabase("activity_module", [
                "activity_id" => $this->editModelId,
                "module_id" => $id,
                "order" => $order
            ]);
        }

    }


    public function testDelete()
    {
        $this->genericDeleteSuccess();
        $this->assertNotInDatabase('activity_notion', ['activity_id' => $this->deleteModelId]);
        $this->assertNotInDatabase('activity_module', ['activity_id' => $this->deleteModelId]);
    }


    public function testMarkFavorite()
    {
        $this->sameAs('registered');
    }


    public function testMarkStatus()
    {
        $this->sameAs('registered');
    }
}