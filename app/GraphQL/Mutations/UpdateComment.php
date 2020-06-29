<?php

declare(strict_types=1);

namespace Railgun\GraphQL\Mutations;

use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;
use Railgun\Auth\PermissionGate;
use Railgun\Exceptions\MissingPermissionException;
use Railgun\Models\Comment;
use Railgun\Models\User;

class UpdateComment
{
    /**
     * @param null           $rootValue The parent value, always null in this case.
     * @param mixed[]        $args      The mutation arguments (input: CommentInput).
     * @param GraphQLContext $context   The GraphQL context which holds the user.
     *
     * @return Comment The updated comment.
     */
    public function __invoke($rootValue, array $args, GraphQLContext $context): Comment
    {
        /** @var User $user */
        $user = $context->user();
        /** @var Comment $comment */
        $comment = Comment::query()->findOrFail($args['id']);

        if ($comment->user_id !== $user->id && $user->cant(PermissionGate::NAME, 'comments:write')) {
            throw new MissingPermissionException(['comments:write']);
        }

        $comment->update($args);

        return $comment;
    }
}
