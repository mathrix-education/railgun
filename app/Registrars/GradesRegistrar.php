<?php

namespace App\Registrars;

use App\Controllers\GradesController;
use Mathrix\Lumen\Bases\BaseRegistrar;

/**
 * Class GradesRegistrar.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0-rc1
 */
class GradesRegistrar extends BaseRegistrar
{
    /**
     * Register the routes.
     */
    public function register(): void
    {
        $controller = GradesController::class;

        $this->rest("grades", $controller, [
            "index" => null,
            "get" => null,
            "post" => "scope:grades:write",
            "patch" => "scope:grades:write",
            "delete" => "scope:grades:delete",
            "related:disciplines" => null
        ]);

        $this->get("grades/slug/{gradeSlug:[a-z0-9\-]+}", "$controller@getBySlug");

        $this->get("grades/slug/{gradeSlug:[a-z0-9\-]+}/chapters/slug/{chapterSlug:[a-z0-9\-]+}",
            "$controller@getBySlugChapterSlug");

        $this->get("grades/{gradeId:[1-9]\d*}/disciplines/{disciplineId:[1-9]\d*}/chapters/{page:\d+}/{perPage:[1-9]\d*}",
            "$controller@chaptersByDiscipline");
    }
}
