<?php

namespace App\Services\Braintree\Pipelines;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\MessageBag;
use Mathrix\Lumen\Exceptions\Http\Http500InternalServerErrorException;
use Mathrix\Lumen\Exceptions\ValidationException;

/**
 * Class BasePipeline.
 * Describe a base pipeline while user interacts with Braintree.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0
 */
abstract class BasePipeline
{
    /** @var Request The Illuminate HTTP request. */
    private $request;
    /** @var User $customer The customer. */
    private $customer;
    /** @var MessageBag $validationErrors The validation errors */
    private $validationErrors;


    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->customer = $this->request->user(); // By default, the user is the requester.
    }


    /**
     * Override the pipeline customer.
     *
     * @param User $newCustomer
     */
    public function setCustomer(User $newCustomer)
    {
        $this->customer = $newCustomer;
    }


    /**
     * Process the pipeline after validation.
     *
     * @throws ValidationException
     * @throws \Throwable
     */
    public final function process()
    {
        if (!$this->validate()) {
            throw new ValidationException($this->validationErrors->jsonSerialize());
        }

        throw_if(!$this->work(), new Http500InternalServerErrorException([
            "pipeline" => get_class($this)
        ]));

        if (!$this->work()) {
            // Do stuff
        }

        return $this->finalize();
    }


    /**
     * Do the validation.
     */
    abstract public function validate(): bool;


    /**
     * Do the pipeline work.
     *
     * @return bool
     */
    abstract protected function work(): bool;


    /**
     * If the pipeline succeeded, finalize the process (issue products, privileges, etc.).
     * Also return the final JsonResponse.
     *
     * @return JsonResponse
     */
    abstract protected function finalize(): JsonResponse;
}
