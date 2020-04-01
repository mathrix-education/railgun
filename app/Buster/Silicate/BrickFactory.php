<?php

declare(strict_types=1);

namespace Railgun\Buster\Silicate;

use Railgun\Buster\Utils\StringUtils;
use RuntimeException;
use function count;
use function trim;
use function uuid_create;

class BrickFactory
{
    /**
     * Build a text brick.
     *
     * @param string $text The text.
     *
     * @return mixed[] The text brick.
     */
    public static function text(string $text): array
    {
        return [
            'uuid' => uuid_create(),
            'kind' => 'text',
            'text' => StringUtils::mtrim($text),
        ];
    }

    /**
     * Build a new equation brick.
     *
     * @param string      $latex       The new equation in LaTeX.
     * @param string|null $description The equation description.
     *
     * @return mixed[] The equation brick.
     */
    public static function equation(string $latex, ?string $description = null): array
    {
        $latex = trim($latex, '$'); // Remove starting and ending dollars.

        return [
            'uuid'        => uuid_create(),
            'kind'        => 'equation',
            'equation'    => StringUtils::mtrim($latex),
            'description' => $description,
        ];
    }

    /**
     * Build a new image brick.
     *
     * @param string $src        The image src.
     * @param string $alt        The image alt text.
     * @param bool   $displayAlt If the alt text should be displayed.
     *
     * @return mixed[] The image brick.
     */
    public static function image(string $src, string $alt = '', bool $displayAlt = false): array
    {
        return [
            'uuid'       => uuid_create(),
            'kind'       => 'image',
            'src'        => StringUtils::mtrim($src),
            'alt'        => StringUtils::mtrim($alt),
            'displayAlt' => $displayAlt,
        ];
    }

    /**
     * Build a new table brick.
     *
     * @param mixed[][] $matrix The table matrix.
     *
     * @return mixed[] The table brick.
     */
    public static function table(array $matrix): array
    {
        return [
            'uuid'   => uuid_create(),
            'kind'   => 'table',
            'matrix' => $matrix,
        ];
    }

    /**
     * Build a new list brick.
     *
     * @param mixed[] $items The list items.
     * @param string  $type  The list type (ordered / non-ordered).
     *
     * @return mixed[] The list brick.
     */
    public static function list(array $items, string $type = 'ul'): array
    {
        return [
            'uuid'  => uuid_create(),
            'kind'  => 'list',
            'type'  => $type,
            'items' => $items,
        ];
    }

    /**
     * Build a new question brick.
     *
     * @param mixed[] $question
     * @param mixed[] $explanations
     * @param int[]   $points
     *
     * @return mixed[] The question brick.
     */
    public static function question(array $question, array $explanations, array $points): array
    {
        $data = [
            'uuid'         => uuid_create(),
            'kind'         => 'question',
            'statement'    => $question,
            'explanations' => [],
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

        return $data;
    }
}
