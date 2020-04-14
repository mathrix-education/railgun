<?php

declare(strict_types=1);

namespace Railgun\Collectors;

use Mathrix\Prometheus\Collectors\Collector;

class GraphQLCollector extends Collector
{
    public const GRAPHQL_OPERATIONS_TOTAL           = 'graphql_operations_total';
    public const GRAPHQL_OPERATIONS_LATENCY_SECONDS = 'graphql_operations_latency_seconds';

    public function register(): void
    {
        $this->counter(
            self::GRAPHQL_OPERATIONS_TOTAL,
            'The total number of the GraphQL operations',
            ['operation'],
        );

        $this->histogram(
            self::GRAPHQL_OPERATIONS_LATENCY_SECONDS,
            'The total number of the GraphQL operations',
            ['operation'],
        );
    }
}
