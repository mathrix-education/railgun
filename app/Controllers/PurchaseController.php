<?php
namespace App\Controllers;

use App\Exceptions\Http400InvalidRequestException;
use App\Exceptions\Http401UnauthorizedException;
use App\Exceptions\Http422ModelSaveException;
use App\Exceptions\Http422ModelValidationException;
use App\Models\Discount;
use App\Models\Interfaces\Purchasable;
use App\Models\Log;
use App\Models\Purchase;
use App\Models\User;
use App\Utils\StripeUtils;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * Class PurchaseController
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @package App\Controllers
 * @since 4.0.4
 * @copyright Mathrix Education SA
 */
class PurchaseController extends BaseController
{
    /**
     * @param Request $request
     *
     * @param array $overrideRequest
     *
     * @return JsonResponse
     * @throws Http400InvalidRequestException
     * @throws Http401UnauthorizedException
     * @throws Http422ModelSaveException
     * @throws Http422ModelValidationException
     * @throws ValidationException
     */
    private function process(Request $request, $overrideRequest = []): JsonResponse
    {
        $request->merge($overrideRequest);

        // Validate request
        $this->validate($request, [
            "user_id" => "exists:users,id",
            "premium" => "",
            "sponsor_email" => "exists:users,email",
            "purchasable_id" => "required|integer",
            "purchasable_type" => "in:" . implode(",", Purchase::PURCHASABLE_TYPES)
        ]);

        // Build options
        $options = $request->only([
            "user_id",
            "stripe_token",
            "premium",
            "sponsor_email",
            "discount_code",
            "purchasable_id",
            "purchasable_type"
        ]);
        $defaultOptions = [
            "stripe_token" => null,
            "premium" => false,
            "sponsor_email" => null,
            "discount_code" => null,
            "purchasable_id" => null,
            "purchasable_type" => null
        ];
        $options = array_replace_recursive($defaultOptions, $options);

        // Build objects
        $customer = $this->getUserByRequestOrUnauthorized($request);
        /** @var Purchasable $purchasableClass */
        $purchasableClass = Relation::getMorphedModel($options["purchasable_type"]);
        /** @var Purchasable $purchasableClass */
        $purchasable = $purchasableClass::where("id", "=", $options["purchasable_id"])->firstOrFail();

        // Update customer credit card
        if (!empty($options["stripe_token"])) {
            $customer->updateCardOrCreateStripeCustomer($options["stripe_token"]);
        }

        // Stripe customer check
        if (!$customer->hasStripeId()) {
            throw new Http400InvalidRequestException("User with id `{$customer->id}` is not a Stripe customer");
        }

        // Customer already bought the purchasable
        if ($customer->hasPurchased($purchasable)) {
            throw new Http400InvalidRequestException("User with id `{$customer->id}` has already bought " .
                "{$purchasable->getType()} with id `{$purchasable->id}`");
        }

        // Build the price
        /** @var int $price The price in cents */
        $price = null;
        $description = $purchasable->name;

        switch ($purchasable->getType()) {
            case "training":
                $price = $purchasable->price;

                // Trigger sponsor discount
                if (!empty($options["sponsor_email"])) {
                    $sponsor = User::where("email", "=", $options["sponsor_email"])
                        ->firstOrFail();

                    if ($sponsor->hasPurchased($purchasable)) {
                        $price -= StripeUtils::SPONSORSHIP_DISCOUNT;

                        if (getenv("CASHIER_ENV") !== "testing") {
                            $sponsor->refund(StripeUtils::SPONSORSHIP_REFUND); // @codeCoverageIgnore
                        }
                    }
                }

                // Trigger premium surcharge
                if ($options["premium"] && !$customer->premium) { // TODO
                    $price += StripeUtils::getSurchargeForPremium($purchasable);
                    $description .= " with premium subscription";
                }

                // Trigger discount code
                if (!empty($options["discount_code"])) {
                    /** @var Discount $discount */
                    $discount = Discount::where("code", "=", $options["discount_code"])->first();

                    if ($discount !== null && $discount->counter > 0) {
                        // @codeCoverageIgnoreStart
                        if ($discount->type === "absolute") {
                            $price -= $discount->value;
                        } else if ($discount->type === "percentage") {
                            $price *= 1 - ($discount->value / 100);
                        }
                        // @codeCoverageIgnoreEnd
                    }

                    // Decrement counter
                    if ($discount->counter != -1) {
                        $discount->counter--;
                        $discount->save();
                    }
                }
                break;
            case "section":
                $price = StripeUtils::SECTION_PRICE;
                break;
        }

        // Apply charge
        if (getenv("CASHIER_ENV") !== "testing") {
            $charge = $customer->charge($price, ["description" => $description]); // @codeCoverageIgnore
        } else {
            // Mock charge status
            $charge = new \stdClass();
            $charge->status = "success";
        }

        if ($charge->status === "success") {
            $purchase = new Purchase([
                "user_id" => $customer->id,
                "purchasable_id" => $purchasable->id,
                "purchasable_type" => $purchasable->getType(),
                "price" => $price,
                "refund" => 0
            ]);

            // Add premium subscription if needed
            if ($options["premium"]) {
                $customer->newSubscription("premium", "v3_annually")
                    ->trialUntil(StripeUtils::getNextJuly())
                    ->create();
            }

            $purchase->save();

            Log::log("user_payment_succeed_for_{$purchasable->getType()}", $customer, [
                "loggable_id" => $purchasable->id,
                "loggable_type" => $purchasable->getType()
            ]);

            return new JsonResponse(
                $customer->with(["class_", "subscriptions", "purchases", "purchases.purchasable"])
                    ->find($customer->id)
            );
        } else {
            throw new Http400InvalidRequestException("Charge failed"); // @codeCoverageIgnore
        }
    }


