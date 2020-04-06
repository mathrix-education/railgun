<?php

declare(strict_types=1);

namespace Railgun\Collectors;

use Illuminate\Support\Facades\DB;
use Mathrix\Prometheus\Collectors\Collector;
use stdClass;

/**
 * Collect the users statistics.
 */
class UserCollector extends Collector
{
    /**
     * Register all metrics associated with the collector.
     */
    public function register(): void
    {
        $this->gauge('users_registered_total', 'The total of registered users', ['grade']);
    }

    /**
     * Compute the users grade.
     */
    public function compute(): void
    {
        DB::table('grades')
            ->join('users', 'grades.id', '=', 'users.grade_id')
            ->groupBy('users.grade_id')
            ->selectRaw('`grades`.`name`, count(`users`.`grade_id`) as `count`')
            ->get()
            ->each(function (stdClass $line): void {
                $this->gauge('users_registered_total')->set($line->count, [$line->name]);
            });

    }
}
