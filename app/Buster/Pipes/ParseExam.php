<?php

declare(strict_types=1);

namespace Railgun\Buster\Pipes;

use Closure;
use Railgun\Buster\Silicate\SilicateConverter;
use stdClass;
use function app;
use function array_key_exists;
use function count;

/**
 * Parse an exam using the {@see SilicateConverter}.
 */
class ParseExam extends Pipe
{
    private SilicateConverter $converter;

    public function __construct()
    {
        $this->converter = app()->make(SilicateConverter::class);
    }

    /**
     * @param stdClass $exam The exam to parse.
     * @param Closure  $next The next pipe.
     *
     * @return stdClass The parsed exam.
     */
    public function handle(stdClass $exam, Closure $next): stdClass
    {
        $wall = $this->converter->convert($exam->content);

        foreach ($exam->questions as $i => $question) {
            $question['statement'] = $this->converter->convert($question['statement']);

            if (array_key_exists('children', $question) && count($question['children']) > 0) {
                foreach ($question['children'] as $j => $child) {
                    $child['statement'] = $this->converter->convert($child['statement']);

                    foreach ($child['explanations'] as $k => $explanation) {
                        $child['explanations'][$k]['content'] = $this->converter->convert($explanation['content']);
                    }

                    $question['children'][$j] = $child;
                }

                $question['kind'] = 'question-container';
                unset($question['points'], $question['explanations']);
            } else {
                foreach ($question['explanations'] as $k => $explanation) {
                    $question['explanations'][$k]['content'] = $this->converter->convert($explanation['content']);
                }
            }

            $wall[] = $question;
        }

        $exam->content = $wall;

        return $next($exam);
    }
}
