<?php

declare(strict_types=1);

use GraphQL\Error\Debug;
use GraphQL\Validator\Rules\QueryComplexity;
use Nuwave\Lighthouse\Execution\ExtensionErrorHandler;
use Nuwave\Lighthouse\Support\Http\Middleware\AcceptJson;

return [
    'route'                   => [
        'uri'        => '/graphql',
        'name'       => 'graphql',
        'middleware' => [
            AcceptJson::class,
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
        'max_query_depth'       => 3,
        'disable_introspection' => env('APP_ENV') !== 'master',
    ],
    'paginate_max_count'      => 100,
    'debug'                   => Debug::INCLUDE_DEBUG_MESSAGE | Debug::INCLUDE_TRACE,
    'error_handlers'          => [
        ExtensionErrorHandler::class,
    ],
    'global_id_field'         => 'id',
    'batched_queries'         => true,
    'transactional_mutations' => true,
    'batchload_relations'     => true,
];
