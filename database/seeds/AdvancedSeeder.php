<?php

declare(strict_types=1);

use Faker\Factory;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Base class for advance seeders.
 * Expose seeding methods (from a collection, factory or raw file).
 */
class AdvancedSeeder extends Seeder
{
    protected Faker $faker;

    public function __construct()
    {
        $this->faker = Factory::create();
    }

    /**
     * @param string      $model      The model class.
     * @param int|mixed[] $options    The number of models to generate (int) or the options (array).
     * @param mixed[]     $attributes The overridden attributes.
     *
     * @return Collection|mixed[] The generated data.
     */
    protected function seedFactory(
        string $model,
        $options = [],
        array $attributes = []
    ): Collection
    {
        $count = 100;

        if (is_int($options)) {
            $count = $options;
        } elseif (isset($options['count']) && is_int($options['count'])) {
            $count = $options['count'];
        }

        $factory = factory($model, $count);

        if (isset($options['state'])) {
            if (is_string($options['state'])) {
                $factory = $factory->state($options['state']);
            } elseif (is_array($options['state'])) {
                $factory = $factory->states($options['state']);
            }
        }

        $data = collect($factory->raw($attributes));

        if (!isset($options['seed']) || $options['seed']) {
            $table = $options['table'] ?? $this->getModelTable($model);
            $this->writeFactoryMessage($model, $table);
            $this->seedCollection($data, $table);
        }

        return $data;
    }

    /**
     * Get the table of a model class.
     *
     * @param string $model The model class.
     *
     * @return string The model table.
     */
    protected function getModelTable(string $model): string
    {
        /** @var Model $instance */
        $instance = new $model();

        return $instance->getTable();
    }

    /**
     * Write the collection debug message.
     *
     * @param string $table The model table.
     */
    protected function writeCollectionMessage(?string $table = null): void
    {
        $this->writeln("Seeding <info>$table</info> from collection");
    }

    /**
     * Write the factory debug message.
     *
     * @param string      $model The model class.
     * @param string|null $table Override the displayed model table.
     */
    protected function writeFactoryMessage(string $model, ?string $table = null): void
    {
        $table ??= $this->getModelTable($model);
        $this->writeln("Seeding <info>$table</info> from <info>$model</info> factory");
    }

    /**
     * Shortcut to command output writeln() method.
     *
     * @see SymfonyStyle::writeln
     *
     * @param string $message The message to print.
     * @param int    $type    The message type ({@see SymfonyStyle}).
     */
    protected function writeln(string $message, int $type = SymfonyStyle::OUTPUT_NORMAL): void
    {
        $this->command->getOutput()->writeln($message, $type);
    }

    /**
     * @param Collection|mixed[] $data  The data to insert.
     * @param string             $table The target database table.
     * @param int                $chunk The chunk size for database insert (1000 by default).
     */
    protected function seedCollection(Collection $data, string $table, int $chunk = 1000): void
    {
        $progress = $this->command->getOutput()->createProgressBar();
        $progress->start($data->count());

        $data
            ->map(static function (array $model) {
                // Loop over fields to find arrays and stdClass
                foreach ($model as $key => $value) {
                    if (!is_array($value)) {
                        continue;
                    }

                    $model[$key] = json_encode($value, JSON_THROW_ON_ERROR, 512);
                }

                return $model;
            })
            ->chunk($chunk)
            ->each(static function (Collection $data) use ($table, $progress): void {
                DB::table($table)->insert($data->toArray());
                $progress->advance($data->count());
            });

        $progress->finish();
        $this->command->getOutput()->newLine(2);
    }

