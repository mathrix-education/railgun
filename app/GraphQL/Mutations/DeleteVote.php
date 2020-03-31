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
     * @param null    $rootValue The parent value, always null in this case.
     * @param mixed[] $args      The mutation arguments.
     *
     * @return mixed
     *
     * @throws Exception
     */
    public function __invoke($rootValue, array $args): Vote
    {
        /** @var Vote $vote */
        $vote = Vote::query()->where($args)->first();
        Vote::query()->where($args)->delete(); // TODO: change that

        return $vote;
    }
}
