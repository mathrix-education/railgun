<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\User;
use App\Services\Bishop\Card;
use App\Services\Bishop\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Mathrix\Lumen\Zero\Controllers\BaseController;
use Mathrix\Lumen\Zero\Responses\DataResponse;
use Stripe\Customer;
use Stripe\Exception\ApiErrorException;
use function array_merge;
use function hasScope;

/**
 * Serve routes starting with /users.
 */
class UsersController extends BaseController
{
    /**
     * POST /users
     * Create a new user.
     *
     * @param Request $request The Illuminate HTTP request.
     *
     * @return DataResponse The newly created user.
     */
    public function create(Request $request): SuccessJsonResponse
    {
        $user = new User();
        $user->fillable(array_merge($user->getFillable(), ['password']));
        $user->fill($request->all())->save();

        return new DataResponse($user);
    }

    /**
     * GET /users/{userId}
     *
     * @param Request    $request    The Illuminate HTTP request.
     * @param string     $key        The key.
     * @param int|string $identifier The user identifier.
     *
     * @return SuccessJsonResponse The user.
     */
    public function standardGet(Request $request, string $key, $identifier): SuccessJsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        if (!hasScope($user, 'users:read')) {
            return parent::standardGet($request, $key, $identifier);
        }

        $target = User::query()
            ->findOrFail($identifier);
        $target->makeVisible(['premium_bypass', 'braintree_id', 'braintree']);

        return new SuccessJsonResponse($target);
    }

    /**
     * PATCH /users/{userId}
     * Patch a user by its id.
     *
     * @param Request    $request    The Illuminate HTTP request.
     * @param string     $key        The key.
     * @param int|string $identifier The user identifier.
     *
     * @return SuccessJsonResponse The patched user.
     *
     * @throws Http401UnauthorizedException
     * @throws ValidationException
     */
    public function standardPatch(Request $request, string $key, $identifier): SuccessJsonResponse
    {
        /** @var User $target */
        $target = User::query()
            ->findOrFail($identifier);
        /** @var User $user */
        $user = $request->user();

        // Check permission
        $user->canOrFail('patch', $target);

        // Unlock fields for privileged user
        User::unguard($user->hasScope('users:write'));

        // Check for current_password, skip for admins if they are editing another user
        $this->validate(
            $request,
            [
                'current_password' => Rule::requiredIf(static function () use ($request, $user, $target) {
                    $passwordProvided = $request->has('password');
                    $targetHimself    = $user->id === $target->id;
                    $hasScope         = $user->hasScope('users:write');

                    return $passwordProvided && ($targetHimself || !$hasScope);
                }),
            ]
        );

        // Engage password modification
        if ($request->has('current_password') && $request->has('password')) {
            $current = $request->json('current_password');

            if (!Hash::check((string)$current, $target->password)) {
                throw new Http401UnauthorizedException([], 'current_password is incorrect');
            }
        }

        // Allow some additional fields
        $columns = array_merge($target->getFillable(), ['type', 'premium_bypass']);
        $target->fill($request->only($columns))
            ->save();

        return new SuccessJsonResponse($target->fresh());
    }

    /**
     * GET /users/{userId}/subscriptions/{page}/{perPage}
     * Get the user subscriptions.
     *
     * @param Request $request The Illuminate HTTP request.
     * @param int     $userId  The user id.
     *
     * @return SuccessJsonResponse The user subscriptions.
     *
     * @throws Http401UnauthorizedException
     */
    public function getSubscriptions(Request $request, int $userId): SuccessJsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        /** @var User $customer */
        $customer = User::query()
            ->findOrFail($userId);
        $user->canOrFail('subscriptions', $customer);

        if (!$customer->isStripeCustomer() && !$customer->isBraintreeCustomer()) {
            // If the is not a Stripe nor a Braintree customer, simply return and empty array.
            return new SuccessJsonResponse([], [
                'page'    => 0,
                'perPage' => 1000,
                'total'   => 0,
            ]);
        }

        $subscriptions = new Collection();

        if ($customer->isBraintreeCustomer()) {
            foreach ($customer->getBraintreeSubscriptions() as $braintreeSubscription) {
                $subscriptions->push(Subscription::from($braintreeSubscription));
            }
        }

        if ($customer->isStripeCustomer()) {
            foreach ($customer->getStripeSubscriptions() as $stripeSubscription) {
                $subscriptions->push(Subscription::from($stripeSubscription));
            }
        }

        [$page, $perPage] = PaginationJsonResponse::getPagination();

        return new SuccessJsonResponse($subscriptions, [
            'page'    => $page,
            'perPage' => $perPage,
            'total'   => $subscriptions->count(),
        ]);
    }

    /**
     * GET /users/{userId}/cards
     * Get the user Stripe cards.
     *
     * @param Request $request The Illuminate HTTP request.
     *
     * @return SuccessJsonResponse
     *
     * @throws ApiErrorException
     */
    public function getCards(Request $request): SuccessJsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        /**
         * @var int $page    The page.
         * @var int $perPage The number of card per page.
         */
        [$page, $perPage] = PaginationJsonResponse::getPagination();

        if (!$user->isStripeCustomer()) {
            return new SuccessJsonResponse([], [
                'page'     => $page,
                'per_page' => $perPage,
            ]);
        }

        $cards = Customer::allSources(
            $user->stripe_id,
            [
                'limit'  => 100,
                'object' => 'card',
            ]
        );

        $data = [];

        foreach ($cards as $card) {
            $data[] = Card::from($card);
        }

        return new SuccessJsonResponse($data, [
            'page'     => 0,
            'per_page' => 100,
        ]);
    }
}
