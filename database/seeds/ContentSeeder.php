<?php

declare(strict_types=1);

use App\Models\Chapter;
use App\Models\Course;
use App\Models\Exam;
use App\Models\Exercise;
use App\Models\Flashcard;
use App\Models\Notion;
use App\Models\Subject;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Seed the content data (countries, grades, disciplines, chapters, notions, courses, subjects, exercises, flashcards
 * and exams.
 *
 * Schema:
 * countries <=> disciplines
 * └── chapters
 *     ├── notions
 *     │   ├── courses
 *     │   └── subjects
 *     │       └── exercises
 *     ├── flashcards
 *     └── exams
 */
class ContentSeeder extends AdvancedSeeder
{
    private const CHAPTER_COUNT          = 30;
    private const NOTIONS_PER_CHAPTER    = [1, 2];
    private const COURSES_PER_NOTION     = [1, 2];
    private const SUBJECTS_PER_NOTION    = [1, 2];
    private const EXERCISES_PER_SUBJECT  = [1, 2];
    private const FLASHCARDS_PER_CHAPTER = [1, 2];
    private const EXAMS_PER_CHAPTER      = [1, 2];

    public function run(): void
    {
        // Countries
        $this->seedFile('countries.json');

        // Grades
        $this->seedFile('grades.json');

        // Disciplines
        $this->seedFile('disciplines.json');

        // Chapters
        $this->seedFactory(Chapter::class, self::CHAPTER_COUNT);

        $chapterIds = DB::table('chapters')->get(['id'])->pluck('id');

        // Notions
        $chapterIds
            ->map(function (int $chapterId) {
                return array_merge(
                    factory(Notion::class, $this->faker->numberBetween(...self::NOTIONS_PER_CHAPTER))->raw([
                        'type'       => 'course',
                        'chapter_id' => $chapterId,
                    ]),
                    factory(Notion::class, $this->faker->numberBetween(...self::NOTIONS_PER_CHAPTER))->raw([
                        'type'       => 'subject',
                        'chapter_id' => $chapterId,
                    ])
                );
            })
            ->flatten(1)
            ->tap(fn() => $this->writeFactoryMessage(Notion::class))
            ->tap(fn(Collection $data) => $this->seedCollection($data, 'notions'));

        // Courses
        DB::table('notions')
            ->where('type', '=', 'course')
            ->get(['id'])
            ->pluck('id')
            ->map(function (int $notionId) {
                return $this->seedFactory(
                    Course::class,
                    ['seed' => false, 'count' => $this->faker->numberBetween(...self::COURSES_PER_NOTION)],
                    ['notion_id' => $notionId]
                )->map(fn(array $course, int $order) => ['order' => $order] + $course);
            })
            ->flatten(1)
            ->tap(fn() => $this->writeFactoryMessage(Course::class))
            ->tap(fn(Collection $data) => $this->seedCollection($data, 'courses'));

        // Subjects
        DB::table('notions')
            ->where('type', '=', 'subject')
            ->get(['id'])
            ->pluck('id')
            ->map(function (int $notionId) {
                return $this->seedFactory(
                    Subject::class,
                    ['seed' => false, 'count' => $this->faker->numberBetween(...self::SUBJECTS_PER_NOTION)],
                    ['notion_id' => $notionId]
                )->map(fn(array $subject, int $order) => ['order' => $order] + $subject);
            })
            ->flatten(1)
            ->tap(fn() => $this->writeFactoryMessage(Subject::class))
            ->tap(fn(Collection $data) => $this->seedCollection($data, 'subjects'));

        // Exercises
        Subject::enableRandomizationCache();
        DB::table('subjects')
            ->get(['id'])
            ->pluck('id')
            ->map(function (int $subjectId) {
                return $this->seedFactory(
                    Exercise::class,
                    ['seed' => false, 'count' => $this->faker->numberBetween(...self::EXERCISES_PER_SUBJECT)],
                    ['subject_id' => $subjectId]
                )->map(fn(array $exercise, int $order) => ['order' => $order] + $exercise);
            })
            ->flatten(1)
            ->tap(fn() => $this->writeFactoryMessage(Exercise::class))
            ->tap(fn(Collection $data) => $this->seedCollection($data, 'exercises'));
        Subject::enableRandomizationCache();

        // Flashcards
        $chapterIds
            ->map(function (int $chapterId) {
                return $this->seedFactory(
                    Flashcard::class,
                    ['seed' => false, 'count' => $this->faker->numberBetween(...self::FLASHCARDS_PER_CHAPTER)],
                    ['chapter_id' => $chapterId]
                )->map(fn(array $flashcard, int $order) => ['order' => $order] + $flashcard);
            })
            ->flatten(1)
            ->tap(fn() => $this->writeFactoryMessage(Flashcard::class))
            ->tap(fn(Collection $data) => $this->seedCollection($data, 'flashcards'));

        // Exams
        $chapterIds
            ->map(function (int $chapterId) {
                return $this->seedFactory(
                    Exam::class,
                    ['seed' => false, 'count' => $this->faker->numberBetween(...self::EXAMS_PER_CHAPTER)],
                    ['chapter_id' => $chapterId]
                )->map(fn(array $exam, int $order) => ['order' => $order] + $exam);
            })
            ->flatten(1)
            ->tap(fn() => $this->writeFactoryMessage(Exam::class))
            ->tap(fn(Collection $data) => $this->seedCollection($data, 'exams'));
    }
}
