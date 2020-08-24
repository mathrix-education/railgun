<?php

declare(strict_types=1);

namespace Railgun\Actions;

use Illuminate\Routing\Controller;
use JsonException;
use function app;
use function config;
use function data_get;
use function file_get_contents;
use function json_decode;
use const JSON_THROW_ON_ERROR;

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
            'debug'       => config('app.debug'),
            'release'     => config('app.release'),
        ];
    }

    /**
     * @param string|null $path The object path.
     *
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
}
