<?php
namespace App\Observers;

use App\Models\Lesson;

/**
 * Class LessonObserver
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @package App\Observers
 * @since 1.0.1
 * @copyright Mathrix Education SA
 */
class LessonObserver extends BaseObserver
{
    public function saving(Lesson $lesson)
    {
        // Sluggify if needed
        if (empty($lesson->slug) ||
            ($lesson->isDirty("name") && !$lesson->isDirty("slug"))) {
            $lesson->slug = str_slug($lesson->title);
        }
    }
}
