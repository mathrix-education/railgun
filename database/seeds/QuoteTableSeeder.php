<?php
use App\Models\Quote;

class QuoteTableSeeder extends BaseTableSeeder
{
    public function run()
    {
        $this->println('<comment>Seeding:</comment> quotes from factory');

        factory(Quote::class, 100)->create();

        $this->println('<info>Seeded:</info>  quotes from factory');
    }
}