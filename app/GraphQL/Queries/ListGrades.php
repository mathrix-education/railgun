<?php

declare(strict_types=1);

namespace Railgun\GraphQL\Queries;

use Illuminate\Database\Eloquent\Collection;
use Railgun\Models\Grade;

class ListGrades
{
    /**
     * @return Collection<Grade>|Grade[]
     */
    public function __invoke(): Collection
    {
        return Grade::query()->orderBy('order')->get();
    }
}
