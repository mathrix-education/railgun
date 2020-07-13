<?php

declare(strict_types=1);

namespace Tests\Functional\Models\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Laravel\Lumen\Testing\DatabaseTransactions;
use Nuwave\Lighthouse\Testing\MakesGraphQLRequestsLumen;
use Railgun\Models\Interaction;
use Railgun\Models\Traits\Interactable;
use Railgun\Models\Traits\Randomizable;
use Railgun\Models\User;
use Tests\ApplicationTestCase;
use function array_search;
use function class_basename;
use function collect;
use function data_get;
use function factory;

/**
 * @testdox Interactable Model trait
 * @coversDefaultClass \Railgun\Models\Traits\Interactable
 */
class InteractableTest extends ApplicationTestCase
{
    use DatabaseTransactions;
    use MakesGraphQLRequestsLumen;

    /**
     * @return string[][] The interactables dataset.
     */
    public function createInteractablesDataset(): array
    {
        return collect(Interaction::INTERACTABLES)
            ->mapWithKeys(static fn($c) => [class_basename($c) => [$c]])
            ->toArray();
    }

    /**
     * @dataProvider createInteractablesDataset
     * @testdox      has a valid relation with $class
     *
     * @param string|Randomizable $class The model class.
     */
    public function testInteractableRelation(string $class): void
    {
        /** @var string $alias */
        $alias = array_search($class, Relation::$morphMap, true);
        /** @var Interactable|Model $model */
        $model = $class::random();
        /** @var User $user */
        $user = factory(User::class)->create();
        $this->actingAs($user);

        $interaction = new Interaction([
            'user_id'           => $user->id,
            'interactable_type' => $alias,
            'interactable_id'   => $model->id,
        ]);
        $interaction->save();

        $interaction = $interaction->fresh(['interactable']);

        $this->assertTrue($interaction->interactable->is($model));
    }

    /**
     * @dataProvider createInteractablesDataset
     * @covers ::interactions
     *
     * @param string|Randomizable $class The model class.
     */
    public function testInteractionsRelation(string $class): void
    {
        /** @var string $alias */
        $alias = array_search($class, Relation::$morphMap, true);
        /** @var Interactable|Model $model */
        $model = $class::random();

        $interaction = new Interaction([
            'user_id'           => User::random()->id,
            'interactable_type' => $alias,
            'interactable_id'   => $model->getKey(),
        ]);
        $interaction->save();

        $model->refresh();

        $this->assertNotEmpty($model->interactions);
        $this->assertContains($interaction->id, $model->interactions->pluck('id')->toArray());
    }

    /**
     * @dataProvider createInteractablesDataset
     * @testdox      is in the GraphQL type associated with $class
     *
     * @param string|Randomizable $class The model class.
     */
    public function testInteractableTypes(string $class): void
    {
        $type  = $this->introspectType(class_basename($class));
        $found = collect($type['fields'])->firstWhere('name', '=', 'interaction');

        $this->assertNotNull($found);
        $this->assertEquals('Interaction', data_get($found, 'type.name'));
    }
}
