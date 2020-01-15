<?php

declare(strict_types=1);

namespace App\MigrationV7\Exams;

use stdClass;
use function count;
use function is_array;
use function is_int;
use function is_string;

class ExamUtils
{
    public static function malformed(stdClass $exam, string $message): stdClass
    {
        $exam->malformed = $message;

        return $exam;
    }
}
