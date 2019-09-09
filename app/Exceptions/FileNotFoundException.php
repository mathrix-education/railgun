<?php

declare(strict_types=1);

namespace App\Exceptions;

use Mathrix\Lumen\Zero\Exceptions\Http\Http404NotFoundException;
use Throwable;

class FileNotFoundException extends Http404NotFoundException
{
    public function __construct(string $file, ?Throwable $previous = null)
    {
        parent::__construct(['file' => $file], "Unable to find file `$file`", $previous);
    }
}
