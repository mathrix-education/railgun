<?php
use App\Models\Comment;

class CommentTableSeeder extends BaseTableSeeder
{
    public function run()
    {

        $this->println('<comment>Seeding:</comment> comments from factory');

        factory(Comment::class, 200)->create();

        $this->println('<info>Seeded:</info>  comments from factory');
    }
}