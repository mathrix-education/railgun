<?php

namespace App\Registrars;

use App\Controllers\ChaptersController;
use Mathrix\Lumen\Bases\BaseRegistrar;

/**
 * Class ChaptersRegistrar.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0-rc1
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
            "index" => "moderator",
            "get" => null,
            "post" => "moderator",
            "patch" => "moderator",
            "delete" => "moderator"
        ]);

        $this->get("chapters/{id:[1-9]\d*}/recursive", "$controller@recursive");
        $this->get("chapters/by-module/{moduleId:[1-9]\d*}/recursive", "$controller@byModuleRecursive");
        $this->get("chapters/by-grade-discipline/{gradeId:[1-9]\d*}/{disciplineId:[1-9]\d*}[/{scope:all}]",
            "$controller@byGradeIdDisciplineId");
        $this->get("chapters/slug/{chapterSlug:[a-z\-]+}/by-grade-slug/{gradeSlug:[a-z0-9\-]+}",
            "$controller@getBySlugGradeSlug");
    }
}
