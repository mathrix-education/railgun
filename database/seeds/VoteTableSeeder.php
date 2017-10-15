<?php
use App\Models\Vote;

class VoteTableSeeder extends BaseTableSeeder
{
    public function run()
    {
        $this->println('<comment>Seeding:</comment> votes from factory');
        factory(Vote::class, 500)->create();
        $this->println('<info>Seeded:</info>  votes from factory');
    }
}