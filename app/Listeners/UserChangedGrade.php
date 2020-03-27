<?php

declare(strict_types=1);

namespace Railgun\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder;
use Railgun\Models\Session;
use Railgun\Models\User;

class UserChangedGrade implements ShouldQueue
{
    public function handle(User $user)
    {
        Session::query()
            ->where('user_id', '=', "$user->id")
            ->whereHas('chapter', static function (Builder $builder) use ($user) {
                $builder->where('grade_id', '!=', "$user->grade_id");
            })
            ->delete();
    }
}
