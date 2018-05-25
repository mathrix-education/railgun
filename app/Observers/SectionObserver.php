<?php
namespace App\Observers;


use App\Exceptions\Http400InvalidRequestException;
use App\Models\Section;

/**
 * Class ActivityObserver
 *
 * @author Mathrix Team
 * @package App\Observers
 */
class SectionObserver extends BaseObserver
{
    /**
     * @param Section $section
     *
     * @return bool
     */
    public function saving($section)
    {
        // Sluggify if needed
        if (empty($section->slug) ||
            ($section->isDirty("name") && !$section->isDirty("slug"))) {
            $section->slug = str_slug($section->name);
        }

        return true;
    }


    /**
     * Detach all modules and notions linked with the deleting notion
     *
     * @param Section $section
     *
     * @return bool
     * @throws Http400InvalidRequestException
     * @throws \Exception
     */
    public function deleting($section)
    {
        // $section->lessons()->delete(); // TODO: Talk about the force option
        if ($section->lessons->isEmpty()) {
            throw new Http400InvalidRequestException("Cannot delete section {$section->id} be cause it has " .
                "attached lessons");
        }

        return true;
    }
}
