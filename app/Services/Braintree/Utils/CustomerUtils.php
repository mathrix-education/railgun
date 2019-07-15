<?php


namespace App\Services\Braintree\Utils;


class CustomerUtils
{
    public static function generateId(): string
    {
        return "cus_" . str_replace("-", "", uuid());
    }
}
