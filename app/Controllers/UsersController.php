<?php

namespace App\Controllers;

use App\Exceptions\InvalidBraintreeCustomerException;
use App\Models\Log;
use App\Models\User;
use App\Services\Stripe\Serializers\SubscriptionSerializer;
use Braintree\Transaction;
use Braintree\TransactionSearch;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Mathrix\Lumen\Zero\Controllers\BaseController;
use Mathrix\Lumen\Zero\Exceptions\Http\Http400BadRequestException;
use Mathrix\Lumen\Zero\Exceptions\Http\Http401UnauthorizedException;
use Mathrix\Lumen\Zero\Exceptions\ValidationException;
use Mathrix\Lumen\Zero\Responses\PaginationJsonResponse;
use Mathrix\Lumen\Zero\Responses\SuccessJsonResponse;

/**
 * Class UsersController.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 1.0.0
 */
class UsersController extends BaseController
{
    /**
     * POST /users
     * Create a new user.
     *
     * @param Request $request The Illuminate HTTP request.
     *
     * @return SuccessJsonResponse
     */
    public function standardPost(Request $request): SuccessJsonResponse
    {
        // Build and save the user
        $user = new User();

        User::unguarded(function () use ($user, $request) {
            $user->fill($request->all())->save();
        });

        return new SuccessJsonResponse($user->fresh());
    }


    /**
     * GET /users/{userId}
     *
     * @param Request $request The Illuminate HTTP request.
     * @param string $key
     * @param int $identifier The user id.
     *
     * @return SuccessJsonResponse
     */
    public function standardGet(Request $request, string $key, $identifier): SuccessJsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        if (!scope($user, "users:read")) {
            return parent::standardGet($request, $key, $identifier);
        }

        $target = User::query()->findOrFail($identifier);
        $target->makeVisible(["premium_bypass", "braintree_id", "braintree"]);

        return new SuccessJsonResponse($target);
    }


    /**
     * PATCH /users/{userId}
     * Patch a user by its id.
     *
     * @param Request $request The Illuminate HTTP request.
     * @param string $key
     * @param int $identifier The user id.
     *
     * @return SuccessJsonResponse
     * @throws Http401UnauthorizedException
     * @throws ValidationException
     */
    public function standardPatch(Request $request, string $key, $identifier): SuccessJsonResponse
    {
        /** @var User $target */
        $target = User::query()->findOrFail($identifier);
        /** @var User $user */
        $user = $request->user();

        // Check permission
        $user->canOrFail("patch", $target);

        // Unlock fields for privileged user
        User::unguard($user->hasScope("users:write"));

        // Check for current_password, skip for admins if they are editing another user
        $this->validate($request, [
            "current_password" => Rule::requiredIf(function () use ($request, $user, $target) {
                $passwordProvided = $request->has("password");
                $targetHimself = $user->id === $target->id;
                $hasScope = $user->hasScope("users:write");

                return $passwordProvided && ($targetHimself || !$hasScope);
            })
        ]);

        // Engage password modification
        if ($request->has("current_password") && $request->has("password")) {
            $current = $request->json("current_password");

            if (!Hash::check($current, $target->password)) {
                throw new Http401UnauthorizedException([], "current_password is incorrect");
            }
        }

        // Allow some additional fields
        $columns = array_merge($target->getFillable(), ["type", "premium_bypass"]);
        $target->fill($request->only($columns))
            ->save();

        return new SuccessJsonResponse($target->fresh());
    }


    /**
     * GET /users/search/{query}/{page}/{perPage}?order={order}&dir={dir}
     *
     * @param Request $request
     * @param string $query
     *
     * @return PaginationJsonResponse
     * @throws Http400BadRequestException
     */
    public function search(Request $request, string $query)
    {
        $order = $request->query("order", "id");
        $dir = $request->query("dir", "asc");

        $fields = Collection::make([
            "username",
            "email",
            "firstname",
            "lastname",
            "scopes",
            "braintree_id",
            "stripe_id"
        ]);

        $builder = User::query()->orderBy($order, $dir);

        $fields->each(function ($field) use ($query, &$builder) {
            $builder = $builder->orWhere($field, "LIKE", "%$query%");
        });

        return new PaginationJsonResponse($builder);
    }


    /**
     * GET /users/{userId}/subscriptions/{page}/{perPage}
     * Get the user subscriptions.
     *
     * @param Request $request The Illuminate HTTP request.
     * @param int $userId The user id.
     *
     * @return JsonResponse
     * @throws Http401UnauthorizedException
     * @throws InvalidBraintreeCustomerException
     */
    public function getSubscriptions(Request $request, int $userId)
    {
        /** @var User $user */
        $user = $request->user();
        /** @var User $customer */
        $customer = User::query()->findOrFail($userId);
        $user->canOrFail("subscriptions", $customer);

        if (!$user->isStripeCustomer() && !$user->isBraintreeCustomer()) {
            return new  JsonResponse([
                "page" => 0,
                "perPage" => 1000,
                "total" => 0,
                "data" => []
            ]);
        }

        $subscriptions = new Collection();

        if ($customer->isBraintreeCustomer()) {
            foreach ($customer->getBraintreeSubscriptions() as $braintreeSubscription) {
                $subscriptions->push(SubscriptionSerializer::serialize($braintreeSubscription));
            }
        }

        if ($customer->isStripeCustomer()) {
            foreach ($customer->getStripeSubscriptions() as $stripeSubscription) {
                $subscriptions->push(SubscriptionSerializer::serialize($stripeSubscription));
            }
        }

        return new JsonResponse([
            "page" => 0,
            "perPage" => 1000,
            "total" => $subscriptions->count(),
            "data" => $subscriptions
        ]);
    }


    /**
     * GET /users/{userId}/transactions/{page}/{perPage}
     * Get user transactions.
     *
     * @param int $userId
     * @param int $page
     * @param int $perPage
     *
     * @return JsonResponse
     * @throws InvalidBraintreeCustomerException
     */
    public function transactions(int $userId, int $page, int $perPage)
    {
        /** @var User $customer */
        $customer = User::query()->findOrFail($userId);
        $customer->requireBraintreeCustomer();
        $transactionResults = Transaction::search([
            TransactionSearch::customerId()
                ->is($customer->braintree_id),
        ]);
        $transactions = new Collection();

        foreach ($transactionResults as $transaction) {
            $transactions->push(Formatter::cast($transaction));
        }

        return new JsonResponse([
            "page" => $page,
            "per_page" => $perPage,
            "total" => $transactions->count(),
            "data" => $transactions
        ]);
    }


    /**
     * DELETE /users/{userId}/cache
     *
     * @param int $userId
     *
     * @return JsonResponse
     */
    public function cache(int $userId): JsonResponse
    {
        /** @var User $user */
        $user = User::query()->findOrFail($userId);
        $user->invalidate();

        return new SuccessJsonResponse();
    }


    /**
     * POST /users/points
     * Add points to the current user.
     *
     * @param Request $request The Illuminate HTTP request.
     *
     * @return JsonResponse
     * @throws ValidationException
     */
    public function points(Request $request)
    {
        /** @var User $user */
        $user = $request->user();

        $this->validate($request, [
            "points" => "required|integer"
        ]);

        $pointsToAdd = (int)$request->json("points");

        // Update the user points count
        $user->update(["points" => $user->points + $pointsToAdd]);

        return new JsonResponse($user);
    }
}
