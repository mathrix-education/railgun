<?php

declare(strict_types=1);

namespace Railgun\Actions;

use Illuminate\Routing\Controller;
use JsonException;
use OutOfBoundsException;
use PackageVersions\Versions;
use function app;
use function config;
use function data_get;
use function file_get_contents;
use function json_decode;
use function preg_match;
use const JSON_THROW_ON_ERROR;
use const PHP_VERSION;

class VersionAction extends Controller
{
    /**
     * @return mixed[] The Railgun metadata.
     *
     * @throws JsonException
     */
    public function __invoke(): array
    {
        return [
            'description' => $this->composer('description'),
            'environment' => app()->environment(),
            'versions'    => [
                'php'                       => PHP_VERSION,
                'mathrix-education/railgun' => config('app.release'),
                'laravel/lumen-framework'   => $this->version('laravel/lumen-framework'),
            ],
        ];
    }

    /**
     * @return mixed The composer metadata.
     *
     * @throws JsonException
     */
    private function composer(?string $path = null)
    {
        $composer = json_decode(
            file_get_contents(app()->basePath('composer.json')),
            true, 512, JSON_THROW_ON_ERROR,
        );

        return data_get($composer, $path);
    }

    /**
     * Get the installed package version.
     *
     * @param string $package The package full qualified name (vendor/package).
     *
     * @return string The parsed package version.
     */
    private function version(string $package): string
    {
        try {
            preg_match('/^v?(.*)@/', Versions::getVersion($package), $matches);

            return $matches[1] ?? 'unknown';
        } catch (OutOfBoundsException $exception) { // @codeCoverageIgnoreStart
            return 'unknown';
        } // @codeCoverageIgnoreEnd
    }
}
