<?php

declare(strict_types=1);

namespace Railgun\GraphQL\Mutations;

use Illuminate\Support\Arr;
use Railgun\Models\Theme;

class CreateTheme
{
    /**
     * @param null    $_    The parent value, always null in this case.
     * @param mixed[] $args The mutation arguments.
     *
     * @return Theme The newly created theme.
     */
    public function __invoke($_, array $args): Theme
    {
        $chapterLinks = Arr::pull($args, 'chapters') ?? [];

        $theme = new Theme($args);
        $theme->save();

        $replace = Arr::pull($chapterLinks, 'replace') ?? Arr::pull($chapterLinks, 'add');

        if ($replace) {
            $theme->chapters()->sync($theme->parseChapterLinks($replace));
        }

        return $theme;
    }
}
