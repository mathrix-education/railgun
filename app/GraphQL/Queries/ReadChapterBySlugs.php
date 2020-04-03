<?php

declare(strict_types=1);

namespace Railgun\GraphQL\Queries;

use Illuminate\Database\Eloquent\Builder;
use Railgun\Models\Chapter;

class ReadChapterBySlugs
{
    /**
     * @param null    $rootValue The parent value, always null in this case.
     * @param mixed[] $args      The query arguments (gradeSlug, disciplineSlug, chapterSlug).
     *
     * @return Chapter|null The chapter matching the given grade, discipline and chapter slugs if it exists.
     */
    public function __invoke($rootValue, array $args): ?Chapter
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return Chapter::query()
            ->whereHas('grade', static function (Builder $builder) use ($args): void {
                $builder->where('slug', '=', $args['gradeSlug']);
            })
            ->whereHas('discipline', static function (Builder $builder) use ($args): void {
                $builder->where('slug', '=', $args['disciplineSlug']);
            })
            ->where('slug', '=', $args['chapterSlug'])
            ->first();
    }
}
