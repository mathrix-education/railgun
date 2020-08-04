<?php

declare(strict_types=1);

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Railgun\Models\Chapter;
use Railgun\Models\Course;
use Railgun\Models\Exam;
use Railgun\Models\Exercise;
use Railgun\Models\Flashcard;
use Railgun\Models\Notion;
use Railgun\Models\Subject;
use Railgun\Models\Theme;

/**
 * Seed the content data (countries, grades, disciplines, chapters, notions, courses, subjects, exercises, flashcards
 * and exams.
 *
 * Schema:
 * countries <=> disciplines
 * └── themes
 *     └── chapters
 *         ├── notions
 *         │   ├── courses
 *         │   └── subjects
 *         │       └── exercises
 *         ├── flashcards
 *         └── exams
 */
class ContentSeeder extends AdvancedSeeder
{
    private const THEMES_PER_TUPLE       = [1, 2];
    private const CHAPTERS_PER_THEME     = [1, 2];
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

        // Themes
        DB::table('grades')
            ->crossJoin('disciplines')
            ->selectRaw('grades.id as grade_id, disciplines.id as discipline_id')
            ->get()
            ->flatMap(function (stdClass $theme) {
                $themeCount = $this->faker->numberBetween(...self::THEMES_PER_TUPLE);

                return collect(factory(Theme::class, $themeCount)->raw((array)$theme))
                    ->map(static fn(array $theme, int $index) => array_merge($theme, ['order' => $index]));
            })
            ->tap(function (Collection $themes): void {
                $this->writeCollectionMessage('themes');
                $this->seedCollection($themes, 'themes');
            });

        // Chapters
        DB::table('themes')
            ->get(['id'])
            ->pluck('id')
            ->flatMap(function (int $themeId) {
                $chapterCount = $this->faker->numberBetween(...self::CHAPTERS_PER_THEME);

                return collect(factory(Chapter::class, $chapterCount)
                    ->raw(['theme_id' => $themeId]))
                    ->map(static fn(array $chapter, int $order) => array_merge($chapter, ['order' => $order]));
            })
            ->tap(function (Collection $chapters): void {
                $this->writeCollectionMessage('chapters');
                $this->seedCollection($chapters, 'chapters');
            });

        $chapterIds = DB::table('chapters')->get(['id'])->pluck('id');

        // Notions
        $chapterIds
            ->map(function (int $chapterId) {
                $notionCount = $this->faker->numberBetween(...self::NOTIONS_PER_CHAPTER);
                /** @var array $notions */
                $notions = factory(Notion::class, $notionCount)->raw([
                    'type'       => 'standard',
                    'chapter_id' => $chapterId,
                ]);
                // Set the first notion as the chapter summary
                $notions[0]['type'] = 'summary';

                return $notions;
            })
            ->flatten(1)
            ->tap(fn() => $this->writeFactoryMessage(Notion::class))
            ->tap(fn(Collection $data) => $this->seedCollection($data, 'notions'));

        // Courses
        DB::table('notions')
            ->get(['id'])
            ->pluck('id')
            ->map(function (int $notionId) {
                return $this->seedFactory(
                    Course::class,
                    ['seed' => false, 'count' => $this->faker->numberBetween(...self::COURSES_PER_NOTION)],
                    ['notion_id' => $notionId],
                )->map(static fn(array $course, int $order) => ['order' => $order] + $course);
            })
            ->flatten(1)
            ->tap(fn() => $this->writeFactoryMessage(Course::class))
            ->tap(fn(Collection $data) => $this->seedCollection($data, 'courses'));

        // Subjects
        DB::table('notions')
            ->get(['id'])
            ->pluck('id')
            ->map(function (int $notionId) {
                return $this->seedFactory(
                    Subject::class,
                    ['seed' => false, 'count' => $this->faker->numberBetween(...self::SUBJECTS_PER_NOTION)],
                    ['notion_id' => $notionId],
                )->map(static fn(array $subject, int $order) => ['order' => $order] + $subject);
            })
            ->flatten(1)
            ->tap(fn() => $this->writeFactoryMessage(Subject::class))
            ->tap(fn(Collection $data) => $this->seedCollection($data, 'subjects'));

        // Exercises
        DB::table('subjects')
            ->get(['id'])
            ->pluck('id')
            ->map(function (int $subjectId) {
                return $this->seedFactory(
                    Exercise::class,
                    ['seed' => false, 'count' => $this->faker->numberBetween(...self::EXERCISES_PER_SUBJECT)],
                    ['subject_id' => $subjectId],
                )->map(static fn(array $exercise, int $order) => ['order' => $order] + $exercise);
            })
            ->flatten(1)
            ->tap(fn() => $this->writeFactoryMessage(Exercise::class))
            ->tap(fn(Collection $data) => $this->seedCollection($data, 'exercises', 100));

        // Flashcards
        $chapterIds
            ->map(function (int $chapterId) {
                return $this->seedFactory(
                    Flashcard::class,
                    ['seed' => false, 'count' => $this->faker->numberBetween(...self::FLASHCARDS_PER_CHAPTER)],
                    ['chapter_id' => $chapterId],
                )->map(static fn(array $flashcard, int $order) => ['order' => $order] + $flashcard);
            })
            ->flatten(1)
            ->tap(fn() => $this->writeFactoryMessage(Flashcard::class))
            ->tap(fn(Collection $data) => $this->seedCollection($data, 'flashcards', 100));

        // Exams
        $chapterIds
            ->map(function (int $chapterId) {
                return $this->seedFactory(
                    Exam::class,
                    [
                        'seed'  => false,
                        'count' => $this->faker->numberBetween(...self::EXAMS_PER_CHAPTER),
                        'state' => 'random',
                    ],
                    ['chapter_id' => $chapterId],
                )->map(static fn(array $exam, int $order) => ['order' => $order] + $exam);
            })
            ->flatten(1)
            ->tap(fn() => $this->writeFactoryMessage(Exam::class))
            ->tap(fn(Collection $data) => $this->seedCollection($data, 'exams'));
    }
}
