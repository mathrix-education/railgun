<?php
$config = [];
switch (env('MAILGUN_CONFIG')) {
    case 'sandbox':
        $config['mailgun'] = [
            'domain' => 'sandbox0aa19ceedf694eff93133d0650048b38.mailgun.org',
            'secret' => 'key-12289dacded2ea6ddf3642bab2b32a54'
        ];
        break;
    case 'live':
        $config['mailgun'] = [
            'domain' => 'mathrix.fr',
            'secret' => 'key-12289dacded2ea6ddf3642bab2b32a54'
        ];
        break;
}

switch (env('STRIPE_CONFIG')) {
    case 'sandbox':
        $config['stripe'] = [
            'model' => App\Models\User::class,
            'key' => 'pk_test_0AdjnzefBxZtL0SZfCzvD1rm',
            'secret' => 'sk_test_DTEvwkDwwov7FbDRFbEqAPaI',
        ];
        break;
    case 'live':
        $config['stripe'] = [
            'model' => App\Models\User::class,
            'key' => env('STRIPE_KEY'),
            'secret' => env('STRIPE_SECRET'),
        ];
}

return $config;