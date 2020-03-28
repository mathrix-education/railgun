<?php

declare(strict_types=1);

namespace Railgun\Models\Traits;

use Railgun\Models\Chapter;

interface BelongsToChapter
{
    /**
     * Get the model parent chapter.
     *
     * @return Chapter
     */
    public function getChapter(): Chapter;
}
