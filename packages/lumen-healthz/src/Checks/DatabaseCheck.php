<?php

declare(strict_types=1);

namespace Mathrix\Healthz\Checks;

use Illuminate\Container\Container;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Connection;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use function array_key_exists;
use function array_merge;
use function collect;
use function str_replace;
use function strtolower;

/**
 * Database check which retrieves the permissions of the user on the default database.
 */
class DatabaseCheck
{
    public const NAME = 'database';

    private DatabaseManager $manager;
    private Connection      $database;
    /** @var mixed[] $options The check options */
    private array $options;

    /**
     * @param mixed[] $options The check options
     *
     * @throws BindingResolutionException
     */
    public function __construct(array $options = [])
    {
        $this->manager = Container::getInstance()->make('db');

        $this->options = array_merge([
            'connection' => $this->manager->getName(),
            'details'    => true,
        ], $options);

        $this->database = $this->manager->connection($this->options['connection']);
    }

    public function getName(): string
    {
        return 'database' . (
            $this->options['connection'] === $this->manager->getName()
                ? ''
                : '_' . $this->manager->getName()
            );
    }

    /**
     * @return Collection|mixed[] The database check details.
     */
    public function execute(): ?Collection
    {
        $global = $this->database->select(
            'SELECT * FROM mysql.user WHERE User = ?',
            [$this->database->getConfig('username')],
        );

        if (array_key_exists(0, $global)) {
            $global = (array)$global[0];
        }

        $specific = $this->database->select(
            'SELECT * FROM mysql.db WHERE User = ? && Db = ?',
            [$this->database->getConfig('username'), $this->database->getConfig('database')],
        );

        if (array_key_exists(0, $specific)) {
            $specific = (array)$specific[0];
        }

        if (!$this->options['details']) {
            return null;
        }

        return collect(array_merge($global, $specific))
            ->filter(static fn($v, $k) => Str::contains($k, 'priv'))
            ->mapWithKeys(static function (string $v, string $k) {
                $key   = str_replace('_priv', '', strtolower($k));
                $value = $v === 'Y';

                return [$key => $value];
            });
    }
}
