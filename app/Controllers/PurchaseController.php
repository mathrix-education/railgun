<?php
namespace App\Controllers;

use App\Exceptions\Http400InvalidRequestException;
use App\Exceptions\Http401UnauthorizedException;
use App\Exceptions\Http422ModelSaveException;
use App\Exceptions\Http422ModelValidationException;
use App\Models\Discount;
use App\Models\Interfaces\Purchasable;
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
        // Validate request
        $this->validate($request, [
            "user_id" => "exists:users,id",
            "sponsor_email" => "exists:users,email",
            "purchasable_id" => "required|integer",
            "purchasable_type" => "in:" . implode(",", Purchase::PURCHASABLE_TYPES)
        ]);

        // Build options
        $options = $request->only([
            "user_id",
            "stripe_token",
            "sponsor_email",
            "discount_code",
            "purchasable_id",
            "purchasable_type"
        ]);
        $defaultOptions = [
            "stripe_token" => null,
            "sponsor_email" => null,
            "discount_code" => null,
            "purchasable_id" => null,
            "purchasable_type" => null
        ];
        $options = array_replace_recursive($defaultOptions, $options, $overrideRequest);

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

        switch ($purchasable->getType()) {
            case "training":
                $price = $purchasable->price;

                // Trigger sponsor discount
                if (getenv("CASHIER_ENV") !== "testing" && !empty($options["sponsor_email"])) {
                    $sponsor = User::where("email", "=", $options["sponsor_email"])
                        ->firstOrFail();

                    if (!$sponsor->hasPurchased($purchasable)) {
                        throw new Http400InvalidRequestException("User with email `{$options["sponsor_email"]}`"
                            . "is not a valid sponsor");
                    } else if (getenv("CASHIER_ENV") !== "testing") {
                        $sponsor->refund(StripeUtils::SPONSORSHIP_REFUND);
                        $price -= StripeUtils::SPONSORSHIP_DISCOUNT;
                    }
                }

                // Trigger discount code
                if (!empty($options["discount_code"])) {
                    /** @var Discount $discount */
                    $discount = Discount::where("code", "=", $options["discount_code"])->first();

                    if ($discount !== null && $discount->counter > 0) {
                        if ($discount->type === "absolute") {
                            $price -= $discount->value;
                        } else if ($discount->type === "percentage") {
                            $price *= 1 - ($discount / 100);
                        }
                    }
                }
                break;
            case "section":
                $price = StripeUtils::SECTION_PRICE;
                break;
        }

        // Apply charge
        if (getenv("CASHIER_ENV") !== "testing") {
            $charge = $customer->charge($price); // @codeCoverageIgnore
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

            $purchase->save();

            return new JsonResponse($purchase);
        } else {
            throw new Http400InvalidRequestException("Charge failed");
        }
    }


    /**
     * @param Request $request
     *
     * @return JsonResponse
     * @throws ValidationException
     * @throws Http400InvalidRequestException
     * @throws Http422ModelSaveException
     * @throws Http422ModelValidationException
     * @throws Http401UnauthorizedException
     */
    public function add(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $user->canOrFail("add", Purchase::class);

        return $this->process($request);
    }


    /**
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
