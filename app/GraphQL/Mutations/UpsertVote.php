<?php

declare(strict_types=1);

namespace Railgun\GraphQL\Mutations;

use Illuminate\Support\Arr;
use Railgun\Models\Vote;

/**
 * Mutation upsertVote.
 * Create or update an existing vote (using the current user id).
 */
class UpsertVote
{
    /**
     * @param null    $_    The parent value, always null in this case.
     * @param mixed[] $args The mutation arguments.
     *
     * @return mixed
     */
    public function __invoke($_, array $args): Vote
    {
        $data = Arr::only($args, ['comment_id', 'user_id']);

        /** @var Vote $vote */
        $vote        = Vote::query()->firstOrNew($data, $data);
        $vote->value = $args['value'];
        $vote->save();

        return $vote;
    }
}
