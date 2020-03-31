<?php

declare(strict_types=1);

namespace Railgun\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Railgun\Models\Traits\Content;
use Railgun\Models\Traits\Interactable;

/**
 * Delete outdated interactions.
 */
class DeleteChapterInteractions extends Job implements ShouldQueue
{
    /** @var Model|Interactable $reference */
    private $reference;

    public function __construct($reference)
    {
        $this->reference = $reference;
    }

    public function handle(): void
    {
        // Delete interactions
        $this->reference->interactions()->delete();

        // Delete parent chapter sessions
        if (!($this->reference instanceof Content)) {
            return;
        }

        $this->reference->getChapter()->sessions()->delete();
    }
}
