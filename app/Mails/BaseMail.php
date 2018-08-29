<?php

namespace App\Mails;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Class BaseMail
 * Base class for all mails.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 *
 * @since     1.0.1
 *
 * @copyright Mathrix Education SA
 */
abstract class BaseMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct()
    {
        // Push to the email view the app, cdn and webroot urls defined in the .env file
        $this->with([
            "api" => env("APP_API"),
            "cdn" => env("APP_CDN"),
            "webroot" => env("APP_WEBROOT"),
        ]);
    }

    abstract public static function mock();
}
