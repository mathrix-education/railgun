<?php

declare(strict_types=1);

namespace Railgun\Jobs;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use stdClass;
use function array_key_exists;
use function implode;

class GravityJob extends Job
{
    public const ORDER_COLUMN = 'order';
    public const IDENTIFIERS  = ['uuid', 'id'];

    protected string $table;
    /** @var string[] $groups The order groups. */
    protected array  $groups;

    /**
     * @param string   $table  The table name.
     * @param string[] $groups The order groups.
     */
    public function __construct(string $table, array $groups = [])
    {
        $this->table  = $table;
        $this->groups = $groups;
    }

    public function handle(): void
    {
        /** @var Collection|stdClass[] $lines */
        $lines = DB::table($this->table)
            ->orderByRaw(implode(',', [...$this->groups, ...['`' . self::ORDER_COLUMN . '`']]))
            ->get()
            ->map(static fn(stdClass $line) => (array)$line);

        if ($lines->isEmpty()) {
            return; // The table is empty
        }

        foreach (self::IDENTIFIERS as $identifier) {
            if (array_key_exists($identifier, $lines->first())) {
                break;
            }

            unset($identifier);
        }

        if (!isset($identifier)) {
            $identifier = null;
        }

        $group    = null;
        $iterator = 0;

        $lines
            ->map(function (array $line) use ($identifier, &$group, &$iterator) {
                if ($identifier) {
                    $wheres = [$identifier => $line[$identifier]];
                } else {
                    $wheres = Arr::except($line, [self::ORDER_COLUMN]);
                }

                $theGroup = Arr::only($line, $this->groups);

                if ($group !== $theGroup) {
                    $iterator = 0;
                    $group    = $theGroup;
                }

                return [
                    'wheres' => $wheres,
                    'values' => [
                        self::ORDER_COLUMN => $iterator++,
                    ],
                ];
            })
            ->each(function (array $line): void {
                DB::table($this->table)->where($line['wheres'])->update($line['values']);
            });
    }
}
