<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Tinify\Tinify;

/**
 * Class TinifyServiceProvider.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 *
 * @since     4.1.0
 *
 * @copyright Mathrix Education SA
 */
class TinifyServiceProvider extends ServiceProvider
{
    /**
     * @throws \Tinify\AccountException
     */
    public function boot()
    {
        Tinify::setKey(env("TINIFY_API_KEY"));
        \Tinify\validate();
    }
}
