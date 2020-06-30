<?php

declare(strict_types=1);

namespace Railgun\GraphQL\Directives;

use Illuminate\Database\Eloquent\Builder;
use Nuwave\Lighthouse\Schema\Directives\ValidationDirective;
use Railgun\Models\Chapter;
use Railgun\Models\Theme;
use function array_key_exists;
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
                    $conflict = Chapter::query();

                    if (!array_key_exists('id', $this->args)) {
                        /** @var Theme $theme */
                        $theme = Theme::query()->findOrFail($this->args['theme_id']);
                        $slug  = $this->args['slug'];
                    } else {
                        // This is an update
                        $conflict = $conflict->where('id', '!=', $this->args['id']);

                        /** @var Chapter $reference */
                        $reference = Chapter::query()->findOrFail($this->args['id']);
                        /** @var Theme $theme */
                        $theme = Theme::query()->findOrFail($this->args['theme_id'] ?? $reference->theme_id);
                        $slug  = $this->args['slug'] ?? $reference->slug;
                    }

                    $conflict
                        ->where('slug', '=', $slug)
                        ->whereHas('theme.grade', static function (Builder $query) use ($theme): void {
                            $query->where('id', '=', $theme->grade_id);
                        })
                        ->whereHas('theme.discipline', static function (Builder $query) use ($theme): void {
                            $query->where('id', '=', $theme->discipline_id);
                        });

                    if ($conflict->exists()) {
                        func_get_arg(2)(trans('validation.unique', ['attribute' => 'slug']));
                    }
                },
            ],
        ];
    }
}
