<?php

declare(strict_types=1);

namespace Tests\E2E\GraphQL\Mutations;

use Railgun\Models\Chapter;
use Railgun\Models\Grade;
use Railgun\Models\Theme;
use Tests\E2E\GraphQL\GraphQLTestCase;

class CloneChapterTest extends GraphQLTestCase
{
    /**
     * Execute the clone chapter mutation.
     *
     * @param int $chapterId The chapter id to clone.
     * @param int $themeId   The destination theme id.
     */
    public function execute(int $chapterId, int $themeId): void
    {
        $this->graphQL(
            <<<'GRAPHQL'
            mutation($chapterId: ID!, $themeId: ID!) {
                cloneChapter(input: {id: $chapterId, theme_id: $themeId}) {
                    id
                    name
                    theme {
                        id
                    }
                    notions {
                        id
                        name
                        courses {
                            id
                            name
                            content
                        }
                        subjects {
                            id
                            name
                            exercises {
                                id
                                content
                            }
                        }
                    }
                    flashcards {
                        id
                        name
                        content {
                            front
                            back
                        }
                    }
                    exams {
                        id
                        name
                        content
                    }
                }
            }  
            GRAPHQL,
            ['chapterId' => $chapterId, 'themeId' => $themeId],
        );
    }

    public function testCloneChapter(): void
    {
        /** @var Chapter $chapter */
        $chapter = Chapter::query()
            ->has('notions.courses')
            ->has('notions.subjects.exercises')
            ->has('flashcards')
            ->has('exams')
            ->with([
                'theme',
                'notions',
                'notions.courses',
                'notions.subjects',
                'notions.subjects.exercises',
                'flashcards',
                'exams',
            ])
            ->inRandomOrder()
            ->firstOrFail();

        /** @var Grade $newGrade */
        $newGrade = Grade::query()
            ->where('id', '!=', $chapter->theme->grade_id)
            ->inRandomOrder()
            ->firstOrFail();

        /** @var Theme $newTheme */
        $newTheme = Theme::query()->create([
            'name'          => "Clone of {$chapter->theme->name}",
            'visible'       => $chapter->theme->visible,
            'grade_id'      => $newGrade->id,
            'discipline_id' => $chapter->theme->discipline_id,
        ]);

        $this->withPermissions('chapters:write');
        $this->execute($chapter->id, $newTheme->id);

        $this->assertNotEquals($chapter->id, (int)$this->getData('id'));
        $this->assertEquals($chapter->name, $this->getData('name'));
        $this->assertNotEquals($chapter->theme->id, $this->getData('theme.id'));
        $this->assertEquals($newTheme->id, $this->getData('theme.id'));

        // Check the chapter content
        foreach ($chapter->notions as $n => $notion) {
            $this->assertNotEquals($notion->id, (int)$this->getData("notions.$n.id"));
            $this->assertEquals($notion->name, $this->getData("notions.$n.name"));

            foreach ($notion->courses as $c => $course) {
                $this->assertNotEquals($course->id, (int)$this->getData("notions.$n.courses.$c.id"));
                $this->assertEquals($course->name, $this->getData("notions.$n.courses.$c.name"));
                $this->assertEquals($course->content, $this->getData("notions.$n.courses.$c.content"));
            }

            foreach ($notion->subjects as $s => $subject) {
                $this->assertNotEquals($subject->id, (int)$this->getData("notions.$n.subjects.$s.id"));
                $this->assertEquals($subject->name, $this->getData("notions.$n.subjects.$s.name"));

                foreach ($subject->exercises as $e => $exercise) {
                    $this->assertNotEquals(
                        $exercise->id,
                        (int)$this->getData("notions.$n.subjects.$s.exercises.$e.id"),
                    );
                    $this->assertEquals(
                        $exercise->content,
                        $this->getData("notions.$n.subjects.$s.exercises.$e.content"),
                    );
                }
            }
        }

        foreach ($chapter->flashcards as $f => $flashcard) {
            $this->assertNotEquals($flashcard->id, (int)$this->getData("flashcards.$f.id"));
            $this->assertEquals($flashcard->name, $this->getData("flashcards.$f.name"));
            $this->assertEquals($flashcard->content, $this->getData("flashcards.$f.content"));
        }

        foreach ($chapter->exams as $e => $exam) {
            $this->assertNotEquals($exam->id, (int)$this->getData("exams.$e.id"));
            $this->assertEquals($exam->name, $this->getData("exams.$e.name"));
            $this->assertEquals($exam->content, $this->getData("exams.$e.content"));
        }
    }
}
