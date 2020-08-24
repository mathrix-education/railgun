<?php

declare(strict_types=1);

namespace Tests\Functional\Models;

use Laravel\Lumen\Testing\DatabaseTransactions;

/**
 * @coversDefaultClass \Railgun\Models\Grade
 */
class GradeTest extends ModelTestCase
{
    use DatabaseTransactions;

    /**
     * @covers ::parent
     */
    public function testParent(): void
    {
        $this->assertBelongsTo('parent');
    }

    /**
     * @covers ::children
     */
    public function testChildren(): void
    {
        $this->assertHasMany('children');
    }

    /**
     * @covers ::country
     */
    public function testCountry(): void
    {
        $this->assertBelongsTo('country');
    }

    /**
     * @covers ::themes
     */
    public function testThemes(): void
    {
        $this->assertHasMany('themes');
    }

    /**
     * @covers ::disciplines
     */
    public function testDisciplines(): void
    {
        $this->assertBelongsToMany(
            'disciplines',
            [],
            [],
            ['order' => 99, 'visible' => true],
        );
    }

    /**
     * @covers ::users
     */
    public function testUsers(): void
    {
        $this->assertHasMany('users');
    }

    /**
     * @covers ::trainings
     */
    public function testTrainings(): void
    {
        $this->assertBelongsToMany('trainings');
    }
}
