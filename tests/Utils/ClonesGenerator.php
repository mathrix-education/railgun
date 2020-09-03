<?php

declare(strict_types=1);

namespace Tests\Utils;

use Railgun\Models\Chapter;
use Railgun\Models\Grade;
use Railgun\Models\Theme;
use function factory;

class ClonesGenerator
{
    /**
     * @param Chapter $chapter The chapter which will be cloned.
     *
     * @return Theme The new theme.
     */
    public static function themeForClone(Chapter $chapter): Theme
    {
        return factory(Theme::class)->create([
            'discipline_id' => $chapter->theme->discipline_id,
            'grade_id'      => Grade::query()
                ->where('id', '!=', $chapter->theme->grade_id)
                ->inRandomOrder()
                ->firstOrFail()
                ->getKey(),
        ]);
    }

    /**
     * Generate a clone for a chapter with a valid theme.
     *
     * @param Chapter $chapter The chapter to clone.
     *
     * @return Chapter The cloned chapter instance.
     */
    public static function cloneChapter(Chapter $chapter): Chapter
    {
        $theme = self::themeForClone($chapter);

        return $chapter->clone(['theme_id' => $theme->id]);
    }
}
