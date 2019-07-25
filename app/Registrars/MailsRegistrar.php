<?php

namespace App\Registrars;

use App\Mails\SubscriptionRenewalSoonMail;
use App\Mails\TransactionSuccessMail;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Mathrix\Lumen\Zero\Registrars\BaseRegistrar;

/**
 * Class MailsRegistrar.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0-rc1
 *
 * @codeCoverageIgnore
 */
class MailsRegistrar extends BaseRegistrar
{
    /**
     * Make mails routes and classes. The returned collection has uri as keys and Mailable class as values.
     *
     * @return Collection
     */
    public static function getMails(): Collection
    {
        $mailsDirectory = app()->basePath("app/Mails");

        return Collection::make(glob("$mailsDirectory/*Mail.php"))
            ->filter(function (string $mailFile) {
                return !Str::contains($mailFile, "BaseMail"); // Exclude BaseMail
            })
            ->map(function (string $mailFile) use ($mailsDirectory) {
                return str_replace(["$mailsDirectory/", ".php"], "", $mailFile);
            })
            ->mapWithKeys(function ($mailName) {
                $uri = "mails/" . Str::replaceLast("-mail", "", Str::snake($mailName, "-"));
                $class = "App\\Mails\\$mailName";

                return [$uri => $class];
            });
    }


    /**
     * Register the routes.
     */
    public function register(): void
    {
        if (app()->environment("local", "dev", "testing")) {
            $this->get("mails", function () {
                return new JsonResponse(self::getMails());
            });

//            self::getMails()->map(function ($mailableClass, string $uri) {
//                /** @var BaseMail|string $mailableClass */
//                $this->get($uri, function () use ($mailableClass) {
//                    return $mailableClass::mock();
//                });
//            });

            $this->get("mails/subscriptions/subscription_renewal_soon", function () {
                return SubscriptionRenewalSoonMail::mock();
            });

            // Custom mails routes
            $this->get("mails/transaction-success/{transactionId}", function ($transactionId) {
                return TransactionSuccessMail::mock($transactionId);
            });
        }
    }
}
