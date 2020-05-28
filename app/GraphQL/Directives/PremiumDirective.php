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
use Railgun\Auth\PremiumGate;
use function is_json;
use function json_decode;
use const JSON_THROW_ON_ERROR;

/**
 * Censor fields if the user is not premium.
 */
class PremiumDirective extends BaseDirective implements FieldMiddleware, DefinedDirective
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
        directive @premium(
            """
            The JSON representation of the stripped value.
            """
            stripped: String!
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
            if (
                $root instanceof Model
                && (
                    ($root->getAttribute('premium') && Gate::allows(PremiumGate::NAME))
                    || !$root->getAttribute('premium')
                )
            ) {
                return $previous($root, $args, $context, $resolveInfo);
            }

            $stripped = $this->directiveArgValue('stripped');

            return is_json($stripped)
                ? json_decode($stripped, true, 512, JSON_THROW_ON_ERROR)
                : $stripped;
        };

        return $next($fieldValue->setResolver($resolver));
    }
}
