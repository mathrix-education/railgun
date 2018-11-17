<?php
namespace App\Controllers;

use App\Models\Activity;
use App\Models\Chapter;
use App\Models\Notion;
use App\Utils\RouterFront;
use Illuminate\Database\Eloquent\Collection;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Class CSVController.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @since
 * @copyright Mathrix Education SA
 */
class CSVController extends BaseController
{
    public function routes1Module()
    {
        // Init
        $data = [
            [
                "uri",
                "country.name",
                "class.name",
                "discipline.name",
                "chapter.name",
                "notion.name",
                "activity.name",
                "activity.type",
                "activity.seo_title",
                "activity.seo_description",
                "activity.reviewed",
                "module.id",
                "seo_index",
                "premium (N.premium || A.type == \"premium\")"
            ]
        ];

        /** @var Chapter[]|Collection $chapters */
        $chapters = Chapter::all();

        /** @var Chapter $chapter */
        foreach ($chapters as $chapter) {
            $class = $chapter->class_;
            $country = $class->country;
            $discipline = $chapter->discipline;

            /** @var Notion $notion */
            foreach ($chapter->notions as $notion) {
                /** @var Activity $activity */
                foreach ($notion->activities as $activity) {

                    if ($activity->modules->count() > 0) {
                        $module = $activity->modules[0];

                        $data[] = [
                            "/{$country->slug}/{$class->slug}/{$discipline->slug}/{$chapter->slug}/{$notion->slug}"
                            . "/{$activity->slug}/{$module->uri_type}/{$module->id}",
                            $country->name,
                            $class->name,
                            $discipline->name,
                            $chapter->name,
                            $notion->name,
                            $activity->name,
                            $activity->type,
                            $activity->seo_title,
                            $activity->seo_description,
                            $activity->reviewed ? 1 : 0,
                            $module->id,
                            RouterFront::seoIndex($country, $class, $discipline, $chapter, $notion, $activity, $module) ? 1 : 0,
                            ($notion->type == "premium" || $activity->premium) ? 1 : 0
                        ];
                    }
                }
            }
        }

        $response = new StreamedResponse(
            function () use ($data) {
                // A resource pointer to the output stream for writing the CSV to
                $handle = fopen("php://output", "w");
                foreach ($data as $row) {
                    // Loop through the data and write each entry as a new row in the csv
                    fputcsv($handle, $row);
                }

                fclose($handle);
            }, 200, [
                "Content-Type" => "text/csv",
                "Content-Disposition" => "attachment; filename=routes1Module.csv"
            ]
        );

        return $response;
    }
}
