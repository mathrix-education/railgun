<?php

declare(strict_types=1);

namespace Railgun\GraphQL\Queries;

use GraphQL\Type\Definition\ResolveInfo;
use Illuminate\Support\Facades\DB;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;
use Railgun\Models\Chapter;
use Railgun\Models\Interaction;
use Railgun\Models\User;

class ListLatestViewedChapters
{
    public function __invoke($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {
        /** @var User $user */
        $user = $context->user();
        $examsResultsQuery = Interaction::query()
            ->join('exams', 'interactions.interactable_id', '=', 'exams.id')
            ->join('chapters', 'exams.chapter_id', '=', 'chapters.id')
            ->where([
                ['user_id','=',"$user->id"],
                ['interactable_type','LIKE','exam'],
            ])
            ->select(['interactions.updated_at', 'exams.chapter_id', 'chapters.discipline_id'])
            ->getQuery();

        $flashcardsResultsQuery = Interaction::query()
            ->join('flashcards', 'interactions.interactable_id', '=', 'flashcards.id')
            ->join('chapters', 'flashcards.chapter_id', '=', 'chapters.id')
            ->where([
                ['user_id','=',"$user->id"],
                ['interactable_type','LIKE','flashcard'],
            ])
            ->select(['interactions.updated_at', 'flashcards.chapter_id', 'chapters.discipline_id'])
            ->getQuery();

        $subjectsResultsQuery = Interaction::query()
            ->join('subjects', 'interactions.interactable_id', '=', 'subjects.id')
            ->join('notions', 'subjects.notion_id', '=', 'notions.id')
            ->join('chapters', 'notions.chapter_id', '=', 'chapters.id')
            ->where([
                ['user_id','=',"$user->id"],
                ['interactable_type','LIKE','subject'],
            ])
            ->select(['interactions.updated_at', 'notions.chapter_id', 'chapters.discipline_id'])
            ->getQuery();

        $exercisesResultsQuery = Interaction::query()
            ->join('exercises', 'interactions.interactable_id', '=', 'exercises.id')
            ->join('subjects', 'exercises.subject_id', '=', 'subjects.id')
            ->join('notions', 'subjects.notion_id', '=', 'notions.id')
            ->join('chapters', 'notions.chapter_id', '=', 'chapters.id')
            ->where([
                ['user_id','=',"$user->id"],
                ['interactable_type','LIKE','exercise'],
            ])
            ->select(['interactions.updated_at', 'notions.chapter_id', 'chapters.discipline_id'])
            ->getQuery();

        $results = DB::table('interactions')
            ->join('courses', 'interactions.interactable_id', '=', 'courses.id')
            ->join('notions', 'courses.notion_id', '=', 'notions.id')
            ->join('chapters', 'notions.chapter_id', '=', 'chapters.id')
            ->where([
                ['user_id','=',"$user->id"],
                ['interactable_type','LIKE','course'],
            ])
            ->select(['interactions.updated_at', 'notions.chapter_id', 'chapters.discipline_id'])
            ->union($examsResultsQuery)
            ->union($flashcardsResultsQuery)
            ->union($subjectsResultsQuery)
            ->union($exercisesResultsQuery)
            ->orderBy('updated_at')
            ->get()
            ->mapWithKeys(fn($r) => [
                $r->discipline_id => $r->chapter_id,
            ]);

        return Chapter::query()->whereIn('id', $results->values())->get();
    }
}
