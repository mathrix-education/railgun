<?php

namespace App\Console\Commands\Migrations;

use App\Models\Activity;
use App\Models\Module;
use App\Models\Notion;

/**
 * Trait CopyTrait.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0
 */
trait CopyTrait
{
    /**
     * Copy an notion
     *
     * @param int $notionId
     * @param int $chapterId
     * @param int $order
     *
     * @throws \App\Exceptions\Http422ModelSaveException
     * @throws \App\Exceptions\Http422ModelValidationException
     */
    public function copyNotion(int $notionId, int $chapterId, int $order)
    {
        /** @var Notion $notion */
        $notion = Notion::query()->findOrFail($notionId);
        $data = $notion->toArray();
        unset($data["id"]);
        $copyNotion = new Notion($data);
        $copyNotion->chapter_id = $chapterId;
        $copyNotion->save();

        foreach ($notion->activities as $order => $activity) {
            $this->copyActivity($activity->id, $copyNotion->id, $order);
        }
    }


    /**
     * Copy an activity
     *
     * @param int $activityId
     * @param int $notionId
     * @param int $order
     *
     * @throws \App\Exceptions\Http422ModelSaveException
     * @throws \App\Exceptions\Http422ModelValidationException
     */
    public function copyActivity(int $activityId, int $notionId, int $order)
    {
        /** @var Activity $activity */
        $activity = Activity::query()->findOrFail($activityId);
        $data = $activity->toArray();
        unset($data["id"]);
        $copyActivity = new Activity($data);
        $copyActivity->order = $order;
        $copyActivity->notion_id = $notionId;
        $copyActivity->save();

        foreach ($activity->modules as $order => $module) {
            $this->copyModule($module->id, $copyActivity->id, $order);
        }
    }


    /**
     * Copy a module
     *
     * @param int $moduleId
     * @param int $activityId
     * @param int $order
     *
     * @throws \App\Exceptions\Http422ModelSaveException
     * @throws \App\Exceptions\Http422ModelValidationException
     */
    public function copyModule(int $moduleId, int $activityId, int $order)
    {
        $module = Module::query()->findOrFail($moduleId);
        $data = $module->toArray();
        unset($data["id"]);
        $copyModule = new Module($data);
        $copyModule->order = $order;
        $copyModule->activity_id = $activityId;
        $copyModule->save();
    }
}
