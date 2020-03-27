<?php

declare(strict_types=1);

namespace Railgun\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Railgun\Models\Exam;
use Railgun\Models\Flashcard;
use Railgun\Models\Interaction;
use Railgun\Models\Session;

class UpdateSessions implements ShouldQueue
{
    public function handle(Interaction $interaction): void
    {
        switch ($interaction->interactable_type) {
            case 'flashcard':
                [$chapter_id,$discipline_id] = Flashcard::query()
                    ->where('id', '=', "$interaction->interactable_id")
                    ->join('chapters c', 'flashcards.chapter_id', '=', 'c.id')
                    ->get(['chapter_id', 'c.discipline_id']);
                break;
            case 'exam':
                [$chapter_id,$discipline_id] = Exam::query()
                    ->where('id', '=', "$interaction->interactable_id")
                    ->join('chapters c', 'exams.chapter_id', '=', 'c.id')
                    ->get(['chapter_id', 'c.discipline_id']);
                break;
            case 'course':
                [$chapter_id,$discipline_id] = Exam::query()
                    ->where('id', '=', "$interaction->interactable_id")
                    ->join('notions n', 'courses.notion_id', '=', 'n.id')
                    ->join('chapters c', 'n.chapter_id', '=', 'c.id')
                    ->get(['n.chapter_id', 'c.discipline_id']);
                break;
            case 'subject':
                [$chapter_id,$discipline_id] = Exam::query()
                    ->where('id', '=', "$interaction->interactable_id")
                    ->join('notions n', 'subjects.notion_id', '=', 'n.id')
                    ->join('chapters c', 'n.chapter_id', '=', 'c.id')
                    ->get(['n.chapter_id', 'c.discipline_id']);
                break;
            case 'exercise':
                [$chapter_id,$discipline_id] = Exam::query()
                    ->where('id', '=', "$interaction->interactable_id")
                    ->join('subjects', 'exercises.subject_id', '=', 'subjects.id')
                    ->join('notions n', 'subjects.notion_id', '=', 'n.id')
                    ->join('chapters c', 'n.chapter_id', '=', 'c.id')
                    ->get(['n.chapter_id', 'c.discipline_id']);
                break;
        }

        Session::query()
            ->where([
                ['user_id', '=', "$interaction->user_id"],
                ['discipline_id', '=', "$discipline_id"],
            ])
            ->update([
                'updated_at' => $interaction->updated_at,
                'chapter_id' => $chapter_id,
            ]);
    }
}
