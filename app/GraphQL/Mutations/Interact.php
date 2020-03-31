<?php

declare(strict_types=1);

namespace Railgun\GraphQL\Mutations;

use Illuminate\Support\Arr;
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

        $keys        = Arr::only($args, ['interactable_type', 'interactable_id', 'user_id']);
        $interaction = Interaction::query()->firstOrNew($keys);
        $interaction->fill($args);
        $interaction->touch();
        $interaction->save();

        return true;
    }
}
