<?php
namespace App\Tests;

use App\Models\Chapter;
use App\Models\Class_;
use App\Models\Country;
use App\Models\Discipline;
use App\Models\Notion;

/**
 * Abstract Class ContentBaseTest
 * Test Content Models
 * @author Mathrix Team
 */
abstract class ContentBaseTest extends BaseTest
{
    /*
    |--------------------------------------------------------------------------
    | Random data
    |--------------------------------------------------------------------------
    */
    /**
     * @var Country $country
     */
    public $country;

    /**
     * @var Class_ $class
     */
    public $class;

    /**
     * @var Discipline $discipline
     */
    public $discipline;

    /**
     * @var Chapter $chapter
     */
    public $chapter;

    /**
     * @var Notion $notion
     */
    public $notion;

    /**
     * Initialize country
     */
    public function initCountry(): void
    {
        $this->country = Country::where('slug_short', 'fr')->firstOrFail();
    }

    /**
     * Initialise class
     */
    public function initClass(): void
    {
        $this->initCountry();
        $this->class = $this->country
            ->classes()
            ->getResults()
            ->random();
    }

    /**
     * Initialize discipline
     */
    public function initDiscipline(): void
    {
        $this->initClass();
        $this->discipline = $this->class
            ->disciplines()
            ->getResults()
            ->random();
    }

    /**
     * Initialize chapter
     */
    public function initChapter(): void
    {
        $this->initDiscipline();
        $this->chapter = $this->discipline
            ->chapters()
            ->where('class_id', $this->class->id)
            ->getResults()
            ->random();
    }

    /**
     * Initialize notion
     */
    public function initNotion(): void
    {
        $this->initChapter();
        $this->notion = $this->chapter
            ->notions()
            ->getResults()
            ->random();
    }
}