<?php

namespace App\Registrars;

use App\Controllers\ChaptersController;
use Mathrix\Lumen\Bases\BaseRegistrar;

/**
 * Class ChaptersRegistrar.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0
 */
class ChaptersRegistrar extends BaseRegistrar
{
    /**
     * Register the routes.
     */
    public function register(): void
    {
        $controller = ChaptersController::class;
        $this->rest("chapters", $controller, [
            "index" => "admin",
            "get" => null,
            "post" => "admin",
            "patch" => "admin",
            "delete" => "admin"
        ]);

        $this->get("chapters/{id:[1-9]\d*}/recursive", "$controller@recursive");
        $this->get("chapters/by-grade-discipline/{gradeId:[1-9]\d*}/{disciplineId:[1-9]\d*}[/{scope:all}]",
            "$controller@byGradeIdDisciplineId");
        $this->get("chapters/slug/{chapterSlug:[a-z\-]+}/by-grade-slug/{gradeSlug:[a-z0-9\-]+}",
            "$controller@getBySlugGradeSlug");
    }
}
