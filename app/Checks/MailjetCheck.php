<?php

declare(strict_types=1);

namespace Railgun\Checks;

use Mailjet\Client;
use Mailjet\Config;
use Mailjet\Resources;
use Mathrix\Healthz\Checks\Check;

class MailjetCheck extends Check
{
    private Client $mailjet;

    public function __construct(Client $mailjet, array $options = [])
    {
        parent::__construct($options);

        $this->mailjet = $mailjet;
    }

    /**
     * @return mixed[] The check details.
     */
    public function execute(): array
    {
        $response = $this->mailjet->get(
            Resources::$Contactstatistics,
            [],
            [
                'call'    => true,
                'version' => Config::MAIN_VERSION,
            ],
        );

        return $response->getData();
    }
}
