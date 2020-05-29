<?php

declare(strict_types=1);

namespace Railgun\GraphQL\Directives;

use Closure;
use GraphQL\Type\Definition\ResolveInfo;
use Illuminate\Support\Facades\Gate;
use Nuwave\Lighthouse\Schema\Directives\BaseDirective;
use Nuwave\Lighthouse\Schema\Values\FieldValue;
use Nuwave\Lighthouse\Support\Contracts\ArgTransformerDirective;
use Nuwave\Lighthouse\Support\Contracts\DefinedDirective;
use Nuwave\Lighthouse\Support\Contracts\FieldMiddleware;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;
use Railgun\Exceptions\MissingPermissionException;
use RuntimeException;

/**
 * Check that the user has the required permission.
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
        Check that the current user has the required permission.
        """
        directive @permission(
            """
            The permission to check.
            """
            name: String!
        ) on FIELD_DEFINITION | OBJECT
        SDL;
    }

    /**
     * Check that the current user has the required permission.
     *
     * @throws RuntimeException
     * @throws MissingPermissionException
     */
    private function checkPermission(): void
    {
        $permission = $this->directiveArgValue('name');

        if (Gate::denies('permission', [$permission])) {
            throw new MissingPermissionException([$permission]);
        }
    }

    /**
     * Handle permission check when the user provides an argument.
     *
     * @param mixed $argumentValue The provided argument value.
     *
     * @return mixed
     */
    public function transform($argumentValue)
    {
        $this->checkPermission();

        return $argumentValue;
    }

    /**
     * Handle permission check during field resolution.
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
            $this->checkPermission();

            return $previous($root, $args, $context, $resolveInfo);
        };

        return $next($fieldValue->setResolver($resolver));
    }
}
