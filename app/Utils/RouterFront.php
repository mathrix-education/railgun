<?php
namespace App\Utils;

use App\Models\Activity;
use App\Models\Chapter;
use App\Models\Class_;
use App\Models\Country;
use App\Models\Discipline;
use App\Models\Meta;
use App\Models\Module;
use App\Models\Notion;

/**
 * Class RouterFront
 *
 * @author    Mathieu Bour <mathieu.tin.bour@ŋmail.com>
 * @package   App\Utils
 * @since
 * @copyright Mathrix Education SA
 */
class RouterFront
{
    /**
     * Get the module route URL.
     *
     * @param Country    $country
     * @param Class_     $class
     * @param Discipline $discipline
     * @param Chapter    $chapter
     * @param Notion     $notion
     * @param Activity   $activity
     * @param Module     $module
     *
     * @return string
     */
    public static function getModuleUrl(Country $country, Class_ $class, Discipline $discipline, Chapter $chapter,
                                        Notion $notion, Activity $activity, Module $module)
    {
        return "/{$country->slug}/{$class->slug}/{$discipline->slug}/{$chapter->slug}/{$notion->slug}/{$activity->slug}"
            . "/{$module->uri_type}/{$module->id}";
    }

    /**
     * Check if the current route should be NOT indexed.
     *
     * @param Country    $country
     * @param Class_     $class
     * @param Discipline $discipline
     * @param Chapter    $chapter
     * @param Notion     $notion
     * @param Activity   $activity
     * @param Module     $module
     *
     * @return bool
     */
    public static function seoIndex(Country $country, Class_ $class, Discipline $discipline, Chapter $chapter,
                                    Notion $notion, Activity $activity, Module $module)
    {
        return self::seoIndexByIds($country->id, $class->id, $discipline->id, $chapter->id, $notion->id, $activity->id,
            $module->id);
    }

    /**
     * Check if the current route should be NOT indexed with content ids.
     *
     * @param int $countryId
     * @param int $classId
     * @param int $disciplineId
     * @param int $chapterId
     * @param int $notionId
     * @param int $activityId
     * @param int $moduleId
     *
     * @return bool
     */
    public static function seoIndexByIds(int $countryId, int $classId, int $disciplineId, int $chapterId, int $notionId,
                                         int $activityId, int $moduleId)
    {
        return Meta::where([
            ["country_id", "=", $countryId],
            ["class_id", "=", $classId],
            ["discipline_id", "=", $disciplineId],
            ["chapter_id", "=", $chapterId],
            ["notion_id", "=", $notionId],
            ["activity_id", "=", $activityId],
            ["module_id", "=", $moduleId],
            ["seo_index", "=", true]
        ])
            ->whereNull("user_id")
            ->exists();
    }
}
