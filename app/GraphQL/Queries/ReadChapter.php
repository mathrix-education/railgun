<?php

declare(strict_types=1);

namespace Railgun\GraphQL\Queries;

use Illuminate\Database\Eloquent\Builder;
use Railgun\Models\Chapter;
use function array_key_exists;

class ReadChapter
{
    /**
     * @param null    $_    The parent value, always null in this case.
     * @param mixed[] $args The query arguments (id, chapterSlug, gradeSlug, disciplineSlug).
     *
     * @return Chapter|null The chapter matching the given grade, discipline and chapter slugs if it exists.
     */
    public function __invoke($_, array $args): ?Chapter
    {
        $query = Chapter::query();

        if (array_key_exists('id', $args)) {
            $query->whereKey($args['id']);
        }

        if (array_key_exists('chapterSlug', $args)) {
            $query->where('slug', '=', $args['chapterSlug']);
        }

        if (array_key_exists('disciplineSlug', $args)) {
            $query->whereHas('theme.discipline', static function (Builder $builder) use ($args): void {
                $builder->where('slug', '=', $args['disciplineSlug']);
            });
        }

        if (array_key_exists('gradeSlug', $args)) {
            $query->whereHas('theme.grade', static function (Builder $builder) use ($args): void {
                $builder->where('slug', '=', $args['gradeSlug']);
            });
        }

        /** @var Chapter $chapter */
        $chapter = $query->firstOrFail();

        return $chapter;
    }
}
