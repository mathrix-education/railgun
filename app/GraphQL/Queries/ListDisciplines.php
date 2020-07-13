<?php

declare(strict_types=1);

namespace Railgun\GraphQL\Queries;

use Illuminate\Database\Eloquent\Collection;
use Railgun\Models\Discipline;
use Railgun\Models\Grade;
use function array_key_exists;

/**
 * Query listDisciplines.
 * Allow to list the disciplines of the application, with some optional filters.
 */
class ListDisciplines
{
    /**
     * @param null    $_    The parent value, always null in this case.
     * @param mixed[] $args The query arguments.
     *
     * @return Collection<Discipline>
     */
    public function __invoke($_, array $args): Collection
    {
        $query = Discipline::query();

        if (array_key_exists('gradeSlug', $args)) {
            /** @var Grade $grade */
            $grade = Grade::query()->where('slug', '=', $args['gradeSlug'])->firstOrFail();
            $query = $grade->disciplines();

            if (!array_key_exists('withInvisible', $args) || $args['withInvisible'] !== true) {
                $query = $query->wherePivot('visible', '=', true);
            }
        }

        return $query->get();
    }
}
