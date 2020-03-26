<?php

declare(strict_types=1);

namespace Tests\E2E\GraphQL;

use Exception;
use Railgun\Facades\Storage;
use function class_basename;

/**
 * @mixin GraphQLIntegrationTestCase
 */
trait TestFilesystem
{
    /**
     * @return string Get the test root directory.
     */
    protected function getRootDirectory(): string
    {
        return 'tests/' . class_basename($this) . '/' . $this->getName();
    }

    /**
     * @before
     */
    public function setupRootDirectory(): void
    {
        if (!$this->app) {
            $this->refreshApplication();
        }

        $delete = function () {
            try {
                Storage::deleteDirectory($this->getRootDirectory());
            } catch (Exception $e) {
            }
        };

        $delete();
        $this->beforeApplicationDestroyedCallbacks[] = $delete;
    }
}
