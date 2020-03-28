<?php

declare(strict_types=1);

namespace Railgun\GraphQL\Mutations;

use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;
use Railgun\Models\Interaction;
use Railgun\Models\User;

/**
 * Mutation interact.
 * Create new interaction with the provided model (interactable_type, interactable_id) for the current user.
 */
class Interact
{
    /**
     * @param null           $rootValue The parent value, always null in this case.
     * @param mixed[]        $args      The mutation arguments.
     * @param GraphQLContext $context   The GraphQL context which holds the user.
     *
     * @return mixed
     */
    public function __invoke($rootValue, array $args, GraphQLContext $context): bool
    {
        /** @var User $user */
        $user = $context->user();

        // Filter args based on the interactable_type
        switch ($args['interactable_type']) {
            case 'subject':
                unset($args['comprehension'], $args['score']);
                break;
            case 'exam':
            case 'exercise':
                unset($args['comprehension']);
                break;
            case 'course':
            case 'flashcard':
                unset($args['score']);
                break;
        }

        $interaction          = new Interaction($args);
        $interaction->user_id = $user->id;
        $interaction->save();

        return true;
    }
}
