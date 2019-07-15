<?php

namespace App\Registrars;

use App\Controllers\GradesController;
use Mathrix\Lumen\Zero\Registrars\BaseRegistrar;

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

        $this->rest([
            "std:index" => null,
            "std:get" => null,
            "std:get:slug" => null,
            "std:post" => "scope:grades:write",
            "std:patch" => "scope:grades:write",
            "std:delete" => "scope:grades:delete",
            "rel:get:disciplines" => null
        ]);

        $controller = GradesController::class;

        $this->get("grades/slug/{gradeSlug:[a-z0-9\-]+}/chapters/slug/{chapterSlug:[a-z0-9\-]+}",
            "$controller@getBySlugChapterSlug");
        $this->get("grades/{gradeId:[1-9]\d*}/disciplines/{disciplineId:[1-9]\d*}/chapters/{page:\d+}/{perPage:[1-9]\d*}",
            "$controller@chaptersByDiscipline");
    }
}
