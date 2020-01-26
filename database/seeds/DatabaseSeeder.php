<?php

declare(strict_types=1);

class DatabaseSeeder extends AdvancedSeeder
{
    public function run(): void
    {
        $this->writeln('Starting database seeding (this might take a while!)');
        putenv('CACHE_DRIVER=array');
        $start = time();

        $this->callSilent(ContentSeeder::class);
        $this->callSilent(TrainingsSeeder::class);
        $this->callSilent(BlogSeeder::class);
        $this->callSilent(UsersSeeder::class);
        $this->callSilent(CommentsSeeder::class);
        $this->callSilent(InteractionsSeeder::class);

        $duration = time() - $start;
        $this->writeln("<info>> Done in $duration seconds</info>");
    }
}
