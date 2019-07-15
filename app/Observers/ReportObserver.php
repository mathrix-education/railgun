<?php

namespace App\Observers;

use App\Models\Report;

/**
 * Class ReportObserver.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0-rc1
 */
class ReportObserver extends BaseObserver
{
    /**
     * @param Report $report
     *
     * @return bool
     */
    public function creating(Report $report)
    {
        $report->status = "todo";
        return true;
    }
}
