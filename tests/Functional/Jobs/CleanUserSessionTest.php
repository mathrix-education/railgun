<?php

declare(strict_types=1);

namespace Tests\Functional\Jobs;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Railgun\Jobs\CleanUserSession;
use Railgun\Models\Chapter;
use Railgun\Models\Discipline;
use Railgun\Models\Grade;
use Railgun\Models\Session;
use Railgun\Models\User;
use Tests\ApplicationTestCase;
use function factory;

/**
 * @testdox CleanUserSession Job
 * @coversDefaultClass \Railgun\Jobs\CleanUserSession
 */
class CleanUserSessionTest extends ApplicationTestCase
{
    /**
     * @testdox deletes the previous user session on grade change.
     */
    public function testHandle(): void
    {
        Queue::fake();

        $count = 5;

        $grade1 = Grade::random();
        /** @var User $user */
        $user = factory(User::class)->create(['grade_id' => $grade1->id]);
        /** @var Collection|Discipline[] $disciplines */
        $disciplines = Discipline::query()->inRandomOrder()->limit($count)->get();

        factory(Chapter::class, $count)
            ->create(['grade_id' => $grade1->id])
            ->each(static function (Chapter $chapter, int $index) use ($user, $disciplines): void {
                $session = new Session([
                    'user_id'       => $user->id,
                    'discipline_id' => $disciplines[$index]->id,
                    'chapter_id'    => $chapter->id,
                ]);
                $session->save();
            });

        $this->assertEquals($count, DB::table('sessions')->where(['user_id' => $user->id])->count());

        /** @var Grade $grade2 */
        $grade2 = Grade::query()->inRandomOrder()->whereKeyNot($grade1->getKey())->firstOrFail();

        $user->grade_id = $grade2->id;
        $user->save();

        (new CleanUserSession($user))->handle();
        $this->assertEquals(0, DB::table('sessions')->where(['user_id' => $user->id])->count());
    }
}
