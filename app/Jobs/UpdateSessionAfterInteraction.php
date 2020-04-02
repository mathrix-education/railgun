<?php

declare(strict_types=1);

namespace Railgun\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Railgun\Models\Interaction;
use Railgun\Models\Session;
use Railgun\Models\Traits\Content;

/**
 * After a user interacted with a {@see Interactable} model, update its session.
 */
class UpdateSessionAfterInteraction extends Job implements ShouldQueue
{
    private Interaction $interaction;

    public function __construct(Interaction $interaction)
    {
        $this->interaction = $interaction;
    }

    public function handle(): void
    {
        if (!($this->interaction instanceof Interaction) || !($this->interaction->interactable instanceof Content)) {
            // This should never happen
            return;
        }

        $chapter = $this->interaction->interactable->getChapter();

        Session::query()
            ->updateOrCreate(
                [
                    'user_id'       => $this->interaction->user_id,
                    'discipline_id' => $chapter->discipline_id,
                ],
                [
                    'chapter_id' => $chapter->id,
                    'updated_at' => $this->interaction->updated_at,
                ],
            );
    }
}
