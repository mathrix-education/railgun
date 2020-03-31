<?php

declare(strict_types=1);

namespace Railgun\Buster\Pipes;

use Closure;
use stdClass;
use function count;
use function preg_match;
use function uuid_create;

class RestructureExamQuestions extends Pipe
{
    private const ROOT_QUESTION_REGEX  = '/^([\d]+)-\s+([\s\S]*)/m';
    private const CHILD_QUESTION_REGEX = '/^([a-z]+)--\s+([\s\S]*)/m';

    /**
     * @param stdClass $exam The exam to restructure.
     * @param Closure  $next The next pipe.
     *
     * @return stdClass The restructured exam.
     */
    public function handle(stdClass $exam, Closure $next): stdClass
    {
        $questions   = [];
        $allChildren = false;

        foreach ($exam->questions as $i => $originalQuestion) {
            if (preg_match(self::ROOT_QUESTION_REGEX, $originalQuestion, $matches)) {
                $mode      = 'root';
                $statement = $matches[2];
            } elseif (preg_match(self::CHILD_QUESTION_REGEX, $originalQuestion, $matches)) {
                $mode      = 'child';
                $statement = $matches[2];

                if ($allChildren || count($questions) === 0) {
                    $mode        = 'root';
                    $allChildren = false;
                }
            } else {
                $mode      = 'root';
                $statement = $originalQuestion;
            }

            $question = [
                'kind'         => 'question',
                'uuid'         => uuid_create(),
                'statement'    => $statement,
                'explanations' => [],
            ];

            foreach ($exam->points[$i] as $j => $points) {
                $question['explanations'][$j] = [
                    'points'  => $points,
                    'content' => $exam->explanations[$i][$j],
                ];
            }

            if ($mode === 'root') {
                $questions[] = $question;
            } elseif ($mode === 'child') {
                $questions[count($questions) - 1]['children'][] = $question;
            }
        }

        $exam->questions = $questions;

        return $next($exam);
    }
}
