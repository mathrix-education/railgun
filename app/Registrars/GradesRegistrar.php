<?php

declare(strict_types=1);

namespace App\Registrars;

use App\Controllers\GradesController;
use Mathrix\Lumen\Zero\Exceptions\InvalidArgument;
use Mathrix\Lumen\Zero\Registrars\BaseRegistrar;

class GradesRegistrar extends BaseRegistrar
{
    /**
     * Register the routes.
     *
     * @throws InvalidArgument
     */
    public function register(): void
    {
        $controller = GradesController::class;

        $this->get('/grades/{gradeId}/disciplines/visible', "$controller@disciplinesVisible");
        $this->get('/grades/slug/{gradeSlug}/chapters/slug/{chapterSlug}', "$controller@getBySlugChapterSlug");
        $this->get(
            '/grades/{gradeId}/disciplines/{disciplineId}/chapters/visible',
            "$controller@chaptersByDisciplineVisible"
        );

        $this->group(['middleware' => 'scope:chapters:read'], function () use ($controller): void {
            $this->get(
                '/grades/{gradeId}/disciplines/{disciplineId}/chapters',
                "$controller@chaptersByDiscipline"
            );
        });

        $this->registerCRUDRoutes([
            'list' => null,
            'read' => null,
            'read:slug' => null,
            'create' => 'scope:grades:write',
            'update' => 'scope:grades:write',
            'delete' => 'scope:grades:delete',
            // 'rel:get:disciplines' => 'scope:disciplines:read',
        ]);
    }
}
