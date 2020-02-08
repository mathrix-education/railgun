<?php

declare(strict_types=1);

namespace Railgun\GraphQL\Directives;

use Closure;
use Exception;
use GraphQL\Type\Definition\ResolveInfo;
use Illuminate\Auth\AuthManager;
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

class PermissionDirective extends BaseDirective implements ArgTransformerDirective, FieldMiddleware, DefinedDirective
{
    /** @var AuthManager */
    protected AuthManager $auth;

    /**
     * @param AuthManager $auth
     */
    public function __construct(AuthManager $auth)
    {
        $this->auth = $auth;
    }

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

    /**
     * @inheritDoc
     */
    public function name()
    {
        return 'permission';
    }

    /**
     * Check that the current user has the required scope.
     *
     * @throws Exception
     */
    private function checkScope()
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

        $guard = $this->auth->guard($this->directiveArgValue('guard'));
        /** @var User $user */
        $user = $guard->user();

        if ($user === null) {
            throw new MissingScope($scopes);
        }
        $result = array_intersect($user->scopes, $scopes);

        if (count($result) < $min) {
            throw new MissingScope($scopes);
        }
    }

    /**
     * @inheritDoc
     */
    public function transform($argumentValue)
    {
        $this->checkScope();

        return $argumentValue;
    }

    /**
     * @inheritDoc
     */
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
