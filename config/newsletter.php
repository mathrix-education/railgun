<?php
return [
    'apiKey' => env('MAILCHIMP_APIKEY'),
    'defaultListName' => 'subscribers',
    'lists' => [
        'subscribers' =>
            'id' => env('MAILCHIMP_LIST_ID'),
        ],
    ],
    'ssl' => true
];