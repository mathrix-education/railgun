<?php

declare(strict_types=1);

namespace Railgun\GraphQL\Queries;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Railgun\Models\Chapter;
use Railgun\Models\Theme;
use function array_key_exists;

class ListThemes
{
    /**
     * Return a value for the field.
     *
     * @param null    $rootValue The parent value, always null in this case.
     * @param mixed[] $args      The query arguments (gradeId, disciplineId, gradeSlug, disciplineSlug).
     *
     * @return Collection|Chapter[]
     */
    public function __invoke($rootValue, array $args): Collection
    {
        $query = Theme::query()->orderBy('order');

        if (array_key_exists('gradeId', $args)) {
            $query->where('grade_id', '=', $args['gradeId']);
        }

        if (array_key_exists('disciplineId', $args)) {
            $query->where('discipline_id', '=', $args['disciplineId']);
        }

        if (array_key_exists('gradeSlug', $args)) {
            $query->whereHas('grade', static function (Builder $builder) use ($args): void {
                $builder->where('slug', '=', $args['gradeSlug']);
            });
        }

        if (array_key_exists('disciplineSlug', $args)) {
            $query->whereHas('discipline', static function (Builder $builder) use ($args): void {
                $builder->where('slug', '=', $args['disciplineSlug']);
            });
        }

        return $query->get();
    }
}
