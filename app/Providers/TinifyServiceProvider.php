<?php
namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Tinify\Tinify;

/**
 * Class TinifyServiceProvider.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @author    Jérémie Levain <munezero999@live.fr>
 * @since     4.1.0
 * @copyright Mathrix Education SA
 * @package   App\Providers
 */
class TinifyServiceProvider extends ServiceProvider
{
    public function boot()
    {
        Tinify::setKey(env("TINIFY_API_KEY"));
    }
}
