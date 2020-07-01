<?php

declare(strict_types=1);

namespace Tests\Functional\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Tests\ApplicationTestCase;
use function array_merge;
use function class_basename;
use function factory;
use function get_class;
use function method_exists;
use function str_replace;

abstract class ModelTestCase extends ApplicationTestCase
{
    /** @var string $modelClass The model class, resolved from the test class name. */
    protected string $modelClass;

    public function setUp(): void
    {
        parent::setUp();

        $this->modelClass = 'Railgun\\Models\\' . str_replace('Test', '', class_basename($this));
    }

    /**
     * Assert that the provided model class has a valid {@see BelongsTo} relationship using the given relation.
     *
     * @param string  $relation          The relation name.
     * @param mixed[] $attributes        The model attributes.
     * @param mixed[] $relatedAttributes The related model attributes.
     */
    public function assertHasOne(
        string $relation,
        array $attributes = [],
        array $relatedAttributes = []
    ): void
    {
        /** @var Model $instance */
        $instance = factory($this->modelClass)->create($attributes);
        $this->assertTrue(method_exists($instance, $relation));

        /** @var HasOne $hasMany */
        $hasMany = $instance->{$relation}();
        $this->assertInstanceOf(HasOne::class, $hasMany);

        $class   = get_class($hasMany->getQuery()->getModel());
        $related = factory($class)->create(array_merge(
            $relatedAttributes,
            [$hasMany->getForeignKeyName() => $instance->getKey()],
        ));

        $this->assertTrue($instance->{$relation}->is($related));
        $this->assertInstanceOf($class, $instance->{$relation});
    }

    /**
     * Assert that the provided model class has a valid {@see BelongsTo} relationship using the given relation.
     *
     * @param string  $relation          The relation name.
     * @param mixed[] $attributes        The model attributes.
     * @param mixed[] $relatedAttributes The related model attributes.
     */
    public function assertBelongsTo(
        string $relation,
        array $attributes = [],
        array $relatedAttributes = []
    ): void
    {
        /** @var Model $instance */
        $instance = (new $this->modelClass());
        $this->assertTrue(method_exists($instance, $relation));

        /** @var BelongsTo $belongsTo */
        $belongsTo = $instance->{$relation}();
        $this->assertInstanceOf(BelongsTo::class, $belongsTo);

        $class    = get_class($belongsTo->getQuery()->getModel());
        $instance = factory($this->modelClass)->create(array_merge(
            $attributes,
            [$belongsTo->getForeignKeyName() => factory($class)->create($relatedAttributes)->getKey()]),
        );

        $this->assertInstanceOf($class, $instance->{$relation});
    }

    /**
     * Assert that the provided model class has a valid {@see HasMany} relationship using the given relation.
     *
     * @param string  $relation          The relation name.
     * @param mixed[] $attributes        The model attributes.
     * @param mixed[] $relatedAttributes The related model attributes.
     */
    public function assertHasMany(
        string $relation,
        array $attributes = [],
        array $relatedAttributes = []
    ): void
    {
        /** @var Model $instance */
        $instance = factory($this->modelClass)->create($attributes);
        $this->assertTrue(method_exists($instance, $relation));

        /** @var HasMany $hasMany */
        $hasMany = $instance->{$relation}();
        $this->assertInstanceOf(HasMany::class, $hasMany);

        $class   = get_class($hasMany->getQuery()->getModel());
        $related = factory($class)->create(array_merge(
            $relatedAttributes,
            [$hasMany->getForeignKeyName() => $instance->getKey()],
        ));

        $this->assertTrue($instance->{$relation}->contains($related));
        $this->assertInstanceOf($class, $instance->{$relation}[0]);
    }

    /**
     * Assert that the provided model class has a valid {@see BelongsToMany} relationship using the given relation.
     *
     * @param string  $relation          The relation name.
     * @param mixed[] $attributes        The model attributes.
     * @param mixed[] $relatedAttributes The related model attributes.
     * @param mixed[] $pivotAttributes   The pivot attributes.
     */
    public function assertBelongsToMany(
        string $relation,
        array $attributes = [],
        array $relatedAttributes = [],
        array $pivotAttributes = []
    ): void
    {
        /** @var Model $instance */
        $instance = factory($this->modelClass)->create($attributes);
        $this->assertTrue(method_exists($instance, $relation));

        /** @var BelongsToMany $belongsToMany */
        $belongsToMany = $instance->{$relation}();
        $this->assertInstanceOf(BelongsToMany::class, $belongsToMany);

        $class   = get_class($belongsToMany->getQuery()->getModel());
        $related = factory($class)->create($relatedAttributes);

        $belongsToMany->attach($related->getKey(), $pivotAttributes);

        $this->assertTrue($instance->{$relation}->contains($related));
        $this->assertInstanceOf($class, $instance->{$relation}[0]);
    }
}
