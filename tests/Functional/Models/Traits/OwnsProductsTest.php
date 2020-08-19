<?php

declare(strict_types=1);

namespace Tests\Functional\Models\Traits;

use Illuminate\Support\Collection;
use Railgun\Models\Acquisition;
use Railgun\Models\Chapter;
use Railgun\Models\Contracts\Ownable;
use Railgun\Models\Pack;
use Railgun\Models\Training;
use Railgun\Models\User;
use Tests\Functional\Models\ModelTestCase;
use function factory;
use function resolveMorph;

/**
 * @coversDefaultClass \Railgun\Billing\Models\OwnsProducts
 */
class OwnsProductsTest extends ModelTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->modelClass = User::class;
    }

    /**
     * @covers \Railgun\Billing\Models\OwnsProducts::acquisitions
     */
    public function testAcquisitions(): void
    {
        $this->assertHasMany('acquisitions');
    }

    /**
     * @covers \Railgun\Billing\Models\OwnsProducts::chapters
     */
    public function testChapters(): void
    {
        /** @var User $user */
        $user = factory(User::class)->create();
        $this->assertInstanceOf(Collection::class, $user->chapters()->get());
    }

    /**
     * @covers \Railgun\Billing\Models\OwnsProducts::packs
     */
    public function testPacks(): void
    {
        /** @var User $user */
        $user = factory(User::class)->create();
        $this->assertInstanceOf(Collection::class, $user->packs()->get());
    }

    /**
     * @covers \Railgun\Billing\Models\OwnsProducts::trainings
     */
    public function testTrainings(): void
    {
        /** @var User $user */
        $user = factory(User::class)->create();
        $this->assertInstanceOf(Collection::class, $user->trainings()->get());
    }

    public function testOwnsChapter(): void
    {
        /** @var User $user */
        $user = factory(User::class)->create();
        /** @var Chapter $chapter */
        $chapter = Chapter::query()
            ->inRandomOrder()
            ->has('notions.courses')
            ->has('notions.subjects.exercises')
            ->has('flashcards')
            ->has('exams')
            ->firstOrFail();
        /** @var Acquisition $acquisition */
        $acquisition = factory(Acquisition::class)->create([
            'user_id'      => $user->id,
            'product_type' => resolveMorph($chapter),
            'product_id'   => $chapter->getKey(),
        ]);

        $this->assertTrue($user->owns($chapter));
        $this->assertTrue($user->owns($acquisition->product));
    }

    public function testOwnsPack(): void
    {
        /** @var User $user */
        $user = factory(User::class)->create();
        /** @var Pack $pack */
        $pack = Pack::query()->inRandomOrder()->firstOrFail();
        /** @var Acquisition $acquisition */
        $acquisition = factory(Acquisition::class)->create([
            'user_id'      => $user->id,
            'product_type' => resolveMorph($pack),
            'product_id'   => $pack->getKey(),
        ]);

        $this->assertTrue($user->owns($acquisition->product));
    }

    public function testOwnsTraining(): void
    {
        /** @var User $user */
        $user = factory(User::class)->create();
        /** @var Training $training */
        $training = Training::query()
            ->inRandomOrder()
            ->has('sections')
            ->firstOrFail();
        /** @var Acquisition $acquisition */
        $acquisition = factory(Acquisition::class)->create([
            'user_id'      => $user->id,
            'product_type' => resolveMorph($training),
            'product_id'   => $training->getKey(),
        ]);

        $this->assertTrue($user->owns($acquisition->product));
    }

    /**
     * @testWith ["\\Railgun\\Models\\Chapter"]
     *           ["\\Railgun\\Models\\Notion"]
     *           ["\\Railgun\\Models\\Course"]
     *           ["\\Railgun\\Models\\Subject"]
     *           ["\\Railgun\\Models\\Exercise"]
     *           ["\\Railgun\\Models\\Flashcard"]
     *           ["\\Railgun\\Models\\Exam"]
     *           ["\\Railgun\\Models\\Lesson"]
     *           ["\\Railgun\\Models\\Pack"]
     *           ["\\Railgun\\Models\\Training"]
     *           ["\\Railgun\\Models\\Section"]
     *           ["\\Railgun\\Models\\Lesson"]
     *
     * @param string|Ownable $modelClass
     */
    public function testDoesNotOwns(string $modelClass): void
    {
        /** @var User $user */
        $user  = factory(User::class)->create();
        $model = $modelClass::random();

        $this->assertFalse($user->owns($model));
    }
}
