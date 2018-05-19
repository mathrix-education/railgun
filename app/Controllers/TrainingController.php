<?php
namespace App\Controllers;

use App\Exceptions\Http400InvalidRequestException;
use App\Exceptions\Http404ModelNotFoundException;
use App\Models\Log;
use App\Models\Purchase;
use App\Models\Training;
use App\Models\User;
use App\Utils\StripeUtils;
use Faker\Factory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Class TrainingController
 *
 * @author Mathrix Back-End Team
 * @package App\Controllers
 * @since 1.0.0
 * @copyright Mathrix Education SA
 */
class TrainingController extends BaseController
{
    /**
     * @apiVersion 4.0.0
     * @api {get} /trainings/{?page}/{?perPage} Index
     * @apiGroup Trainings
     * @apiName TrainingsIndex
     * @apiDescription Retrieve trainings
     * @apiPermission public
     * @apiUse Pagination
     * @apiUse TrainingArray
     * @apiUse Http401Unauthorized
     */


    /**
     * @apiVersion 4.0.0
     * @api {get} /trainings/{id} Get
     * @apiGroup Trainings
     * @apiName TrainingGet
     * @apiDescription Get an training by id
     * @apiPermission public
     * @apiParam {int} id The training id
     * @apiUse TrainingModel
     * @apiUse Http404ModelNotFound
     */


    /**
     * @apiVersion 4.0.0
     * @api {post} /trainings Add
     * @apiGroup Trainings
     * @apiName TrainingAdd
     * @apiDescription Add a training
     * @apiPermission admin
     * @apiUse Bearer
     * @apiUse TrainingRequest
     * @apiUse TrainingModel
     * @apiUse Http401Unauthorized
     * @apiUse Http422ModelValidation
     * @apiUse Http422ModelSave
     */


    /**
     * @apiVersion 4.0.0
     * @api {patch} /trainings/{id} Edit
     * @apiGroup Trainings
     * @apiName TrainingEdit
     * @apiDescription Edit an training
     * @apiPermission admin
     * @apiUse Bearer
     * @apiParam {int} id The training id
     * @apiUse TrainingRequest
     * @apiUse TrainingModel
     * @apiUse Http401Unauthorized
     * @apiUse Http404ModelNotFound
     * @apiUse Http422ModelSave
     * @apiUse Http422ModelValidation
     */


    /**
     * @apiVersion 4.0.0
     * @api {delete} /trainings/{id} Delete
     * @apiGroup Trainings
     * @apiName TrainingDelete
     * @apiDescription Delete an training
     * @apiUse Bearer
     * @apiPermission admin
     * @apiParam {int} id The training id
     * @apiUse Http401Unauthorized
     * @apiUse Http404ModelNotFound
     */

    /**
     * @apiVersion 4.0.1
     * @api {get} /trainings/{id}/recursive Recursive
     * @apiGroup Trainings
     * @apiName TrainingRecursive
     * @apiDescription Recursively explore a training
     * @apiUse Bearer
     * @apiPermission registered if paid, admin otherwise
     * @apiParam {int} id The training id
     * @apiUse TrainingModel
     * @apiUse Http401Unauthorized
     * @apiUse Http404ModelNotFound
     *
     * @param Request $request
     * @param int $id
     *
     * @return JsonResponse
     * @throws Http404ModelNotFoundException
     * @throws \Exception
     */
    public function recursive(Request $request, int $id): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $canSeeInvisible = $user !== null && $user->isModerator();

        /** @var Training $training */
        $training = Training::with([
            "sections" => function (HasMany $query) use ($canSeeInvisible) {
                $query->orderBy("order");

                if (!$canSeeInvisible) {
                    $query->where("visible", true);
                }
            },
            "sections.lessons" => function (HasMany $query) use ($canSeeInvisible) {
                $query->orderBy("order");

                if (!$canSeeInvisible) {
                    $query->where("visible", true);
                }
            },
            "sections.lessons.module"
        ])
            ->whereId($id)
            ->firstOrFail();