    /**
     * @param string         $file    The file containing the data.
     * @param string|mixed[] $options The table (string) or the options (array).
     *
     * @return Collection|mixed[] The inserted data.
     *
     * @throws JsonException
     */
    protected function seedFile(string $file, $options = []): Collection
    {
        $extension = pathinfo($file, PATHINFO_EXTENSION);

        if (is_string($options)) {
            $table = $options;
        } elseif (isset($options['table'])) {
            $table = $options['table'];
        } else {
            $table = str_replace(".$extension", '', basename($file));
        }

        $hypothesis = [
            $file,
            storage_path($file),
            storage_path("raws/$file"),
            database_path($file),
            database_path("raws/$file"),
        ];

        foreach ($hypothesis as $potentialFile) {
            if (file_exists($potentialFile)) {
                $file = $potentialFile;
                break;
            }
        }

        $content = file_get_contents($file);

        if ($extension !== 'json') {
            throw new RuntimeException("Unrecognized extension: $extension");
        }

        $data = collect(json_decode($content, true, 512, JSON_THROW_ON_ERROR));

        if (!isset($options['seed']) || $options['seed']) {
            $file = str_replace(app()->basePath(), '', $file);
            $this->writeln("Seeding <info>$table</info> from <info>$file</info>");
            $this->seedCollection($data, $table, $options['chunk'] ?? 1000);
        }

        return $data;
    }

    /**
     * Link two tables.
     *
     * @param string       $left    The left table
     * @param string       $right   The right table.
     * @param string|mixed $options The link options.
     *
     * phpcs:disable Generic.Metrics.CyclomaticComplexity
     */
    protected function link(string $left, string $right, $options = []): void
    {
        $defaultOptions = [
            'mode'     => 'left',
            'leftFk'   => null,
            'rightFk'  => null,
            'join'     => null,
            'callback' => null,
        ];

        if (is_string($options)) {
            $options = ['mode' => $options];
        }

        $options = array_replace($defaultOptions, $options);

        if ($options['mode'] === 'right') {
            $this->link($right, $left, 'left');

            return;
        }

        if ($options['mode'] === 'both') {
            $this->link($left, $right, 'left');
            $this->link($left, $right, 'right');

            return;
        }

        if (Str::contains($left, '\\')) {
            // This is probably a model class
            $left = $this->getModelTable($left);
        }

        if (Str::contains($right, '\\')) {
            // This is probably a model class
            $right = $this->getModelTable($right);
        }

        $this->writeln("Linking <info>$left</info> <=> <info>$right</info>");

        $leftSingular  = Str::singular($left);
        $rightSingular = Str::singular($right);
        $leftFk        = $options['leftFk'] ?? Str::snake($leftSingular) . '_id';
        $rightFk       = $options['rightFk'] ?? Str::snake($rightSingular) . '_id';

        if ($options['join'] === null) {
            if (strcasecmp($leftSingular, $rightSingular) < 0) {
                $join = "{$leftSingular}_{$rightSingular}";
            } else {
                $join = "{$rightSingular}_{$leftSingular}";
            }
        } else {
            $join = $options['join'];
        }

        if (isset($options['callback'])) {
            $callback = $options['callback'];

            if ($callback === 'order') {
                $callback = static function ($join, $order) {
                    return ['order' => $order] + $join;
                };
            }
        } else {
            $callback = null;
        }

        $leftIds  = DB::table($left)->get(['id'])->pluck('id');
        $rightIds = DB::table($right)->get(['id'])->pluck('id');

        $leftIds
            ->map(function ($leftId) use ($rightIds, $leftFk, $rightFk, $callback) {
                $max = min(5, $rightIds->count());

                return collect($this->faker->randomElements(
                    $rightIds->toArray(),
                    $this->faker->numberBetween(1, $max),
                ))->map(static function ($rightId, int $index) use ($leftId, $leftFk, $rightFk, $callback) {
                    $join = [
                        $leftFk  => $leftId,
                        $rightFk => $rightId,
                    ];

                    if (is_callable($callback)) {
                        return $callback($join, $index);
                    }

                    return $join;
                });
            })
            ->flatten(1)
            ->mapWithKeys(static function (array $join) use ($leftFk, $rightFk) {
                // Avoid composite keys collision
                return ["$leftFk-$rightFk" => $join];
            })
            ->values()
            ->tap(fn(Collection $data) => $this->seedCollection($data, $join));
    }
}
