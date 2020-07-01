<?php

declare(strict_types=1);

namespace Tests\Functional\Jobs;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Laravel\Lumen\Testing\DatabaseTransactions;
use Railgun\Jobs\CleanUserSession;
use Railgun\Models\Grade;
use Railgun\Models\Session;
use Railgun\Models\Theme;
use Railgun\Models\User;
use Tests\ApplicationTestCase;
use function factory;

/**
 * @testdox CleanUserSession Job
 * @coversDefaultClass \Railgun\Jobs\CleanUserSession
 */
class CleanUserSessionTest extends ApplicationTestCase
{
    use DatabaseTransactions;

    /**
     * @testdox deletes the previous user session on grade change
     */
    public function testHandle(): void
    {
        Queue::fake();

        $count = 5;

        /**
         * @var Grade $grade1 The grade initially assigned to the user.
         * @var Grade $grade2 The grade assigned after the session generation.
         */
        [$grade1, $grade2] = Grade::query()->inRandomOrder()->limit(2)->get();

        /** @var User $user */
        $user = factory(User::class)->create(['grade_id' => $grade1->id]);

        Theme::query()
            ->where('grade_id', '=', $grade1->id)
            ->has('chapters')
            ->get()
            ->mapWithKeys(static function (Theme $theme) {
                return [$theme->discipline_id => $theme];
            })
            ->values()
            ->splice(0, $count)
            ->each(static function (Theme $theme) use ($user): void {
                $session = new Session([
                    'user_id'       => $user->id,
                    'discipline_id' => $theme->discipline_id,
                    'chapter_id'    => $theme->chapters()->inRandomOrder()->firstOrFail()->id,
                ]);
                $session->save();
            });

        $this->assertEquals($count, DB::table('sessions')->where(['user_id' => $user->id])->count());

        $user->grade_id = $grade2->id;
        $user->save();

        (new CleanUserSession($user))->handle();
        $this->assertEquals(0, DB::table('sessions')->where(['user_id' => $user->id])->count());
    }
}
