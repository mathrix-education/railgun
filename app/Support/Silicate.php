<?php

declare(strict_types=1);

namespace App\Support;

use Faker\Generator;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use const JSON_THROW_ON_ERROR;
use function app;
use function array_filter;
use function array_map;
use function in_array;
use function json_decode;
use function storage_path;

class Silicate
{
    private static $youTubeIds;

    /**
     * Get a faker instance.
     *
     * @return Generator
     */
    private static function faker(): Generator
    {
        return app(Generator::class);
    }

    /**
     * Generate a random TextBrick.
     *
     * @return array
     */
    public static function text(): array
    {
        return [
            'uuid' => self::faker()->uuid,
            'kind' => 'text',
            'text' => self::faker()->text,
        ];
    }

    /**
     * Generate a random VideoBrick with a valid YouTubeId.
     *
     * @return array
     */
    public static function video(): array
    {
        if (!isset(self::$youTubeIds)) {
            self::$youTubeIds = json_decode(storage_path('raws/youtube.json'), true, 512, JSON_THROW_ON_ERROR);
        }

        return [
            'uuid'    => self::faker()->uuid,
            'kind'    => 'video',
            'type'    => 'youtube',
            'videoId' => self::faker()->randomElement(self::$youTubeIds),
        ];
    }

    /**
     * Generate a random Silicate Wall.
     *
     * @param int   $length    The number of desired bricks.
     * @param array $blacklist An optional blacklist of generators.
     *
     * @return array
     *
     * @throws ReflectionException
     */
    public static function wall(int $length = 5, array $blacklist = []): array
    {
        $class     = new ReflectionClass(self::class);
        $blacklist = [...$blacklist, ...['faker', 'wall']];
        $factories = array_map(
            fn(ReflectionMethod $method) => $method->name,
            $class->getMethods(ReflectionMethod::IS_STATIC)
        );
        $factories = array_filter($factories, fn($f) => !in_array($f, $blacklist, true));

        $wall = [];

        for ($i = 0; $i < $length; $i++) {
            $factory = self::faker()->randomElement($factories);
            $wall[]  = self::{$factory}();
        }

        return $wall;
    }
}
