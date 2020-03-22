<?php

declare(strict_types=1);

namespace Railgun\GraphQL\Mutations;

use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;
use Railgun\Models\Interaction;
use Railgun\Models\User;

class Interact
{
    /**
     * @param null           $rootValue
     * @param mixed[]        $args
     * @param GraphQLContext $context
     *
     * @return mixed
     */
    public function __invoke($rootValue, array $args, GraphQLContext $context): bool
    {
        /** @var User $user */
        $user = $context->user();

        // Filter args
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
