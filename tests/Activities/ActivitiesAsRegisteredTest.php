<?php
namespace Tests\Activities;

use App\Models\Activity;
use App\Models\Chapter;
use App\Models\Class_;
use App\Models\Country;
use App\Models\Discipline;
use App\Models\Notion;
use Tests\ScopeTestCase;

/**
 * Class ActivitiesAsPublicTest
 * Test activities as registered user
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @copyright Mathrix
 * @version 1.0.0
 * @package Tests\Activities
 */
class ActivitiesAsRegisteredTest extends ScopeTestCase
{
    public function testIndex()
    {
        $this->sameAs('public');
    }


    public function testAdd()
    {
        $this->sameAs('public');
    }


    public function testEdit()
    {
        $this->sameAs('public');
    }


    public function testDelete()
    {
        $this->sameAs('public');
    }


    public function buildObjects()
    {
        /** @var Country $country */
        $country = Country::where('name', '=', 'France')->firstOrFail();

        /** @var Class_ $class */
        $class = $country->classes()->inRandomOrder()->firstOrFail();

        /** @var Discipline $discipline */
        $discipline = $class->disciplines()->inRandomOrder()->firstOrFail();

        /** @var Chapter $chapter */
        $chapter = Chapter::where([
            ['class_id', '=', $class->id],
            ['discipline_id', '=', $discipline->id]
        ])->inRandomOrder()->firstOrFail();

        /** @var Notion $notion */
        $notion = $chapter->notions()->inRandomOrder()->firstOrFail();

        /** @var Activity $activity */
        $activity = $notion->activities()->inRandomOrder()->firstOrFail();

        return [$country, $class, $discipline, $chapter, $notion, $activity];
    }


    public function testMarkFavorite()
    {
        [$country, $class, $discipline, $chapter, $notion, $activity] = $this->buildObjects();

        $this->call('patch', '/activities/' . $activity->id . '/mark', [
            'favorite' => true,
            'country_id' => $country->id,
            'class_id' => $class->id,
            'discipline_id' => $discipline->id,
            'chapter_id' => $chapter->id,
            'notion_id' => $notion->id
        ]);

        $this->assertHttp200Success();
    }


    public function testMarkStatus()
    {
        [$country, $class, $discipline, $chapter, $notion, $activity] = $this->buildObjects();

        $this->call('patch', '/activities/' . $activity->id . '/mark', [
            'status' => $this->faker->boolean() ? 'completed' : 'to_revise',
            'country_id' => $country->id,
            'class_id' => $class->id,
            'discipline_id' => $discipline->id,
            'chapter_id' => $chapter->id,
            'notion_id' => $notion->id
        ]);

        $this->assertHttp200Success();
    }
}