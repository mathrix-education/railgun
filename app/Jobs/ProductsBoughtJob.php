<?php

namespace App\Jobs;

use GuzzleHttp\Client;

/**
 * Class ProductsBoughtJob.
 * Send a Zapier Webhook.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.3
 */
class ProductsBoughtJob extends BaseJob
{
    private const ZAPIER_WEBHOOK_BAC = "https://hooks.zapier.com/hooks/catch/2054816/jd0gfe/";
    private const ZAPIER_WEBHOOK_BREVET = "https://hooks.zapier.com/hooks/catch/2054816/vzvqc1/";

    private $email;
    private $type;


    public function __construct(string $email, string $type = "bac")
    {
        $this->email = $email;
        $this->type = $type;
    }


    public function handle()
    {
        if (app()->environment("master")) {
            $http = new Client();

            if ($this->type === "bac") {
                $http->post(self::ZAPIER_WEBHOOK_BAC, [
                    "form_params" => [
                        "email" => $this->email
                    ]
                ]);
            } else if ($this->type === "brevet") {
                $http->post(self::ZAPIER_WEBHOOK_BREVET, [
                    "form_params" => [
                        "email" => $this->email
                    ]
                ]);
            }
        }
    }
}
