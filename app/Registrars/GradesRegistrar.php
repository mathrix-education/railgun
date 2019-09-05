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
        $controller = GradesController::class;

        $this->get("/grades/{gradeId}/disciplines/visible", "$controller@disciplinesVisible");
        $this->get("/grades/slug/{gradeSlug}/chapters/slug/{chapterSlug}", "$controller@getBySlugChapterSlug");
        $this->get("/grades/{gradeId}/disciplines/{disciplineId}/chapters/visible",
            "$controller@chaptersByDisciplineVisible");

        $this->group(["middleware" => "scope:chapters:read"], function () use ($controller) {
            $this->get(
                "/grades/{gradeId}/disciplines/{disciplineId}/chapters",
                "$controller@chaptersByDiscipline"
            );
        });

        $this->rest([
            "std:index" => null,
            "std:get" => null,
            "std:get:slug" => null,
            "std:post" => "scope:grades:write",
            "std:patch" => "scope:grades:write",
            "std:delete" => "scope:grades:delete",
            "rel:get:disciplines" => "scope:disciplines:read"
        ]);
    }
}
