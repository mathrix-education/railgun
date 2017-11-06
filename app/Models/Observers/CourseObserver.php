<?php
namespace App\Models\Observers;

use App\Models\Course;

/**
 * Class CourseObserver
 *
 * @author Mathrix Team
 * @package App\Models\Observers
 */
class CourseObserver extends BaseObserver
{
    /**
     * @param Course $course
     *
     * @return bool
     */
    public function saving($course)
    {
        // Sluggify if needed
        if (empty($course->slug) ||
            ($course->isDirty("title") && !$course->isDirty("slug"))) {
            $course->slug = str_slug($course->title);
        }

        return true;
    }
}