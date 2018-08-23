<?php

/** @var Laravel\Lumen\Routing\Router $router */

use App\Mails\BaseMail;
use Illuminate\Support\Str;

// @codeCoverageIgnoreStart
if (app()->environment("dev", "local")) {
    $router->group(["prefix" => "mails"], function () use ($router) {
        $mailsDir = app()->basePath() . "/app/Mails";
        $mails = glob("$mailsDir/*Mail.php");
        $mails = array_filter($mails, function ($file) {
            return !Str::contains($file, "BaseMail");
        });

        // Build Mail classes
        $mailClasses = array_map(function ($file) use ($mailsDir) {
            return "App\\Mails\\" . str_replace(["$mailsDir/", ".php"], "", $file);
        }, $mails);

        // Build Mail urls
        $mailsUrl = array_map(function ($file) use ($mailClasses, $mailsDir) {
            $mailClasses[] = "App\\Mails\\" . str_replace(["$mailsDir/", ".php"], "", $file);

            return Str::snake(str_replace(["$mailsDir/", "Mail.php"], "", $file), "-");
        }, $mails);

        $mails = array_combine($mailsUrl, $mailClasses);

        /**
         * @var string
         * @var BaseMail $class
         */
        foreach ($mails as $url => $class) {
            $router->get($url, function () use ($class) {
                return $class::mock();
            });
        }
    });

    $router->get("mails/transaction-success/{transactionId}", function ($transactionId) {
        return \App\Mails\TransactionSuccessMail::mock($transactionId);
    });
}
// @codeCoverageIgnoreEnd
