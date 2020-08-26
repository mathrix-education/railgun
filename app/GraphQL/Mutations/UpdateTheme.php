<?php

namespace Railgun\GraphQL\Mutations;

use Illuminate\Bus\Dispatcher;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Railgun\Jobs\GravityJob;
use Railgun\Models\Theme;

class UpdateTheme
{
    protected Dispatcher $dispatcher;

    public function __construct(Dispatcher $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    /**
     * @param null    $_    The parent value, always null in this case.
     * @param mixed[] $args The mutation arguments.
     *
     * @return Theme The updated theme.
     */
    public function __invoke($_, array $args): Theme
    {
        $chapterLinks = Arr::pull($args, 'chapters');

        /** @var Theme $theme */
        $theme = Theme::query()->findOrFail(Arr::pull($args, 'id'));
        $theme->fill($args);
        $theme->save();

        $replace = Arr::pull($chapterLinks, 'replace');
        $add     = Arr::pull($chapterLinks, 'add');
        $delete  = Arr::pull($chapterLinks, 'delete');

        if ($replace) {
            $theme->chapters()->sync($theme->parseChapterLinks($replace));
        }

        if (!$replace && $add) {
            /** @var int $delta */
            $delta = DB::table('chapter_theme')->where('theme_id', '=',)->max('order') ?? 0;
            $theme->chapters()->syncWithoutDetaching($theme->parseChapterLinks($add, $delta));
        }

        if (!$replace && $delete) {
            $theme->chapters()->detach($delete);
            $this->dispatcher->dispatch(new GravityJob('chapter_theme', ['theme_id']));
        }

        return $theme;
    }
}
