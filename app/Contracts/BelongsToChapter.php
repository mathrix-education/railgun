<?php

declare(strict_types=1);

namespace Railgun\Contracts;

use Railgun\Models\BaseModel;
use Railgun\Models\Chapter;

/**
 * @property-read int $id
 * @mixin BaseModel
 */
interface BelongsToChapter
{
    public function getChapter(): Chapter;
}
