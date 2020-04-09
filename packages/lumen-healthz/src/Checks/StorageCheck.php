<?php

declare(strict_types=1);

namespace Mathrix\Healthz\Checks;

use Exception;
use Illuminate\Container\Container;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Filesystem\FilesystemManager;
use function array_merge;
use function base64_encode;
use function random_bytes;

class StorageCheck extends Check
{
    private FilesystemManager $manager;
    private Filesystem        $disk;

    /**
     * @param mixed[] $options The Storage check options.
     *
     * @throws BindingResolutionException
     */
    public function __construct(array $options = [])
    {
        $this->manager = Container::getInstance()->make('filesystem');

        parent::__construct(array_merge([
            'disk' => $this->manager->getDefaultDriver(),
        ], $options));

        $this->disk = $this->manager->disk($this->options['disk']);
    }

    /**
     * @return mixed[] The check details.
     *
     * @throws FileNotFoundException
     * @throws Exception
     */
    public function execute(): array
    {
        $name = base64_encode(random_bytes(24)) . '.txt';

        return [
            'put'    => $this->disk->put($name, 'foo'),
            'get'    => $this->disk->get($name),
            'delete' => $this->disk->delete($name),
        ];
    }
}
