<?php

declare(strict_types=1);

namespace App\Mails;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use function env;

/**
 * Base class for mailables.
 */
abstract class BaseMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct()
    {
        // Push to the email view the app, cdn and webroot urls defined in the .env file
        $this->with([
            'api'     => env('APP_URL'),
            'cdn'     => env('CDN_URL'),
            'webroot' => env('ANGULAR_URL'),
        ]);
    }

    /**
     * @return BaseMail|mixed
     */
    abstract public static function mock();
}
