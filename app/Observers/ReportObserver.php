<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Report;

class ReportObserver extends BaseObserver
{
    public function creating(Report $report) : bool
    {
        $report->status = 'todo';

        return true;
    }
}
