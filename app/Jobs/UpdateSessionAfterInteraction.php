<?php

declare(strict_types=1);

namespace Railgun\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Railgun\Contracts\BelongsToChapter;
use Railgun\Models\Interaction;
use Railgun\Models\Session;

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
        if (!($this->interaction instanceof Interaction) || !($this->interaction->interactable instanceof BelongsToChapter)) {
            // This should never happen
            return; // @codeCoverageIgnore
        }

        $chapter = $this->interaction->interactable->getChapter();

        Session::query()
            ->updateOrCreate(
                [
                    'user_id'       => $this->interaction->user_id,
                    'discipline_id' => $chapter->theme->discipline_id,
                ],
                [
                    'chapter_id' => $chapter->id,
                    'updated_at' => $this->interaction->updated_at,
                ],
            );
    }
}
