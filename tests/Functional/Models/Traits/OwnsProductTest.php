<?php

declare(strict_types=1);

namespace Tests\Functional\Models\Traits;

use Railgun\Models\User;
use Tests\Functional\Models\ModelTestCase;
use function factory;
use function forward_static_call_array;

/**
 * @coversDefaultClass \Railgun\Billing\Models\OwnsProducts
 */
class OwnsProductTest extends ModelTestCase
{
    /**
     * @testWith ["\\Railgun\\Models\\Pack"]
     *           ["\\Railgun\\Models\\Training"]
     *           ["\\Railgun\\Models\\Section"]
     *           ["\\Railgun\\Models\\Lesson"]
     */
    public function testOwns(string $modelClass): void
    {
        /** @var User $user */
        $user  = factory(User::class)->create();
        $model = forward_static_call_array([$modelClass, 'random'], []);

        $this->assertFalse($user->owns($model));
    }
}
