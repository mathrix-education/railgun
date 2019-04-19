<?php
/**
 * For full configuration file, check the embedded link.
 *
 * @link https://github.com/damianopetrungaro/php-commitizen/blob/master/README.md
 */
return [
    "type" => [
        "lengthMin" => 1,
        "lengthMax" => 5,
        "acceptExtra" => false,
        "values" => ["feat", "fix", "refactor", "chore", "tests", "docs"]
    ],
    "scope" => [
        "lengthMin" => 0,
        "lengthMax" => 10,
        "acceptExtra" => true,
        "values" => ["meta", "braintree", "cloudbuild"]
    ],
    "description" => [
        "lengthMin" => 1,
        "lengthMax" => 44
    ],
    "subject" => [
        "lengthMin" => 1,
        "lengthMax" => 50
    ],
    "body" => [
        "wrap" => 72
    ],
    "footer" => [
        "wrap" => 72
    ]
];
