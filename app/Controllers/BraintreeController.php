<?php
/**
 * @author Mathrix Back-End Team
 * @package App\Controllers
 * @since ${CARET}
 * @copyright Mathrix SA
 */


namespace App\Controllers;


/**
 * Class BraintreeController
 *
 * @author Mathrix Back-End Team
 * @package App\Controllers
 * @since 4.1.0
 * @copyright Mathrix SA
 */
class BraintreeController extends BaseController
{
    private $braintree;


    public function __construct(\Braintree_Gateway $braintree)
    {
        parent::__construct();
        $this->braintree = $braintree;
    }
}