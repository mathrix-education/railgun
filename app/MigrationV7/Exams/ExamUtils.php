<?php

declare(strict_types=1);

namespace Railgun\MigrationV7\Exams;

use stdClass;

class ExamUtils
{
    public static function malformed(stdClass $exam, string $message): stdClass
    {
        $exam->malformed = $message;

        return $exam;
    }
}
