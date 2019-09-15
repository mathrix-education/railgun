<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\User;
use Faker\Factory;
use Illuminate\Support\Facades\Hash;

class UserObserver extends BaseObserver
{
    public function creating(User $user): void
    {
        $user->token = Factory::create()->uuid;
    }

    public function saving(User $user): bool
    {
        // Password hash check
        if ($user->isDirty('password') && Hash::needsRehash($user->password)) {
            $user->password = Hash::make($user->password);
        }

        return true;
    }
}
