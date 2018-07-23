<?php
/*
|--------------------------------------------------------------------------
| Mailchimp configuration file
|--------------------------------------------------------------------------
| Define which api key and list ids should be used for the application
*/
return [
    "apiKey" => env("MAILCHIMP_API_KEY"),
    "defaultListName" => "subscribers",
    "lists" => [
        "subscribers" => [
            "id" => env("MAILCHIMP_LIST_SUBSCRIBERS"),
        ],
        "parents" => [
            "id" => env("MAILCHIMP_LIST_PARENTS"),
        ],
        "profs" => [
            "id" => env("MAILCHIMP_LIST_PROFS")
        ],
        "premium" => [
            "id" => "7078e5e448"
        ]
    ],
    "ssl" => true
];
