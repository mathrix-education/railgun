<?php
namespace App\Controllers\Braintree;

use App\Exceptions\Http400InvalidRequestException;
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
    /** @var array The Mathrix Education SA descriptor for payments */
    protected const BRAINTREE_DESCRIPTOR = [
        "name" => "mathrix.fr",
        "phone" => "0970444668"
    ];


    /**
     * Generate id base on a prefix
     *
     * @param string $prefix
     *
     * @return string
     * @throws Http400InvalidRequestException
     */
    protected function generateId(string $prefix = "")
    {
        if (strlen($prefix) > 4) {
            throw new Http400InvalidRequestException("prefix length cannot be more than 4");
        }

        return $prefix . str_replace("-", "", \Faker\Factory::create()->uuid);
    }
}
