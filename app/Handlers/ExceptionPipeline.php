<?php

declare(strict_types=1);

namespace Railgun\Handlers;

use Illuminate\Pipeline\Pipeline;
use Throwable;

class ExceptionPipeline
{
    private Pipeline $pipeline;

    public function __construct(Pipeline $pipeline)
    {
        $this->pipeline = $pipeline->via('transform')
            ->through([
                GraphQLTransformer::class,
                RailgunTransformer::class,
                ValidationTransformer::class,
                SQLTransformer::class,
                StorageTransformer::class,
                StripeTransformer::class,
            ]);
    }

    public function transform(Throwable $throwable): Throwable
    {
        return $this->pipeline->send($throwable)->thenReturn();
    }
}
