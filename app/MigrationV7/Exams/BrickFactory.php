<?php

declare(strict_types=1);

namespace App\MigrationV7\Exams;

use RuntimeException;
use function count;
use function trim;

class BrickFactory
{
    /**
     * TextBrick factory.
     *
     * @param string $text
     *
     * @return array
     */
    public static function text(string $text): array
    {
        return [
            'uuid' => StringUtils::uuid(),
            'kind' => 'text',
            'text' => StringUtils::mtrim($text),
        ];
    }

    /**
     * EquationBrick factory.
     *
     * @param string      $latex
     * @param string|null $description
     *
     * @return array
     */
    public static function equation(string $latex, ?string $description = null): array
    {
        $latex = trim($latex, '$'); // Remove starting and ending dollars.

        return [
            'uuid'        => StringUtils::uuid(),
            'kind'        => 'equation',
            'equation'    => StringUtils::mtrim($latex),
            'description' => $description,
        ];
    }

    public static function question(array $question, array $explanations, array $points, $children = []): array
    {
        $data = [
            'uuid'         => StringUtils::uuid(),
            'kind'         => 'question',
            'statement'    => $question,
            'explanations' => [],
            'children'     => $children,
        ];

        if (count($explanations) !== count($points)) {
            throw new RuntimeException('explanations and points size mismatch');
        }

        foreach ($explanations as $i => $explanation) {
            $data['explanations'][] = [
                'points'  => $points[$i],
                'content' => $explanation,
            ];
        }

        if ($children === false) {
            unset($data['children']);
        }

        return $data;
    }
}
