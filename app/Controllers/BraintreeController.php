<?php
namespace App\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller;

/**
 * Class BraintreeController
 *
 * @author Mathieu Bour <mathieu.tin.bour@ŋmail.com>
 * @package App\Controllers
 * @since
 * @copyright Mathrix Education SA
 */
class BraintreeController extends Controller
{
    /**
     * Generate the Braintree Client Token
     *
     * @param Request $request
     *
     * @return string
     */
    public function clientToken(Request $request)
    {
        /** @var User $user */
        $user = $request->user();
        $payload = [];

        if ($user->isBraintreeCustomer()) {
            $payload["customerId"] = $user->braintree_id;
        }

        $token = \Braintree_ClientToken::generate($payload);

        return new JsonResponse([
            "token" => $token
        ]);
    }
}
