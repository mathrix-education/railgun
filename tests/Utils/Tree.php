<?php

namespace Tests\Utils;

use App\Models\Activity;
use App\Models\Chapter;
use App\Models\Class_;
use App\Models\Country;
use App\Models\Discipline;
use App\Models\Module;
use App\Models\Notion;

/**
 * Class Tree.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 *
 * @since     4.0.1
 *
 * @copyright Mathrix Education SA
 */
class Tree
{
    /** @var Country */
    private $country;
    /** @var Class_ */
    private $class;
    /** @var Discipline */
    private $discipline;
    /** @var Chapter */
    private $chapter;
    /** @var Notion */
    private $notion;
    /** @var Activity */
    private $activity;
    /** @var Module */
    private $module;

    public function __construct()
    {
        // Build the tree
        $this->chapter = Chapter::random();
        $this->class = $this->chapter->class_;
        $this->discipline = $this->chapter->discipline;
        $this->country = $this->class->country;
        $this->notion = $this->chapter->notions()
            ->inRandomOrder()
            ->firstOrFail();
        $this->activity = $this->notion->activities()
            ->inRandomOrder()
            ->firstOrFail();
        $this->module = $this->activity->modules()
            ->inRandomOrder()
            ->firstOrFail();
    }

    /**
     * @return Country
     */
    public function getCountry(): Country
    {
        return $this->country;
    }

    /**
     * @return Class_
     */
    public function getClass(): Class_
    {
        return $this->class;
    }

    /**
     * @return Discipline
     */
    public function getDiscipline(): Discipline
    {
        return $this->discipline;
    }

    /**
     * @return Chapter
     */
    public function getChapter(): Chapter
    {
        return $this->chapter;
    }

    /**
     * @return Notion
     */
    public function getNotion(): Notion
    {
        return $this->notion;
    }

    /**
     * @return Activity
     */
    public function getActivity(): Activity
    {
        return $this->activity;
    }

    /**
     * @return Module
     */
    public function getModule(): Module
    {
        return $this->module;
    }

    /**
     * Get a flat tree as array with the model ids.
     *
     * @param null $stripAfter Strip after a given model (for example to strip after the activity, pass "activity")
     *
     * @throws \Exception
     *
     * @return array
     */
    public function flatIds($stripAfter = null)
    {
        $flatIds = [
            "country_id" => $this->country->id,
            "class_id" => $this->class->id,
            "discipline_id" => $this->discipline->id,
            "chapter_id" => $this->chapter->id,
            "notion_id" => $this->notion->id,
            "activity_id" => $this->activity->id,
            "module_id" => $this->module->id,
        ];

        // Strip process if needed
        if ($stripAfter !== null) {
            $strip = false;
            foreach ($flatIds as $key => $id) {
                if ($key === $stripAfter . "_id") {
                    $strip = true;
                }

                if ($strip) {
                    unset($flatIds[$key]);
                }
            }

            if (!$strip) {
                throw new \Exception("Param \$stripAfter was provided with value \"$stripAfter\" but nothing was stripped.");
            }
        }

        return $flatIds;
    }
}
