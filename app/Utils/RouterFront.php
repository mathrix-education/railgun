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
    public static function noIndex(Country $country, Class_ $class, Discipline $discipline, Chapter $chapter,
                            Notion $notion, Activity $activity, Module $module)
    {
        return Meta::where([
            ["country_id", "=", $country->id],
            ["class_id", "=", $class->id],
            ["discipline_id", "=", $discipline->id],
            ["chapter_id", "=", $chapter->id],
            ["notion_id", "=", $notion->id],
            ["activity_id", "=", $activity->id],
            ["module_id", "=", $module->id],
            ["no_index", "=", true]
        ])
            ->whereNull("user_id")
            ->exists();
    }
}
