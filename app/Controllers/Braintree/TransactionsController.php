<?php
namespace App\Controllers\Braintree;

use App\Exceptions\Http400InvalidRequestException;
use App\Exceptions\Http401UnauthorizedException;
use App\Exceptions\Http422ModelSaveException;
use App\Exceptions\Http422ModelValidationException;
use App\Models\Interfaces\Purchasable;
use App\Models\Purchase;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * Class TransactionsController
 *
 * @author Mathieu Bour <mathieu.tin.bour@ŋmail.com>
 * @package App\Controllers\Braintree
 * @since
 * @copyright Mathrix Education SA
 */
class TransactionsController extends BraintreeController
{

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function buy(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $user->canOrFail("buy", Purchase::class);
    }


    /**
     * Find a transaction by its id
     *
     * @param Request $request
     *
     * @param $transactionId
     *
     * @return JsonResponse
     * @throws Http401UnauthorizedException
     */
    public function find(Request $request, string $transactionId)
    {
        $user = $request->user();

        if (!$user->isSupport()) {
            throw new Http401UnauthorizedException();
        }

        return new JsonResponse(
            \Braintree_Transaction::find($transactionId)->jsonSerialize()
        );
    }


    /**
     * Refund a transaction by its id
     *
     * @param Request $request
     * @param string $transactionId
     */
    public function refund(Request $request, string $transactionId)
    {

    }
}
