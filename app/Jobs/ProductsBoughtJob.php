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
    private const ZAPIER_WEBHOOK = "https://hooks.zapier.com/hooks/catch/2054816/jd0gfe/";
    private $email;


    public function __construct(string $email)
    {
        $this->email = $email;
    }


    public function handle()
    {
        $http = new Client();
        $http->post(self::ZAPIER_WEBHOOK, [
            "form_params" => [
                "email" => $this->email
            ]
        ]);
    }
}
