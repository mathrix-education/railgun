<?php

declare(strict_types=1);

namespace Tests\Functional\Models\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\DB;
use Laravel\Lumen\Testing\DatabaseTransactions;
use Nuwave\Lighthouse\Testing\MakesGraphQLRequestsLumen;
use Railgun\Models\Label;
use Railgun\Models\Note;
use Railgun\Models\Traits\Notable;
use Railgun\Models\Traits\Randomizable;
use Railgun\Models\User;
use Tests\ApplicationTestCase;
use function array_search;
use function class_basename;
use function collect;
use function data_get;
use function factory;
use function strtolower;

/**
 * @testdox [Functional] Notable
 * @coversDefaultClass \Railgun\Models\Note
 */
class NotableTest extends ApplicationTestCase
{
    use DatabaseTransactions;
    use MakesGraphQLRequestsLumen;

    /**
     * @return string[][] The notable dataset.
     */
    public function createNotablesDataset(): array
    {
        return collect(Label::LABELABLES)
            ->mapWithKeys(static fn($c) => [class_basename($c) => [$c]])
            ->toArray();
    }

    /**
     * @dataProvider createNotablesDataset
     * @testdox      has a valid notable morph (using $class)
     * @covers ::notable
     *
     * @param string|Randomizable $class The model class.
     */
    public function testNotablesRelation(string $class): void
    {
        /** @var string $alias */
        $alias = array_search($class, Relation::$morphMap, true);
        /** @var Notable|Model $model */
        $model = $class::random();
        $user  = User::random();
        /** @var Note $note */
        $note = factory(Note::class)->create([
            'user_id'      => $user->id,
            'notable_type' => strtolower(class_basename($class)),
            'notable_id'   => $model->getKey(),
        ]);
        $note = $note->fresh(['notable']);

        $exists = DB::table('notes')
            ->where([
                ['user_id', '=', $user->id],
                ['notable_type', '=', $alias],
                ['notable_id', '=', $model->getKey()],
            ])
            ->exists();

        $this->assertTrue($exists);
        $this->assertEquals($model->getKey(), $note->notable->id);
    }

    /**
     * @dataProvider createNotablesDataset
     * @testdox      in in the GraphQL type associated with $class
     *
     * @param string|Randomizable $class The model class.
     */
    public function testNotablesTypes(string $class): void
    {
        $type  = $this->introspectType(class_basename($class));
        $found = collect($type['fields'])->firstWhere('name', '=', 'notes');

        $this->assertNotNull($found);
        $this->assertEquals('Note', data_get($found, 'type.ofType.ofType.name'));
    }
}
