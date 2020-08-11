<?php

declare(strict_types=1);

namespace Railgun\Registries;

use Illuminate\Support\Collection;
use Railgun\Contracts\BelongsToChapter;
use Railgun\Models\Chapter;
use Railgun\Models\Course;
use Railgun\Models\Exam;
use Railgun\Models\Exercise;
use Railgun\Models\Flashcard;
use Railgun\Models\Notion;
use Railgun\Models\Subject;
use Railgun\Support\Accelerator;

class ChapterContentRegistry extends Accelerator
{
    protected function fetch(): Collection
    {
        $map = new Collection();

        Chapter::query()
            ->with([
                'notions.courses:id,notion_id',
                'notions.subjects.exercises:id,subject_id',
                'flashcards:id,chapter_id',
                'exams:id,chapter_id',
            ])
            ->get()
            ->each(static function (Chapter $chapter) use (&$map): void {
                $chapter->notions->each(static function (Notion $notion) use (&$map, $chapter): void {
                    $notion->courses->each(static function (Course $course) use (&$map, $chapter): void {
                        $map[$course->getGlobalKey()] = $chapter;
                    });

                    $notion->subjects->each(static function (Subject $subject) use (&$map, $chapter): void {
                        $subject->exercises->each(static function (Exercise $exercise) use (&$map, $chapter): void {
                            $map[$exercise->getGlobalKey()] = $chapter;
                        });
                    });
                });

                $chapter->flashcards->each(static fn(Flashcard $flashcard) => $map[$flashcard->getGlobalKey()] =
                    $chapter);
                $chapter->exams->each(static fn(Exam $exam) => $map[$exam->getGlobalKey()] = $chapter);
            });

        return $map;
    }

    public function getChapter(BelongsToChapter $model): ?Chapter
    {
        return $this->get($model->getGlobalKey());
    }
}
