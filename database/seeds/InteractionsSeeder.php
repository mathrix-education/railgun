<?php

declare(strict_types=1);

use Illuminate\Support\Collection;
use Railgun\Models\Interaction;

class InteractionsSeeder extends AdvancedSeeder
{
    private const INTERACTION_COUNT = 500;

    public function run(): void
    {
        $this->seedFactory(Interaction::class, ['seed' => false, 'count' => self::INTERACTION_COUNT])
            ->mapWithKeys(static function (array $i) {
                $key = "{$i['user_id']}-{$i['interactable_type']}-{$i['interactable_id']}";

                return [$key => $i];
            })
            ->values()
            ->tap(fn() => $this->writeFactoryMessage(Interaction::class))
            ->tap(fn(Collection $interactions) => $this->seedCollection($interactions, 'interactions'));
    }
}
