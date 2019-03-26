<?php

namespace App\Controllers;

use App\Models\Activity;
use App\Models\Log;
use App\Models\Module;
use App\Models\User;
use App\Services\Braintree\Models\Plan;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Mathrix\Lumen\Bases\BaseController;
use Mathrix\Lumen\Exceptions\Http\Http400BadRequestException;
use Mathrix\Lumen\Exceptions\Http\Http429TooManyRequestsException;

/**
 * Class ProfileController.
 * Handle Profile API requests. It should be noted that this controller name is a singular noun, even it is against
 * conventions.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0
 */
class ProfileController extends BaseController
{
    /**
     * Invoke the UsersController in order to process the request.
     *
     * @param Request $request The Illuminate HTTP request.
     * @param string $action
     *
     * @return JsonResponse
     */
    private function invokeUsersController(Request $request, string $action): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        /** @var UsersController $usersController */
        $usersController = app()->make(UsersController::class);

        return $usersController->{$action}($request, $user->id);
    }


    /**
     * GET /profile
     * Get the current user.
     *
     * @param Request $request The Illuminate HTTP request.
     * @param int|null $id Unused here
     *
     * @return JsonResponse
     */
    public function get(Request $request, int $id = null): JsonResponse
    {
        return $this->invokeUsersController($request, "get");
    }


    /**
     * POST /profile
     * Register a new user.
     *
     * @param Request $request The Illuminate HTTP request.
     *
     * @return JsonResponse
     */
    public function post(Request $request): JsonResponse
    {
        $request->merge(["type" => "registered"]);

        return $this->invokeUsersController($request, "post");
    }


    /**
     * PATCH /profile
     * Patch the current user.
     *
     * @param Request $request The Illuminate HTTP request.
     * @param int|null $id Unused here
     *
     * @return JsonResponse
     */
    public function patch(Request $request, int $id = null): JsonResponse
    {
        return $this->invokeUsersController($request, "patch");
    }


    /**
     * POST /profile/points
     * Add points to the current user.
     *
     * @param Request $request The Illuminate HTTP request.
     *
     * @return JsonResponse
     * @throws Http400BadRequestException
     * @throws Http429TooManyRequestsException
     * @throws \Illuminate\Validation\ValidationException
     * @throws \Exception
     */
    public function points(Request $request)
    {
        /** @var User $user */
        $user = $request->user();

        // Check permission
        $user->canOrFail("points", $user);

        $loggableType = $request->input("loggable_type");
        $loggableId = $request->input("loggable_id");
        $useLoggable = $loggableType !== null && $loggableId !== null;

        // Valid loggable data were passed
        if ($useLoggable) {
            /** @var Activity|Module $loggableModelClass */
            $loggableModelClass = Relation::getMorphedModel($loggableType); // Retrieve morphed loggable model class

            // If null, abort
            if ($loggableModelClass === null) {
                throw new Http400BadRequestException(
                    array_merge(
                        $request->only(["loggable_type", "loggable_id"]), [
                            "model" => $loggableModelClass
                        ]
                    ), "Invalid loggable model");
            }

            // Else, try to find the loggable model
            /** @var Activity|Module $loggable */
            $loggable = $loggableModelClass::query()->findOrFail($loggableId);

            $userHasAlreadyGatheredPointsWithThisModule = Log::query()
                ->where([
                    ["event", "user_gathered_points"],
                    ["user_id", $user->id],
                    ["loggable_type", $loggableType],
                    ["loggable_id", $loggable->id],
                ])
                ->whereBetween("created_at", [
                    Carbon::now()->setTime(0, 0, 0), // Today midnight
                    Carbon::now(),
                ])
                ->exists();

            // User already gathered point from this loggable today
            if ($userHasAlreadyGatheredPointsWithThisModule) {
                throw new Http429TooManyRequestsException("Already gathered points today for this model");
            }
        }

        $this->validate($request, [
            "points" => "required|integer"
        ]);

        $pointsToAdd = (int)$request->json("points");

        // Update the user points count
        $user->update(["points" => $user->points + $pointsToAdd]);
        $user->append(["premium", "braintree", "stripe"]);

        // Log the points update
        $logData = ["points" => $pointsToAdd];

        if ($useLoggable) {
            $logData = array_merge($logData, [
                "loggable_type" => $loggableType,
                "loggable_id" => $loggableId,
            ]);
        }

        // Then log the information
        Log::log("user_gathered_points", $user, $logData);

        return new JsonResponse($user);
    }


    /**
     * POST /profile/exists
     * Check if a profile exists by username and email.
     *
     * @param Request $request The Illuminate HTTP request.
     *
     * @return JsonResponse
     */
    public function exists(Request $request)
    {
        $wheres = $request->only(["username", "email"]);
        $exists = false;

        foreach ($wheres as $key => $value) {
            if (User::query()->where($key, $value)->exists()) {
                $exists = true;
                break;
            }
        }

        return new JsonResponse(["exists" => $exists]);
    }


    /**
     * PATCH /profile/payment-method
     * Update a profile payment method.
     *
     * @param Request $request The Illuminate HTTP request.
     *
     * @return JsonResponse
     * @throws Http400BadRequestException
     * @throws \Illuminate\Validation\ValidationException
     * @throws \Mathrix\Lumen\Exceptions\ValidationException
     */
    public function updatePaymentMethod(Request $request)
    {
        $this->validate($request, [
            "paymentMethodNonce" => "required",
        ]);

        /** @var User $user */
        $user = $request->user();

        if (!$user->isBraintreeCustomer()) {
            $user->createBraintreeCustomer();
        }

        $paymentMethodAdditionResult = $user->addBraintreePaymentMethod($request->json("paymentMethodNonce"));

        if (!$paymentMethodAdditionResult->success) {
            throw new Http400BadRequestException(
                $paymentMethodAdditionResult->jsonSerialize(),
                $paymentMethodAdditionResult->message
            );
        }
        $newPaymentMethod = $paymentMethodAdditionResult->paymentMethod;

        // If user is already a Stripe customer, begin the migration process
        if ($user->isStripeCustomer()) {
            /** @var \Stripe\Customer $customer */
            $stripeCustomer = $user->getStripeCustomer();

            /** @var \Stripe\Subscription $stripeSubscription */
            foreach ($stripeCustomer->subscriptions->data as $stripeSubscription) {
                // Get new subscription params
                $braintreeSubscriptionShouldStart = Carbon::createFromTimestamp($stripeSubscription->current_period_end);
                $braintreeTrialDays = Carbon::now()->diffInDays($braintreeSubscriptionShouldStart);
                $newBraintreePlanId = Plan::getStripeEquivalentPlanId($stripeSubscription->plan->id);

                if ($newBraintreePlanId !== false) {
                    $subscriptionCreationResult = \Braintree\Subscription::create([
                        "id" => "sub_" . str_replace("-", "", \Faker\Factory::create()->uuid),
                        "planId" => $newBraintreePlanId,
                        "paymentMethodToken" => $newPaymentMethod->token,
                        "trialPeriod" => true,
                        "trialDuration" => $braintreeTrialDays,
                        "trialDurationUnit" => "day"
                    ]);

                    if (!$subscriptionCreationResult->success) {
                        throw new Http400BadRequestException(
                            $subscriptionCreationResult->jsonSerialize(),
                            $subscriptionCreationResult->message
                        );
                    }

                    $stripeSubscription->cancel();
                }
            }

            $user->stripe_id = null;
            $user->save();
        } else {
            // Else, user is only a Braintree customer
            // Now, change all subscriptions to the new payment method
            $braintreeSubscriptions = $user->getBraintreeSubscriptions();

            foreach ($braintreeSubscriptions as $braintreeSubscription) {
                $subscriptionUpdateResult = \Braintree\Subscription::update($braintreeSubscription->id, [
                    "paymentMethodToken" => $newPaymentMethod->token
                ]);

                if (!$subscriptionUpdateResult->success) {
                    throw new Http400BadRequestException(
                        $subscriptionUpdateResult->jsonSerialize(),
                        $subscriptionUpdateResult->message
                    );
                }
            }

            // Now, delete all the other payment methods
            $user->forgetBraintreeCustomerCache();
            $braintreeCustomer = $user->getBraintreeCustomer();

            foreach ($braintreeCustomer->paymentMethods as $paymentMethod) {
                if ($paymentMethod->token !== $newPaymentMethod->token) {
                    $paymentMethodDeletionResult = \Braintree\PaymentMethod::delete($paymentMethod->token);

                    if (!$paymentMethodDeletionResult) {
                        throw new Http400BadRequestException(
                            $paymentMethodDeletionResult->jsonSerialize(),
                            $paymentMethodDeletionResult->message
                        );
                    }
                }
            }
        }

        $user = $user->refresh();
        $user->append(["premium", "braintree", "stripe"]);

        return new JsonResponse($user);

    }
}
