<?php


namespace App\Console\Commands\Braintree;


use App\Console\Commands\BoxStyle;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use League\Csv\Reader;
use Mathrix\Lumen\Zero\Console\Commands\BaseCommand;

class BraintreeRestoreCommand extends BaseCommand
{
    use BoxStyle;

    protected const CHUNK_SIZE = 100;
    protected $signature = "braintree:restore";


    public function handle()
    {
        $this->call("braintree:restore:customers");
        $this->call("braintree:restore:subscriptions");
        $this->call("braintree:restore:transactions");
        $this->call("braintree:restore:line-items");
    }


    /**
     * @param string $file
     * @param string $table
     * @param callable|null $callback
     *
     * @throws Exception
     */
    protected function process(string $file, string $table, callable $callback = null)
    {
        $file = database_path("raws/$file.csv");

        $this->line("Restoring $table using " . str_replace(app()->basePath() . "/", "", $file));

        if (!file_exists($file)) {
            $this->line("Unable to find $file, exiting");

            return;
        }

        $csv = Reader::createFromPath($file, "r");
        $csv->setHeaderOffset(0);

        $data = new Collection();
        $records = $csv->getRecords();

        foreach ($records as $record) {
            $data->push($this->parse($record));
        }

        if ($callback !== null) {
            $data = $callback($data);
        }

        $this->progressStart($data->count());
        $data->chunk(self::CHUNK_SIZE)->each(function (Collection $chunk) use ($table) {
            DB::table($table)->insert($chunk->toArray());
            $this->progressAdvance($chunk->count());
        });
        $this->progressFinish();
    }


    protected function parse($record)
    {
        throw new Exception("Implement it!");
    }


    protected function notEmptyOrNull($var)
    {
        return !empty($var) ? $var : null;
    }
}
