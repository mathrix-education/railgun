<?php
switch (env('MAILGUN_CONFIG')) {
    case 'sandbox':
        return [
            'mailgun' => [
                'domain' => 'sandbox0aa19ceedf694eff93133d0650048b38.mailgun.org',
                'secret' => 'key-12289dacded2ea6ddf3642bab2b32a54'
            ]
        ];
    case 'live':
        return [
            'mailgun' => [
                'domain' => 'mathrix.fr',
                'secret' => 'key-12289dacded2ea6ddf3642bab2b32a54'
            ]
        ];
}