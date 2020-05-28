<?php

declare(strict_types=1);

namespace Railgun\GraphQL\Directives;

use Closure;
use GraphQL\Type\Definition\ResolveInfo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;
use Nuwave\Lighthouse\Schema\Directives\BaseDirective;
use Nuwave\Lighthouse\Schema\Values\FieldValue;
use Nuwave\Lighthouse\Support\Contracts\DefinedDirective;
use Nuwave\Lighthouse\Support\Contracts\FieldMiddleware;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;
use Railgun\Exceptions\PolicyViolationException;

class PolicyDirective extends BaseDirective implements FieldMiddleware, DefinedDirective
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
        Censor the field if the user is not premium.
        """
        directive @policy(
            """
            The policy ability to check.
            """
            ability: String!
        ) on FIELD_DEFINITION | OBJECT
        SDL;
    }

    /**
     * Premium check during field resolution.
     *
     * @param FieldValue $fieldValue The current field value.
     * @param Closure    $next       The next field middleware.
     *
     * @return mixed
     */
    public function handleField(FieldValue $fieldValue, Closure $next): FieldValue
    {
        $previous = $fieldValue->getResolver();

        $resolver = function ($root, array $args, GraphQLContext $context, ResolveInfo $resolveInfo) use ($previous) {
            $ability = $this->directiveArgValue('ability', $root);

            if ($root instanceof Model && Gate::denies($ability, $root)) {
                throw new PolicyViolationException(Gate::getPolicyFor($root), $ability);
            }

            return $previous($root, $args, $context, $resolveInfo);
        };

        return $next($fieldValue->setResolver($resolver));
    }
}
