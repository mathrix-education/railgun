<?php

declare(strict_types=1);

namespace Railgun\GraphQL\Directives;

use Closure;
use GraphQL\Type\Definition\ResolveInfo;
use Illuminate\Support\Facades\Auth;
use Nuwave\Lighthouse\Schema\Directives\BaseDirective;
use Nuwave\Lighthouse\Schema\Values\FieldValue;
use Nuwave\Lighthouse\Support\Contracts\ArgTransformerDirective;
use Nuwave\Lighthouse\Support\Contracts\DefinedDirective;
use Nuwave\Lighthouse\Support\Contracts\FieldMiddleware;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;
use Railgun\Exceptions\MissingScope;
use Railgun\Models\User;
use RuntimeException;
use function array_intersect;
use function count;
use function in_array;

class PermissionDirective extends BaseDirective implements ArgTransformerDirective, FieldMiddleware, DefinedDirective
{
    /**
     * SDL definition of the directive.
     *
     * @return string
     */
    public static function definition(): string
    {
        return /** @lang GraphQL */ <<<SDL
        """
        Check that the current user has the required scope.
        There are three strategies: scope, anyOf and allOf.
        """
        directive @permission(
            """
            The scope to check.
            """
            scope: String
            
            """
            The scopes to check. To pass the directive, the user must have any of the provided scopes.
            """
            anyOf: [String!]
            
            """
            The scopes to check. To pass the directive, the user must have all the provided scopes.
            """
            allOf: [String!]
        ) on FIELD_DEFINITION | OBJECT
        SDL;
    }

    public function name(): string
    {
        return 'permission';
    }

    public function transform($argumentValue)
    {
        $this->checkScope();

        return $argumentValue;
    }

    /**
     * Check that the current user has the required scope.
     *
     * @throws RuntimeException
     * @throws MissingScope
     */
    private function checkScope(): void
    {
        if ($this->directiveHasArgument('scope')) {
            $scopes = [$this->directiveArgValue('scope')];
            $min    = 1;
        } elseif ($this->directiveHasArgument('allOf')) {
            $scopes = [$this->directiveArgValue('allOf')];
            $min    = count($scopes);
        } elseif ($this->directiveHasArgument('anyOf')) {
            $scopes = [$this->directiveArgValue('anyOf')];
            $min    = 1;
        } else {
            throw new RuntimeException('PermissionDirective: at least of scope, allOf or anyOf arguments is required.');
        }

        /** @var User $user */
        $user = Auth::user();

        if ($user === null) {
            throw new MissingScope($scopes);
        }

        if (in_array('*', $user->scopes)) {
            // Users with scope '*' may bypass this verification.
            return;
        }

        $result = array_intersect($user->scopes, $scopes);

        if (count($result) < $min) {
            throw new MissingScope($scopes);
        }
    }

    public function handleField(FieldValue $fieldValue, Closure $next)
    {
        $previousResolver = $fieldValue->getResolver();

        return $next(
            $fieldValue->setResolver(
                function (
                    $root,
                    array $args,
                    GraphQLContext $context,
                    ResolveInfo $resolveInfo
                ) use ($previousResolver) {
                    $this->checkScope();

                    return $previousResolver($root, $args, $context, $resolveInfo);
                }
            )
        );
    }
}
