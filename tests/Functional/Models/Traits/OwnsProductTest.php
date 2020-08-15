<?php

declare(strict_types=1);

namespace Tests\Functional\Models\Traits;

use Railgun\Models\Contracts\Ownable;
use Railgun\Models\User;
use Tests\Functional\Models\ModelTestCase;
use function factory;

/**
 * @covers \Railgun\Billing\Models\OwnsProducts
 */
class OwnsProductTest extends ModelTestCase
{
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
