<?php

declare(strict_types=1);

namespace Railgun\GraphQL\Mutations;

use Illuminate\Support\Arr;
use Railgun\Models\Interaction;

/**
 * Mutation interact.
 * Create new interaction with the provided model (interactable_type, interactable_id) for the current user.
 */
class Interact
{
    /**
     * @param null    $_    The parent value, always null in this case.
     * @param mixed[] $args The mutation arguments.
     *
     * @return mixed
     */
    public function __invoke($_, array $args): bool
    {
        // Filter args
        switch ($args['interactable_type']) {
            case 'subject':
                unset($args['comprehension'], $args['score'], $args['total'], $args['watchtime']);
                break;
            case 'course':
                unset($args['score'], $args['total']);
                break;
            case 'flashcard':
                unset($args['score'], $args['total'], $args['watchtime']);
                break;
            case 'exercise':
                unset($args['comprehension'], $args['watchtime']);
                break;
            case 'exam':
                unset($args['comprehension']);
                break;
        }

        $keys        = Arr::only($args, ['interactable_type', 'interactable_id', 'user_id']);
        $interaction = Interaction::query()->firstOrNew($keys);
        $interaction->fill($args);
        $interaction->touch();
        $interaction->save();

        return true;
    }
}
