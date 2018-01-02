<?php
namespace App\Mails;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Class BaseMail
 *
 * @author Mathrix Back-End Team
 * @package App\Mails
 * @since 1.0.1
 * @copyright Mathrix SA
 */
abstract class BaseMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct()
    {
        $this->with([
            "api" => env("APP_API"),
            "cdn" => env("APP_CDN"),
            "webroot" => env("APP_WEBROOT")
        ]);
    }
}
