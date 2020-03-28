<?php

declare(strict_types=1);

namespace Railgun\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Railgun\Models\Interaction;
use Railgun\Models\Session;
use Railgun\Models\Traits\BelongsToChapter;

/**
 * After a user interacted with a {@see Interactable} model, update its session.
 */
class UpdateSessionAfterInteraction extends Job implements ShouldQueue
{
    /**
     * @link https://blog.maqe.com/solved-eloquent-doesnt-support-composite-primary-keys-62b740120f
     * @link https://stackoverflow.com/questions/56059178/how-do-i-declare-a-composite-primary-key-in-the-lumen-model
     *
     * @var array[] $wheres Workaround to handle composite keys.
     */
    private array $wheres;

    public function __construct(Interaction $interaction)
    {
        $this->wheres = [
            ['user_id', '=', $interaction->user_id],
            ['interactable_id', '=', $interaction->interactable_id],
            ['interactable_type', '=', $interaction->interactable_type],
        ];
    }

    public function handle(): void
    {
        /** @var Interaction $interaction */
        $interaction = Interaction::query()->where($this->wheres)->first();

        if (!($interaction instanceof Interaction) || !($interaction->interactable instanceof BelongsToChapter)) {
            return;
        }

        $chapter = $interaction->interactable->getChapter();

        Session::query()
            ->where([
                ['user_id', '=', $interaction->user_id],
                ['discipline_id', '=', $chapter->discipline_id],
            ])
            ->updateOrCreate([
                'user_id'       => $interaction->user_id,
                'discipline_id' => $chapter->discipline_id,
                'chapter_id'    => $chapter->id,
                'updated_at'    => $interaction->updated_at,
            ]);
    }
}
