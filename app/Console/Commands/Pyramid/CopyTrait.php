<?php

namespace App\Console\Commands\Pyramid;

use App\Models\Activity;
use App\Models\Module;
use App\Models\Notion;
use Illuminate\Support\Facades\DB;

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
     */
    public function copyNotion(int $notionId, int $chapterId, int $order)
    {
        $copyNotion = DB::table("notions")->where("id", "=", $notionId)->first();
        $notionId = $copyNotion->id;

        unset($copyNotion->id);
        $copyNotion->order = $order;
        $copyNotion->chapter_id = $chapterId;
        DB::table("notions")->insert((array)$copyNotion);

        $copyNotionId = DB::select("SELECT LAST_INSERT_ID() as copyNotionId")[0]->copyNotionId;

        $activityIds = DB::table("activity_notion")
            ->select("activity_id")
            ->where("notion_id", "=", $notionId)
            ->orderBy("order")
            ->get()
            ->pluck("activity_id");

        foreach ($activityIds as $order => $activityId) {
            $this->copyActivity($activityId, $copyNotionId, $order);
        }
    }


    /**
     * Copy an activity
     *
     * @param int $activityId
     * @param int $notionId
     * @param int $order
     */
    public function copyActivity(int $activityId, int $notionId, int $order)
    {
        /** @var Activity $activity */
        $copyActivity = DB::table("activities")->where("id", "=", $activityId)->first();

        unset($copyActivity->id);
        $copyActivity->order = $order;
        $copyActivity->notion_id = $notionId;
        DB::table("activities")->insert((array)$copyActivity);

        $moduleIds = DB::table("activity_module")
            ->select("module_id")
            ->where("activity_id", "=", $activityId)
            ->orderBy("order")
            ->get()
            ->pluck("module_id");
        $copyActivityId = DB::select("SELECT LAST_INSERT_ID() as copyActivityId")[0]->copyActivityId;

        foreach ($moduleIds as $order => $moduleId) {
            $this->copyModule($moduleId, $copyActivityId, $order);
        }
    }


    /**
     * Copy a module
     *
     * @param int $moduleId
     * @param int $activityId
     * @param int $order
     */
    public function copyModule(int $moduleId, int $activityId, int $order)
    {
        $copyModule = DB::table("modules")->where("id", "=", $moduleId)->first();

        unset($copyModule->id);
        $copyModule->order = $order;
        $copyModule->activity_id = $activityId;

        DB::table("modules")->insert((array)$copyModule);
    }
}
