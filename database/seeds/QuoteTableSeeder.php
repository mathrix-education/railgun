<?php
use App\Models\Quote;
use Mathrix\Lumen\Database\BaseTableSeeder;

class QuoteTableSeeder extends BaseTableSeeder
{
    public function run()
    {
        $this->println('<comment>Seeding:</comment> quotes from factory');

        factory(Quote::class, 100);

        $this->println('<info>Seeded:</info>  quotes from factory');
    }
}