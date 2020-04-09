<?php

declare(strict_types=1);

namespace Tests\Functional\Models\Traits;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\DB;
use Laravel\Lumen\Testing\DatabaseTransactions;
use Nuwave\Lighthouse\Testing\MakesGraphQLRequestsLumen;
use Railgun\Models\Label;
use Railgun\Models\Traits\Labelable;
use Railgun\Models\Traits\Randomizable;
use Tests\ApplicationTestCase;
use function array_search;
use function class_basename;
use function collect;
use function data_get;
use function factory;

/**
 * @testdox Labelable Model trait
 */
class LabelableTest extends ApplicationTestCase
{
    use DatabaseTransactions;
    use MakesGraphQLRequestsLumen;

    /**
     * @return string[][] The labelables dataset.
     */
    public function createLabelablesDataset(): array
    {
        return collect(Label::LABELABLES)
            ->mapWithKeys(static fn($c) => [class_basename($c) => [$c]])
            ->toArray();
    }

    /**
     * @dataProvider createLabelablesDataset
     * @testdox      has a valid relation with $class
     *
     * @param string|Randomizable $class The model class.
     */
    public function testLabelablesRelation(string $class): void
    {
        /** @var string $alias */
        $alias = array_search($class, Relation::$morphMap, true);
        /** @var Label $label */
        $label = factory(Label::class)->create();
        /** @var Labelable|Model $model */
        $model = $class::random();

        $model->labels()->attach($label->id, ['labeled_at' => Carbon::now()]);
        $model = $model->fresh(['labels']);

        $exists = DB::table('labelables')
            ->where([
                ['label_id', '=', $label->id],
                ['labelable_type', '=', $alias],
                ['labelable_id', '=', $model->getKey()],
            ])
            ->exists();

        $this->assertTrue($exists);
        $this->assertNotEmpty($model->labels->firstWhere('id', $label->id));
    }

    /**
     * @dataProvider createLabelablesDataset
     * @testdox      is in the GraphQL type associated with $class
     *
     * @param string|Randomizable $class The model class.
     */
    public function testLabelablesTypes(string $class): void
    {
        $type  = $this->introspectType(class_basename($class));
        $found = collect($type['fields'])->firstWhere('name', '=', 'labels');

        $this->assertNotNull($found);
        $this->assertEquals('Label', data_get($found, 'type.ofType.ofType.name'));
    }
}
