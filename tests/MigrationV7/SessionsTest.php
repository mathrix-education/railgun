<?php

declare(strict_types=1);

namespace Tests\MigrationV7;

use Illuminate\Support\Facades\DB;
use Tests\BaseTestCase;

class SessionsTest extends BaseTestCase
{
    public function testSessionsIntegrity(): void
    {
        DB::table('sessions')
            ->join('chapters', 'sessions.chapter_id', '=', 'chapters.id')
            ->join('users', 'sessions.user_id', '=', 'users.id')
            ->inRandomOrder()
            ->get([
                'sessions.*',
                'chapters.grade_id as chapter_grade_id',
                'users.grade_id as user_grade_id',
            ])
            ->each(fn($session) => $this->assertEquals($session->chapter_grade_id, $session->user_grade_id));
    }
}
