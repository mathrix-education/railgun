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
use Railgun\Exceptions\MissingScopeException;
use Railgun\Models\User;
use RuntimeException;
use function array_intersect;
use function count;
use function in_array;

/**
 * Check that the user has the required scope.
 * Applies to fields and arguments.
 */
class PermissionDirective extends BaseDirective implements ArgTransformerDirective, FieldMiddleware, DefinedDirective
{
    /**
     * SDL definition of the directive.
     */
    public static function definition(): string
    {
        return /** @lang GraphQL */ <<<'SDL'
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

    /**
     * Check that the current user has the required scope.
     * TODO: check allOf and anyOf
     *
     * @throws RuntimeException
     * @throws MissingScopeException
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
            throw new MissingScopeException($scopes);
        }

        if (in_array('*', $user->scopes)) {
            // Users with scope '*' may bypass this verification.
            return;
        }

        $result = array_intersect($user->scopes, $scopes);

        if (count($result) < $min) {
            throw new MissingScopeException($scopes);
        }
    }

    /**
     * Handle scope check when the user provides an argument.
     *
     * @param mixed $argumentValue The provided argument value.
     *
     * @return mixed
     */
    public function transform($argumentValue)
    {
        $this->checkScope();

        return $argumentValue;
    }

    /**
     * Handle scope check during field resolution.
     *
     * @param FieldValue $fieldValue The current field value.
     * @param Closure    $next       The next field middleware.
     *
     * @return mixed
     */
    public function handleField(FieldValue $fieldValue, Closure $next)
    {
        $previous = $fieldValue->getResolver();

        $resolver = function ($root, array $args, GraphQLContext $context, ResolveInfo $resolveInfo) use ($previous) {
            $this->checkScope();

            return $previous($root, $args, $context, $resolveInfo);
        };

        return $next($fieldValue->setResolver($resolver));
    }
}
