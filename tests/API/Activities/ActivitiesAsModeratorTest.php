<?php
namespace Tests\API\Activities;

use App\Models\Activity;
use App\Models\Module;
use App\Models\Notion;

/**
 * Class ActivitiesAsModeratorTest.
 * Test activities as moderator.
 *
 * @author    Mathieu Bour <mathieu@mathrix.fr>
 * @author    Jérémie Levain <munezero999@live.fr>
 * @since     1.0.0
 * @copyright Mathrix Education SA
 * @package   Tests\API\Activities
 */
class ActivitiesAsModeratorTest extends ActivitiesAsRegisteredTest
{
    /**
     * GET /activities.
     *
     * @throws \Exception
     */
    public function testIndex()
    {
        $this->genericIndexSuccess();
    }

    /**
     * POST /activities.
     *
     * @throws \Exception
     */
    public function testAdd()
    {
        $this->genericAddSuccess();
    }

    /**
     * PATCH /activities/{id}.
     *
     * @throws \Exception
     */
    public function testEdit()
    {
        $this->genericEditSuccess();
    }

    /**
     * PATCH /activities/{id} with linkable models.
     *
     * @throws \Exception
     */
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
            ),
        ];

        $this->genericEditSuccess(["link" => $link]);

        foreach ($link["notions"] as $order => $id) {
            $this->assertInDatabase("activity_notion", [
                "activity_id" => $this->editModelId,
                "notion_id" => $id,
                "order" => $order,
            ]);
        }

        foreach ($link["modules"] as $order => $id) {
            $this->assertInDatabase("activity_module", [
                "activity_id" => $this->editModelId,
                "module_id" => $id,
                "order" => $order,
            ]);
        }
    }

    /**
     * PATCH /activities/{id} while re-ordering module ids.
     */
    public function testEditReorder()
    {
        do {
            $activity = Activity::random();
            $moduleIds = $activity->modules()->get()->pluck("id");
        } while ($moduleIds->isEmpty());

        $moduleIds->shuffle();

        $this->json("patch", "/activities/{$activity->id}", [
            "link" => [
                "modules" => $moduleIds,
            ],
        ]);

        foreach ($moduleIds as $order => $moduleId) {
            $this->assertInDatabase("activity_module", [
                "activity_id" => $activity->id,
                "module_id" => $moduleId,
                "order" => $order,
            ]);
        }
    }

    /**
     * DELETE /activities/{id}.
     *
     * @throws \Exception
     */
    public function testDelete()
    {
        $this->genericDeleteSuccess();
        $this->assertNotInDatabase("activity_notion", ["activity_id" => $this->deleteModelId]);
        $this->assertNotInDatabase("activity_module", ["activity_id" => $this->deleteModelId]);
    }
}
