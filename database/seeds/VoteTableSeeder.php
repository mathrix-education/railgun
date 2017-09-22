<?php
use App\Models\Comment;
use App\Models\Vote;
use Mathrix\Lumen\Database\BaseTableSeeder;

class VoteTableSeeder extends BaseTableSeeder
{
    public function run()
    {
        $this->println('<comment>Seeding:</comment> votes from factory');
        factory(Vote::class, 500)->create();
        $this->println('<info>Seeded:</info>  votes from factory');
    }
}