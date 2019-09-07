<?php

namespace App\Mails;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Class BaseMail.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 1.0.0
 */
abstract class BaseMail extends Mailable
{
    use Queueable, SerializesModels;


    public function __construct()
    {
        // Push to the email view the app, cdn and webroot urls defined in the .env file
        $this->with([
            "api" => env("APP_URL"),
            "cdn" => env("CDN_URL"),
            "webroot" => env("ANGULAR_URL"),
        ]);
    }


    abstract public static function mock();
}
