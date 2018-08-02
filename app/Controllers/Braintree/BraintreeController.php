<?php
namespace App\Controllers\Braintree;

use Laravel\Lumen\Routing\Controller;

/**
 * Class BraintreeController
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @package App\Controllers\Braintree
 * @since 4.1.0
 * @copyright Mathrix Education SA
 */
abstract class BraintreeController extends Controller
{
    /**
     * Generate id base on a prefix
     *
     * @param string $prefix
     *
     * @return string
     * @throws \Exception
     */
    protected function generateId(string $prefix = "")
    {
        if (strlen($prefix) > 4) {
            throw new \Exception("prefix length cannot be more than 4");
        }

        return $prefix . str_replace("-", "", \Faker\Factory::create()->uuid);
    }
}
