<?php

declare(strict_types=1);

namespace Mathrix\Healthz\Checks;

use Illuminate\Container\Container;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Connection;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use InvalidArgumentException;
use function array_key_exists;
use function array_merge;
use function collect;
use function sprintf;
use function str_replace;
use function strtolower;

/**
 * Database check which retrieves the permissions of the user on the default database.
 */
class DatabaseCheck extends Check
{
    public const NAME = 'database';

    private DatabaseManager $manager;
    private Connection      $database;

    /**
     * @param mixed[] $options The check options
     *
     * @throws BindingResolutionException
     */
    public function __construct(array $options = [])
    {
        $this->manager = Container::getInstance()->make('db');

        parent::__construct(array_merge([
            'connection' => $this->manager->getName(),
        ], $options));

        $this->database = $this->manager->connection($this->options['connection']);
    }

    /**
     * @return string The database check name, based on if the selected connection is the default one.
     */
    public function getName(): string
    {
        return 'database' . (
            $this->options['connection'] === $this->manager->getName()
                ? ''
                : '_' . $this->manager->getName()
            );
    }

    /**
     * Read the current user privileges.
     *
     * @return Collection|mixed[] The database check details.
     */
    public function execute(): array
    {
        switch ($this->database->getDriverName()) {
            case 'mysql':
                return $this->executeMySQL();
            default:
                throw new InvalidArgumentException(sprintf(
                    'Driver %s is not supported',
                    $this->database->getDriverName(),
                ));
        }
    }

    /**
     * @return mixed[] The MySQL check details.
     */
    private function executeMySQL(): array
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

        return collect(array_merge($global, $specific))
            ->filter(static fn($v, $k) => Str::contains($k, 'priv'))
            ->mapWithKeys(static function (string $v, string $k) {
                $key   = str_replace('_priv', '', strtolower($k));
                $value = $v === 'Y';

                return [$key => $value];
            })
            ->toArray();
    }
}
