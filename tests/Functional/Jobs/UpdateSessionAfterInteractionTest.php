<?php

declare(strict_types=1);

namespace Tests\Functional\Jobs;

use Illuminate\Support\Facades\Queue;
use Railgun\Jobs\UpdateSessionAfterInteraction;
use Railgun\Models\Chapter;
use Railgun\Models\Exam;
use Railgun\Models\Flashcard;
use Railgun\Models\Grade;
use Railgun\Models\Interaction;
use Railgun\Models\User;
use Tests\BaseTestCase;
use function factory;

/**
 * @testdox [Functional] UpdateSessionAfterInteraction Job
 */
class UpdateSessionAfterInteractionTest extends BaseTestCase
{
    /**
     * @testdox replaces an existing session [user_id, discipline_id].
     *
     * @ticket #207
     */
    public function testHandleExistingSession(): void
    {
        Queue::fake();

        $grade = Grade::random();

        /**
         * @var Chapter $first
         * @var Chapter $second
         */
        [$first, $second] = factory(Chapter::class, 2)->create(['grade_id' => $grade->id]);

        /** @var Flashcard $flashcard */
        $flashcard = factory(Flashcard::class)->create(['chapter_id' => $first->id]);

        /** @var Exam $exam */
        $exam = factory(Exam::class)->create(['chapter_id' => $second->id]);

        /** @var User $user */
        $user = factory(User::class)->create(['grade_id' => $grade->id]);

        $interaction1 = new Interaction([
            'user_id'           => $user->id,
            'interactable_type' => 'flashcard',
            'interactable_id'   => $flashcard->id,
        ]);
        $interaction1->save();
        (new UpdateSessionAfterInteraction($interaction1))->handle();

        $interaction2 = new Interaction([
            'user_id'           => $user->id,
            'interactable_type' => 'exam',
            'interactable_id'   => $exam->id,
        ]);
        $interaction2->save();
        (new UpdateSessionAfterInteraction($interaction2))->handle();

        $this->seeInDatabase('sessions', [
            'user_id'       => $user->id,
            'discipline_id' => $second->discipline_id,
            'chapter_id'    => $second->id,
        ]);
    }
}
