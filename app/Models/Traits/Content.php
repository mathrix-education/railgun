<?php

declare(strict_types=1);

namespace Railgun\Models\Traits;

use Railgun\Models\Chapter;

interface Content
{
    /**
     * Get the model parent chapter.
     *
     * @return Chapter The related parent chapter.
     */
    public function getChapter(): Chapter;
}
