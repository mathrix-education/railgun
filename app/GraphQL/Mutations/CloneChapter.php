<?php

declare(strict_types=1);

namespace Railgun\GraphQL\Mutations;

use Illuminate\Support\Arr;
use Railgun\Models\Chapter;

class CloneChapter
{
    /**
     * @param null    $_    The parent value, always null in this case.
     * @param mixed[] $args The mutation arguments (id, theme_id).
     *
     * @return Chapter The cloned chapter.
     */
    public function __invoke($_, array $args): Chapter
    {
        /** @var Chapter $chapter */
        $chapter = Chapter::query()->findOrFail(Arr::pull($args, 'id'));

        return $chapter->clone($args);
    }
}
