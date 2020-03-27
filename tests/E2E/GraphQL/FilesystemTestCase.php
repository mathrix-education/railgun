<?php

declare(strict_types=1);

namespace Tests\E2E\GraphQL;

use Exception;
use Illuminate\Support\Str;
use Railgun\Facades\Storage;
use function class_basename;
use function collect;
use function is_string;
use function trim;

/**
 * Simple trait to enhance filesystem testing.
 *
 * @mixin GraphQLIntegrationTestCase
 */
trait FilesystemTestCase
{
    /**
     * @return string Get the test root directory.
     */
    protected function getTestDirectory(): string
    {
        return 'tests/' . class_basename($this) . '/' . $this->getName();
    }

    /**
     * Get the blob path relative to the current root directory.
     *
     * @param string $relative The relative path.
     *
     * @return string
     */
    protected function getTestPath(string $relative): string
    {
        $root = $this->getTestDirectory();

        if (Str::contains($relative, $root)) {
            return $relative;
        }

        return $this->getTestDirectory() . '/' . trim($relative, '/');
    }

    /**
     * @before
     */
    public function setupTestDirectory(): void
    {
        if (!$this->app) {
            $this->refreshApplication();
        }

        $delete = function () {
            try {
                Storage::deleteDirectory($this->getTestDirectory());
            } catch (Exception $e) {
            }
        };

        $delete();
        $this->beforeApplicationDestroyedCallbacks[] = $delete;
    }

    /**
     * @param array $files The files to upload.
     */
    public function seedTestDirectory($files = []): void
    {
        if (is_string($files)) {
            $files = [$files];
        }

        collect($files)
            ->map(fn($f) => $this->getTestPath($f))
            ->each(fn($f) => Storage::put($f, $this->faker->text));
    }
}
