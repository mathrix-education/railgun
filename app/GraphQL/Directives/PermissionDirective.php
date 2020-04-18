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
use function in_array;

/**
 * Check that the user has the required scope.
 * Applies to fields and arguments.
 */
class PermissionDirective extends BaseDirective implements ArgTransformerDirective, FieldMiddleware, DefinedDirective
{
    /**
     * SDL definition of the directive.
     *
     * @codeCoverageIgnore
     */
    public static function definition(): string
    {
        return /** @lang GraphQL */ <<<'SDL'
        """
        Check that the current user has the required scope.
        """
        directive @permission(
            """
            The scope to check.
            """
            scope: String!
        ) on FIELD_DEFINITION | OBJECT
        SDL;
    }

    /**
     * @return string The directive name.
     *
     * @codeCoverageIgnore
     */
    public function name(): string
    {
        return 'permission';
    }

    /**
     * Check that the current user has the required scope.
     *
     * @throws RuntimeException
     * @throws MissingScopeException
     */
    private function checkScope(): void
    {
        $scope = $this->directiveArgValue('scope');

        /** @var User $user */
        $user = Auth::user();

        if ($user === null) {
            throw new MissingScopeException([$scope]);
        }

        if (
            !in_array('*', $user->scopes, true) // Users with scope '*' may bypass this verification.
            && !in_array($scope, $user->scopes, true)
        ) {
            throw new MissingScopeException([$scope]);
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
