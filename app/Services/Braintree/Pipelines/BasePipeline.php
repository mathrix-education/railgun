<?php

namespace App\Services\Braintree\Pipelines;

use App\Models\User;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator as ValidatorFacade;
use Mathrix\Lumen\Exceptions\Http\Http401UnauthorizedException;
use Mathrix\Lumen\Exceptions\Http\Http429TooManyRequestsException;
use Mathrix\Lumen\Exceptions\ValidationException;

/**
 * Class BasePipeline.
 * Describe a base pipeline while user interacts with Braintree.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0-rc1
 */
abstract class BasePipeline
{
    /** @var Request The Illuminate HTTP request. */
    protected $request;
    /** @var User $customer The customer. */
    protected $customer;
    /** @var string $paymentMethodNonce The payment method nonce. */
    protected $paymentMethodNonce;


    /**
     * BasePipeline constructor.
     *
     * @param Request $request
     *
     * @throws Http401UnauthorizedException
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
        /** @var User $user */
        $user = $this->request->user();

        if (!$user instanceof User) {
            throw new Http401UnauthorizedException();
        }

        $this->customer = $user; // By default, the user is the requester.
    }


    /**
     * Process the pipeline after validation.
     *
     * @return JsonResponse
     * @throws Http429TooManyRequestsException
     */
    public final function process(): JsonResponse
    {
        if ($this->customer->isBraintreeLocked()) {
            throw new Http429TooManyRequestsException([], "Braintree locked");
        }

        try {
            $this->customer->braintreeLock();
            $this->validatePaymentMethodNonce();
            $this->validate();
            $this->work();
            $response = $this->finalize();
            $this->customer->braintreeUnlock();

            return $response;
        } catch (Exception $e) {
            $this->customer->braintreeUnlock();
            throw $e;
        }
    }


    /**
     * Validate payment method nonce presence.
     *
     * @throws ValidationException
     */
    public final function validatePaymentMethodNonce(): void
    {
        $validator = ValidatorFacade::make($this->request->all(), [
            "payment_method_nonce" => "required"
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors()->getMessages();
            throw new ValidationException($errors, "Model data failed to pass validation.");
        }
        $this->paymentMethodNonce = $this->request->json("payment_method_nonce");
    }


    /**
     * Do the validation.
     *
     * @return void
     */
    abstract protected function validate(): void;


    /**
     * Do the pipeline work.
     *
     * @return void
     */
    abstract protected function work(): void;


    /**
     * If the pipeline succeeded, finalize the process (issue products, privileges, etc.).
     * Also return the final JsonResponse.
     *
     * @return JsonResponse
     */
    abstract protected function finalize(): JsonResponse;
}
