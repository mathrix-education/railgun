<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;

class MakeTests extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:tests {model}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send drip e-mails to a user';

    /**
     * MakeTests constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $model = $this->argument('model');

        $this->info('Generate unit tests for ' . $model);
    }
}