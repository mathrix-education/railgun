<?php

declare(strict_types=1);

namespace Railgun\Support;

use Faker\Generator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use JsonException;
use function app;
use function array_filter;
use function count;
use function file_get_contents;
use function in_array;
use function json_decode;
use function storage_path;
use const JSON_THROW_ON_ERROR;

class Silicate
{
    private const FACTORIES       = ['text', 'video'];
    private const WALL_CACHE_SIZE = 100;

    /** @var string[] Set of valid Mathrix video YouTube ids */
    private static array $youtubeIds = [];

    /**
     * Generate a random TextBrick.
     *
     * @return mixed[]
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
     * Get a faker instance.
     */
    private static function faker(): Generator
    {
        return app(Generator::class);
    }

    /**
     * Generate a random VideoBrick with a valid YouTubeId.
     *
     * @return mixed[]
     *
     * @throws JsonException
     */
    public static function video(): array
    {
        if (!self::$youtubeIds) {
            self::$youtubeIds = json_decode(
                file_get_contents(storage_path('raws/youtube.json')),
                true,
                512,
                JSON_THROW_ON_ERROR,
            );
        }

        return [
            'uuid'    => self::faker()->uuid,
            'kind'    => 'video',
            'type'    => 'youtube',
            'videoId' => self::faker()->randomElement(self::$youtubeIds),
        ];
    }

    /**
     * Generate a random Silicate Wall.
     *
     * @param int      $length    The number of desired bricks.
     * @param string[] $blacklist An optional blacklist of generators.
     *
     * @return mixed[][]
     */
    public static function wall(int $length = 5, array $blacklist = []): array
    {
        $generator = static function (int $length, array $factories) {
            $wall = [];

            for ($i = 0; $i < $length; $i++) {
                $factory = self::faker()->randomElement($factories);
                $wall[]  = self::{$factory}();
            }

            return $wall;
        };

        // This is not a standard generation, generate using generator.
        if ($length !== 5 || count($blacklist) > 0) {
            $factories = array_filter(self::FACTORIES, static fn($f) => !in_array($f, $blacklist, true));

            return $generator($length, $factories);
        }

        // Use the cache
        /** @var Collection $walls */
        $walls = Cache::remember('silicate.walls', 10, static function () use ($generator) {
            $walls = new Collection();
            for ($i = 0; $i < self::WALL_CACHE_SIZE; $i++) {
                $walls->push($generator(5, self::FACTORIES));
            }

            return $walls;
        });

        return $walls->random();
    }
}
