<?php

declare(strict_types=1);

namespace Railgun\GraphQL\Mutations;

use Illuminate\Support\Arr;
use Railgun\Models\Chapter;
use Railgun\Models\Course;
use Railgun\Models\Exam;
use Railgun\Models\Exercise;
use Railgun\Models\Flashcard;
use Railgun\Models\Notion;
use Railgun\Models\Subject;

class CloneChapter
{
    /**
     * @param null    $_    The parent value, always null in this case.
     * @param mixed[] $args The mutation arguments (id, theme_id).
     *
     * @return Chapter The cloned chapter.
     */
    public function __invoke($_, array $args): Chapter
    {
        /** @var Chapter $chapter */
        $chapter = Chapter::query()->with([
            'notions',
            'notions.courses',
            'notions.subjects.exercises',
            'flashcards',
            'exams',
        ])->findOrFail(Arr::pull($args, 'id'));

        $clone = $chapter->newClone($args);

        $chapter->notions->each(static function (Notion $notion) use ($clone): void {
            $notionClone = $notion->newClone(['chapter_id' => $clone->id]);

            $notion->courses->each(static function (Course $course) use ($notionClone): void {
                $course->newClone(['notion_id' => $notionClone->id]);
            });

            $notion->subjects->each(static function (Subject $subject) use ($notionClone): void {
                $subjectClone = $subject->newClone(['notion_id' => $notionClone->id]);

                $subject->exercises->each(static function (Exercise $exercise) use ($subjectClone): void {
                    $exercise->newClone(['subject_id' => $subjectClone->id]);
                });
            });
        });

        $chapter->flashcards->each(static function (Flashcard $flashcard) use ($clone): void {
            $flashcard->newClone(['chapter_id' => $clone->id]);
        });

        $chapter->exams->each(static function (Exam $exam) use ($clone): void {
            $exam->newClone(['chapter_id' => $clone->id]);
        });

        return $clone->fresh();
    }
}
