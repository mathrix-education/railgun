<?php

declare(strict_types=1);

use GraphQL\Error\Debug;
use GraphQL\Validator\Rules\DisableIntrospection;
use GraphQL\Validator\Rules\QueryComplexity;
use GraphQL\Validator\Rules\QueryDepth;
use Nuwave\Lighthouse\Execution\ExtensionErrorHandler;
use Nuwave\Lighthouse\Support\Http\Middleware\AcceptJson;
use Railgun\Handlers\GraphQLHandler;
use Railgun\Middleware\GraphQLOperationMiddleware;

return [
    'route'                   => [
        'uri'        => '/',
        'name'       => 'graphql',
        'middleware' => [
            AcceptJson::class,
            GraphQLOperationMiddleware::class,
        ],
    ],
    'schema'                  => [
        'register' => base_path('graphql/schema.graphql'),
    ],
    'cache'                   => [
        'enable' => env('LIGHTHOUSE_CACHE_ENABLE', env('APP_ENV') !== 'local'),
        'key'    => env('LIGHTHOUSE_CACHE_KEY', 'lighthouse-schema'),
        'ttl'    => env('LIGHTHOUSE_CACHE_TTL', null),
    ],
    'namespaces'              => [
        'models'        => 'Railgun\\Models',
        'queries'       => 'Railgun\\GraphQL\\Queries',
        'mutations'     => 'Railgun\\GraphQL\\Mutations',
        'subscriptions' => 'Railgun\\GraphQL\\Subscriptions',
        'interfaces'    => 'Railgun\\GraphQL\\Interfaces',
        'unions'        => 'Railgun\\GraphQL\\Unions',
        'scalars'       => 'Railgun\\GraphQL\\Scalars',
        'directives'    => ['Railgun\\GraphQL\\Directives'],
    ],
    'security'                => [
        'max_query_complexity'  => QueryComplexity::DISABLED,
        'max_query_depth'       => QueryDepth::DISABLED,
        'disable_introspection' => env('APP_ENV') === 'production'
            ? DisableIntrospection::ENABLED
            : DisableIntrospection::DISABLED,
    ],
    'paginate_max_count'      => 100,
    'debug'                   => in_array(env('APP_ENV'), ['production', 'testing'], true)
        ? Debug::INCLUDE_DEBUG_MESSAGE
        : Debug::INCLUDE_DEBUG_MESSAGE | Debug::INCLUDE_TRACE,
    'error_handlers'          => [
        GraphQLHandler::class,
        ExtensionErrorHandler::class,
    ],
    'global_id_field'         => 'id',
    'batched_queries'         => true,
    'transactional_mutations' => true,
    'batchload_relations'     => true,
];
