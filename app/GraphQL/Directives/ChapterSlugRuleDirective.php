<?php

declare(strict_types=1);

namespace Railgun\GraphQL\Directives;

use Illuminate\Support\Arr;
use Nuwave\Lighthouse\Schema\Directives\ValidationDirective;
use Railgun\Models\Chapter;
use function array_key_exists;
use function array_merge;
use function func_get_arg;
use function trans;

/**
 * Validate the chapter slug, since a given slug can only exist once for a tuple (grade_id/chapter_id).
 * This should be applied on the CreateChapter and UpdateChapter input.
 */
class ChapterSlugRuleDirective extends ValidationDirective
{
    /**
     * Return validation rules for the arguments.
     *
     * @return mixed[][] The slug validation rules.
     */
    public function rules(): array
    {
        if (!array_key_exists('slug', $this->args)) {
            // There is no slug here, we can just exit.
            return [];
        }

        return [
            'slug' => [
                function (): void {
                    $query  = Chapter::query();
                    $wheres = $this->args;

                    if (array_key_exists('id', $this->args)) {
                        /** @var Chapter $reference */
                        $reference = Chapter::query()->findOrFail($this->args['id']);
                        $query     = $query->whereKeyNot($this->args['id']);
                        $wheres    = array_merge($reference->toArray(), $wheres);
                    }

                    $wheres = Arr::only($wheres, ['slug', 'discipline_id', 'grade_id']);

                    if ($query->where($wheres)->exists()) {
                        func_get_arg(2)(trans('validation.unique', ['attribute' => 'slug']));
                    }
                },
            ],
        ];
    }
}
