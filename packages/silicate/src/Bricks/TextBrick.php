<?php

declare(strict_types=1);

namespace Mathrix\Silicate\Bricks;

class TextBrick extends Brick
{
    public const KIND = 'text';

    /** @var mixed[] $rules The brick validation rules. */
    protected array $rules = [
        'text' => 'required|string',
    ];
}
