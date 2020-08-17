<?php

declare(strict_types=1);

namespace Railgun\GraphQL\Directives;

use Closure;
use GraphQL\Type\Definition\ResolveInfo;
use Illuminate\Support\Facades\Gate;
use Nuwave\Lighthouse\Schema\Directives\BaseDirective;
use Nuwave\Lighthouse\Schema\Values\FieldValue;
use Nuwave\Lighthouse\Support\Contracts\DefinedDirective;
use Nuwave\Lighthouse\Support\Contracts\FieldMiddleware;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;
use Railgun\Auth\OwnershipGate;
use Railgun\Auth\PremiumGate;
use Railgun\Models\BaseModel;
use Railgun\Models\Contracts\Ownable;
use Railgun\Models\Lesson;
use function is_json;
use function json_decode;
use const JSON_THROW_ON_ERROR;

class CensorDirective extends BaseDirective implements FieldMiddleware, DefinedDirective
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
        Censor the field if the user does not own the product.
        """
        directive @censor(
            """
            The JSON representation of the censored value.
            """
            value: String!
        ) on FIELD_DEFINITION | OBJECT
        SDL;
    }

    /**
     * @param FieldValue $fieldValue The current field value.
     * @param Closure    $next       The next field middleware.
     *
     * @return mixed
     */
    public function handleField(FieldValue $fieldValue, Closure $next): FieldValue
    {
        $previous = $fieldValue->getResolver();

        $resolver = function ($root, array $args, GraphQLContext $context, ResolveInfo $resolveInfo) use ($previous) {
            $raw      = $this->directiveArgValue('value');
            $censored = is_json($raw)
                ? json_decode($raw, true, 512, JSON_THROW_ON_ERROR)
                : $raw;

            if (!$root instanceof BaseModel) {
                // This should never be the case
                return $previous($root, $args, $context, $resolveInfo); // @codeCoverageIgnore
            }

            // $root is now a model

            if ($root->getAttribute('premium') && Gate::denies(PremiumGate::NAME)) {
                // pPremium censor triggered
                return $censored;
            }

            if (
                (
                    ($root instanceof Ownable && $root->getAttribute('protected'))
                    || $root instanceof Lesson
                )
                && Gate::denies(OwnershipGate::NAME, $root)
            ) {
                // Ownership censor triggered
                return $censored;
            }

            return $previous($root, $args, $context, $resolveInfo);
        };

        return $next($fieldValue->setResolver($resolver));
    }
}