        if (!$training->visible && !$canSeeInvisible) {
            throw new Http404ModelNotFoundException();
        }

        $user->canOrFail("recursive", $training);

        return new JsonResponse($training);
    }


    /**
     * @apiVersion 4.0.1
     * @api {post} /trainings/{id}/buy Buy
     * @apiGroup Trainings
     * @apiName TrainingBuy
     * @apiDescription Buy a training
     * @apiUse Bearer
     * @apiPermission registered
     * @apiParam {int} id The training id
     * @apiParam (Data) {int} stripe_token The stripe token
     * @apiParam (Data) {bool} [sponsor_email=false] The sponsor email
     * @apiParam (Data) {bool} [premium=false] If a premium subscription must be applied
     * @apiUse UserModel
     * @apiUse PurchaseArray
     * @apiUse Http401Unauthorized
     * @apiUse Http404ModelNotFound
     *
     * @param Request $request
     * @param int $id
     *
     * @return JsonResponse
     * @throws Http400InvalidRequestException
     * @throws \Exception
     */
    public function buy(Request $request, int $id): JsonResponse
    {
        $training = Training::whereId($id)->firstOrFail();
        $stripeToken = $request->input("stripe_token");
        $sponsorEmail = $request->input("sponsor_email", false);
        $withPremium = (bool)$request->input("premium", false);

        if ($stripeToken === null) {
            throw new Http400InvalidRequestException("stripe_token cannot be null");
        }

        /** @var User $user */
        $user = $request->user();

        if ($user->hasPurchased($training->id, "training")) {
            throw new Http400InvalidRequestException("Already bought the training '{$training->name}'");
        }

        /** @var int $totalPrice Total price to pay, including premium surcharge if needed */
        $totalPrice = $training->price;

        // Pipe Stripe exception to our HTTP 400 Bad Request exception
        try {
            $description = $training->name;

            if ($withPremium && !$user->premium) {
                $totalPrice += StripeUtils::getSurchargeForPremium($training);
                $description .= " with premium subscription";
            }

            // Sponsorship discount
            if ($sponsorEmail) {
                $sponsor = User::whereEmail($sponsorEmail)->firstOr(function () use ($sponsorEmail) {
                    throw new Http404ModelNotFoundException("Unable to find user with the email `$sponsorEmail`");
                });

                if ($sponsor->hasPurchased($training->id, "training")) {
                    // Reduce total price
                    $totalPrice -= StripeUtils::SPONSORSHIP_DISCOUNT;
                }
            }

            // Charge the user by creating him if he does not exists
            $user->updateCardOrCreateStripeCustomer($stripeToken);

            $response = $user->charge($totalPrice, [
                "description" => $description
            ]);
        } catch (\InvalidArgumentException $exception) {
            throw new Http400InvalidRequestException($exception->getMessage());
        }

        // Give subscription if charge succeeded
        if ($response->status == "succeeded") {
            // Save the purchase
            $purchase = new Purchase([
                "uuid" => Factory::create()->uuid,
                "price" => $totalPrice,
                "user_id" => $user->id,
                "purchasable_id" => $training->id,
                "purchasable_type" => "training"
            ]);
            $purchase->save();

            // Add premium subscription if needed
            if ($withPremium) {
                $user->newSubscription("premium", "v3_annually")
                    ->trialUntil(StripeUtils::getNextJuly())
                    ->create();
            }

            Log::log("user_payment_succeed_for_training", $user, ["training_id" => $training->id]);

            return new JsonResponse(
                $user->with(["class_", "subscriptions", "purchases", "purchases.purchasable"])
                    ->find($user->id)
            );
        } else {
            Log::log("user_payment_failed_for_training", $user, ["training_id" => $training->id]);

            return new JsonResponse([
                "status" => "failure"
            ], 400);
        }
    }
}
