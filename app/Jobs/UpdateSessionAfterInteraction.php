<?php

declare(strict_types=1);

namespace Railgun\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Railgun\Models\Contracts\BelongsToChapter;
use Railgun\Models\Interaction;
use Railgun\Models\Session;
use Railgun\Models\Theme;

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

        $chapter  = $this->interaction->interactable->getChapter();
        $grade_id = $this->interaction->user->grade_id;
        /** @var Theme|null $theme */
        $theme = $chapter->themes()->where('grade_id', '=', $grade_id)->first();

        if (!$theme) {
            // User interacted with a content model which is not linked in a theme
            return;
        }

        $discipline_id = $chapter->themes()->where('grade_id', '=', $grade_id)->first()->discipline_id;

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
