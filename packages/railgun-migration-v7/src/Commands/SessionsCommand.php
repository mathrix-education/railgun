<?php

declare(strict_types=1);

namespace Railgun\MigrationV7\Commands;

use function implode;

class SessionsCommand extends V7Command
{
    protected $signature = 'v7:sessions';

    public function handle(): void
    {
        $this->title('Extracting sessions from interactions');

        $courses = <<<MYSQL
        select discipline_id, chapter_id, i.updated_at
        from interactions i
            inner join courses cs on i.interactable_id = cs.id and i.interactable_type = 'course'
            inner join notions n on cs.notion_id = n.id
            inner join chapters c on n.chapter_id = c.id
        MYSQL;

        $subjects = <<<MYSQL
        select discipline_id, chapter_id, i.updated_at
        from interactions i
            inner join subjects s on i.interactable_id = s.id and i.interactable_type = 'subject'
            inner join notions n on s.notion_id = n.id
            inner join chapters c on n.chapter_id = c.id
        MYSQL;

        $exercises = <<<MYSQL
        select discipline_id, chapter_id, i.updated_at
        from interactions i
            inner join exercises e on i.interactable_id = e.id and i.interactable_type = 'exercise'
            inner join subjects s on e.subject_id = s.id
            inner join notions n on s.notion_id = n.id
            inner join chapters c on n.chapter_id = c.id
        MYSQL;

        $flashcards = <<<MYSQL
        select discipline_id, chapter_id, i.updated_at
        from interactions i
            inner join flashcards f on i.interactable_id = f.id and i.interactable_type = 'flashcard'
            inner join chapters c on f.chapter_id = c.id
        MYSQL;

        $exams = <<<MYSQL
        select discipline_id, chapter_id, i.updated_at
        from interactions i
            inner join exams e on i.interactable_id = e.id and i.interactable_type = 'exam'
            inner join chapters c on e.chapter_id = c.id
        MYSQL;

        $union = implode("\nunion\n", [
            $courses,
            $subjects,
            $exercises,
            $flashcards,
            $exams,
        ]);

        $final = <<<MYSQL
        select set1.*
        from ($union) set1
            left join ($union) set2
                on set1.discipline_id = set2.discipline_id
                and set1.updated_at < set2.updated_at
        where set2.discipline_id is null
        order by discipline_id
        MYSQL;

        $this->line($final);
    }
}
