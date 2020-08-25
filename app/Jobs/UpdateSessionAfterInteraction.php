<?php

declare(strict_types=1);

namespace Railgun\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\DB;
use Railgun\Models\Contracts\BelongsToChapter;
use Railgun\Models\Interaction;
use Railgun\Models\Session;
use Railgun\Models\User;

/**
 * After a user interacted with a {@see Interactable} model, update its session.
 */
class UpdateSessionAfterInteraction extends Job implements ShouldQueue
{
    public Interaction $interaction;

    public function __construct(Interaction $interaction)
    {
        $this->interaction = $interaction;
    }

    public function handle(): void
    {
        if (
            !($this->interaction instanceof Interaction)
            || !($this->interaction->interactable instanceof BelongsToChapter)
        ) {
            // This should never happen
            return; // @codeCoverageIgnore
        }

        $chapter = $this->interaction->interactable->getChapter();

        $grade_id = User::query()
            ->where('id', '=', $this->interaction->user_id)
            ->get('grade_id')
            ->first;

        $discipline_id = DB::table('themes')
            ->join('grades', static function($join) use ($grade_id): void {
                $join->on('grades.id', '=', 'themes.grade_id');
                $join->on('grades.id', '=', $grade_id);
            })
            ->join('chapter_theme', static function($join) use ($chapter): void {
                $join->on('chapter_theme.chapter_id', '=', $chapter->id);
                $join->on('chapter_theme.theme_id', '=', 'themes.id');
            })
            ->select('discipline_id')
            ->get();

        Session::query()
            ->updateOrCreate(
                [
                    'user_id'       => $this->interaction->user_id,
                    'discipline_id' => $discipline_id,
                ],
                [
                    'chapter_id' => $chapter->id,
                    'updated_at' => $this->interaction->updated_at,
                ],
            );
    }
}
