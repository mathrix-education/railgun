<?php

declare(strict_types=1);

namespace Railgun\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Railgun\Models\Contracts\BelongsToChapter;
use Railgun\Models\Traits\Interactable;

/**
 * Delete outdated {@see Interaction} related to chapter.
 */
class DeleteChapterInteractions extends Job implements ShouldQueue
{
    /** @var Model|Interactable $reference */
    private $reference;

    /**
     * @param Model|Interactable $reference The reference model, which should be {@see Interactable}.
     */
    public function __construct($reference)
    {
        $this->reference = $reference;
    }

    public function handle(): void
    {
        // Delete interactions
        $this->reference->interactions()->delete();

        // Delete parent chapter sessions
        if (!($this->reference instanceof BelongsToChapter)) {
            return; // @codeCoverageIgnore
        }

        $this->reference->getChapter()->sessions()->delete();
    }
}