    /**
     * @apiVersion 4.0.4
     * @api {post} /purchases
     * @apiGroup Purchases
     * @apiName PurchaseAdd
     * @apiDescription Buy a section or a training for someone
     * @apiUse Bearer
     * @apiPermission admin
     * @apiParam (Data) {string=training,section} purchasable_type The purchasable type
     * @apiParam (Data) {int} purchasable_id The purchasable id
     * @apiParam (Data) {int} [stripe_token] The stripe token (if `null`, we will attempt to use its current card)
     * @apiParam (Data) {string} [discount_code=false] The discount code
     * @apiParam (Data) {string} [sponsor_email=false] The sponsor email
     * @apiParam (Data) {bool} [premium=false] If a premium subscription must be applied (training-only)
     * @apiUse UserModel
     * @apiUse PurchaseArray
     * @apiUse Http401Unauthorized
     * @apiUse Http404ModelNotFound
     *
     * @param Request $request
     *
     * @return JsonResponse
     * @throws Http400InvalidRequestException
     * @throws Http401UnauthorizedException
     * @throws Http422ModelSaveException
     * @throws Http422ModelValidationException
     * @throws ValidationException
     */
    public function add(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $user->canOrFail("add", Purchase::class);

        return $this->process($request);
    }


    /**
     * @apiVersion 4.0.4
     * @api {post} /purchases/buy/{purchasable_type}/{purchasable_id}
     * @apiGroup Purchases
     * @apiName PurchaseBuy
     * @apiDescription Buy a section or a training
     * @apiUse Bearer
     * @apiPermission admin
     * @apiParam {string=training,section} purchasable_type The purchasable type
     * @apiParam {int} purchasable_id The purchasable id
     * @apiParam (Data) {int} [stripe_token] The stripe token (if `null`, we will attempt to use its current card)
     * @apiParam (Data) {string} [discount_code=false] The discount code
     * @apiParam (Data) {string} [sponsor_email=false] The sponsor email
     * @apiParam (Data) {bool} [premium=false] If a premium subscription must be applied (training-only)
     * @apiUse UserModel
     * @apiUse PurchaseArray
     * @apiUse Http401Unauthorized
     * @apiUse Http404ModelNotFound
     *
     * @param Request $request
     * @param string $purchasableType
     * @param int $purchasableId
     *
     * @return JsonResponse
     * @throws Http400InvalidRequestException
     * @throws Http401UnauthorizedException
     * @throws Http422ModelSaveException
     * @throws Http422ModelValidationException
     * @throws ValidationException
     */
    public function buy(Request $request, string $purchasableType, int $purchasableId): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $user->canOrFail("buy", Purchase::class);

        return $this->process($request, [
            "purchasable_type" => $purchasableType,
            "purchasable_id" => $purchasableId
        ]);
    }
}
