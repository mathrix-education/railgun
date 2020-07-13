<?php

declare(strict_types=1);

namespace Railgun\GraphQL\Mutations;

use Exception;
use Railgun\Models\Vote;

/**
 * Mutation deleteVote.
 * Delete a vote from the database (using the current user id).
 */
class DeleteVote
{
    /**
     * @param null    $_    The parent value, always null in this case.
     * @param mixed[] $args The mutation arguments.
     *
     * @return Vote The deleted vote model.
     *
     * @throws Exception
     */
    public function __invoke($_, array $args): Vote
    {
        /** @var Vote $vote */
        $vote = Vote::query()->where($args)->firstOrFail();
        $vote->delete();

        return $vote;
    }
}
