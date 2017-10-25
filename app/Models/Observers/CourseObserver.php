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
        // Ensure the title is present
        if (!$course->validate(['title' => 'required|max:255'])) {
            $course->setResponse($course->http422Validation([
                'message' => $course->errors()
            ]));

            return false;
        }

        // Sluggify if needed
        if (
            empty($course->slug) ||
            ($course->isDirty('title') && !$course->isDirty('slug'))
        ) {
            $course->slug = str_slug($course->title);
        }

        return true;
    }
}