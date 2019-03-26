<?php

namespace App\Registrars;

use Mathrix\Lumen\Bases\BaseRegistrar;

/**
 * Class MailsRegistrar.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0
 *
 * @codeCoverageIgnore
 */
class MailsRegistrar extends BaseRegistrar
{
    /**
     * Register the routes.
     */
    public function register(): void
    {
        if (app()->environment("local")) {
            $this->group(["prefix" => "mails"], function () {
                $mailsDir = app()->basePath() . "/app/Mails";
                $mails = glob("$mailsDir/*Mail.php");
                $mails = array_filter($mails, function ($file) {
                    return !\Illuminate\Support\Str::contains($file, "BaseMail");
                });

                // Build Mail classes
                $mailClasses = array_map(function ($file) use ($mailsDir) {
                    return "App\\Mails\\" . str_replace(["$mailsDir/", ".php"], "", $file);
                }, $mails);

                // Build Mail urls
                $mailsUrl = array_map(function ($file) use ($mailClasses, $mailsDir) {
                    $mailClasses[] = "App\\Mails\\" . str_replace(["$mailsDir/", ".php"], "", $file);

                    return \Illuminate\Support\Str::snake(str_replace(["$mailsDir/", "Mail.php"], "", $file), "-");
                }, $mails);

                $mails = array_combine($mailsUrl, $mailClasses);

                /**
                 * @var string
                 * @var \App\Mails\BaseMail $class
                 *
                 * // TODO: improve this by getting arguments?
                 */
                foreach ($mails as $url => $class) {
                    $this->get($url, function () use ($class) {
                        return $class::mock();
                    });
                }
            });

            $this->get("mails/transaction-success/{transactionId}", function ($transactionId) {
                return \App\Mails\TransactionSuccessMail::mock($transactionId);
            });
        }
    }
}
