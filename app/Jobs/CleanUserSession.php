<?php

declare(strict_types=1);

namespace Railgun\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder;
use Railgun\Models\Session;
use Railgun\Models\User;

/**
 * Clean the user session by removing the {@see Session} which related chapter belongs to another grade.
 */
class CleanUserSession extends Job implements ShouldQueue
{
    private User $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function handle(): void
    {
        Session::query()
            ->where('user_id', '=', $this->user->id)
            ->whereHas('chapter', function (Builder $builder): void {
                $builder->where('grade_id', '!=', $this->user->grade_id);
            })
            ->delete();
    }
}
