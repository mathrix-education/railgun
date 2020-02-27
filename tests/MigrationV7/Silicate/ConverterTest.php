<?php

declare(strict_types=1);

namespace Tests\MigrationV7\Silicate;

use Illuminate\Support\Facades\DB;
use JsonException;
use Railgun\MigrationV7\Silicate\ExerciseConverter;
use Railgun\MigrationV7\Silicate\FlashcardConverter;
use stdClass;
use Tests\BaseTestCase;
use const JSON_PRETTY_PRINT;
use const JSON_THROW_ON_ERROR;
use function array_keys;
use function array_values;
use function data_get;
use function json_encode;
use function str_replace;

class ConverterTest extends BaseTestCase
{
    private const PATTERNS = [
        '/<img/m',
        '/<figure/m',
        '/<\/figure>/m',
        '/\$\$/m',
        '/^\- ?[A-Za-z]/m',
        '/<table/m',
        '/<thead/m',
        '/<tbody/m',
        '/<tr/m',
        '/<th/m',
        '/<td/m',
    ];

    /**
     * @return array
     */
    public function provideExercises(): array
    {
        return DB::table('exercises')
            ->get()
            ->mapWithKeys(fn($e) => ['exercise:' . $e->id => [$e]])
            ->toArray();
    }

    /**
     * @dataProvider provideExercises
     *
     * @param stdClass $original
     *
     * @throws JsonException
     */
    public function testConvertExercises(stdClass $original): void
    {
        $converted = ExerciseConverter::new()->convert($original);
        $this->assertValidExercise($converted, $original);
    }

    /**
     * @param stdClass $converted
     * @param stdClass $original
     *
     * @throws JsonException
     */
    private function assertValidExercise(stdClass $converted, stdClass $original): void
    {
        $paths = ['questions', 'choices', 'explanations'];

        foreach ($paths as $path) {
            foreach (self::PATTERNS as $pattern) {
                $this->assertNotPresent($converted, $original, $path, $pattern);
            }
        }
    }

    /**
     * @param stdClass $converted
     * @param stdClass $original
     * @param string   $path
     * @param string   $pattern
     *
     * @throws JsonException
     */
    private function assertNotPresent(stdClass $converted, stdClass $original, string $path, string $pattern): void
    {
        $data = data_get($converted, $path);
        $json = json_encode($data, JSON_THROW_ON_ERROR, 512);

        $this->assertNotRegExp(
            $pattern,
            $json,
            $this->failureMessageFrom(
                "Found unexpected {$pattern} in exercise id {$original->id}",
                $data,
                data_get($original, $path)
            )
        );
    }

    /**
     * @param string         $prefix
     * @param stdClass|array $data
     * @param stdClass|array $original
     *
     * @return string
     *
     * @throws JsonException
     */
    private function failureMessageFrom(string $prefix, $data, $original): string
    {
        $replace = [
            '\\"' => '"',
            '\\n' => "\n",
            '\\/' => '/',
        ];

        $data     = json_encode($data, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT, 512);
        $original = json_encode($original, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT, 512);

        $data = str_replace(array_keys($replace), array_values($replace), $data);

        return "{$prefix}:\n{$data}\nOriginal data:\n$original";
    }

    /**
     * @return array
     */
    public function provideFlashcards(): array
    {
        return DB::table('flashcards')
            ->get()
            ->mapWithKeys(fn($e) => ['flashcard:' . $e->id => [$e]])
            ->toArray();
    }

    /**
     * @dataProvider provideFlashcards
     *
     * @param stdClass $original
     *
     * @throws JsonException
     */
    public function testConvertFlashcards(stdClass $original): void
    {
        $converted = FlashcardConverter::new()->convert($original);
        $this->assertValidFlashcard($converted, $original);
    }

    /**
     * @param stdClass $converted
     * @param stdClass $original
     *
     * @throws JsonException
     */
    private function assertValidFlashcard(stdClass $converted, stdClass $original): void
    {
        $paths    = ['fronts', 'backs'];
        $patterns = ['/<img/m', '/<figure/m', '/<\/figure>/m', '/<table/m', '/\$\$/m', '/^\- ?[A-Za-z]/m'];

        foreach ($paths as $path) {
            foreach (self::PATTERNS as $pattern) {
                $this->assertNotPresent($converted, $original, $path, $pattern);
            }
        }
    }
}
