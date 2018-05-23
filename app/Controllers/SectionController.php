<?php
namespace App\Controllers;

use App\Exceptions\Http400InvalidRequestException;
use App\Exceptions\Http404ModelNotFoundException;
use App\Models\Log;
use App\Models\Purchase;
use App\Models\Section;
use App\Models\User;
use App\Utils\StripeUtils;
use Faker\Factory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Class SectionController
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @package App\Controllers
 * @since 1.0.0
 * @copyright Mathrix Education SA
 */
class SectionController extends BaseController
{
    /**
     * @apiVersion 4.0.0
     * @api {get} /sections/{?page}/{?perPage} Index
     * @apiGroup Sections
     * @apiName SectionsIndex
     * @apiDescription Retrieve sections
     * @apiPermission moderator
     * @apiUse Pagination
     * @apiUse SectionArray
     * @apiUse Http401Unauthorized
     */


    /**
     * @apiVersion 4.0.0
     * @api {get} /sections/{id} Get
     * @apiGroup Sections
     * @apiName SectionGet
     * @apiDescription Get an section by id
     * @apiPermission public
     * @apiParam {int} id The section id
     * @apiUse SectionModel
     * @apiUse Http404ModelNotFound
     */


    /**
     * @apiVersion 4.0.0
     * @api {post} /sections Add
     * @apiGroup Sections
     * @apiName SectionAdd
     * @apiDescription Add a section
     * @apiPermission moderator
     * @apiUse Bearer
     * @apiUse SectionRequest
     * @apiUse SectionModel
     * @apiUse Http401Unauthorized
     * @apiUse Http422ModelValidation
     * @apiUse Http422ModelSave
     */


    /**
     * @apiVersion 4.0.0
     * @api {patch} /sections/{id} Edit
     * @apiGroup Sections
     * @apiName SectionEdit
     * @apiDescription Edit an section
     * @apiPermission moderator
     * @apiUse Bearer
     * @apiParam {int} id The section id
     * @apiUse SectionRequest
     * @apiUse SectionModel
     * @apiUse Http401Unauthorized
     * @apiUse Http404ModelNotFound
     * @apiUse Http422ModelSave
     * @apiUse Http422ModelValidation
     */


    /**
     * @apiVersion 4.0.0
     * @api {delete} /sections/{id} Delete
     * @apiGroup Sections
     * @apiName SectionDelete
     * @apiDescription Delete an section
     * @apiUse Bearer
     * @apiPermission moderator
     * @apiParam {int} id The section id
     * @apiUse Http401Unauthorized
     * @apiUse Http404ModelNotFound
     */


    /**
     * @apiVersion 4.0.2
     * @api {get} /sections/{id}/recursive Recursive
     * @apiGroup Sections
     * @apiName SectionRecursive
     * @apiDescription Recursively explore a section
     * @apiUse Bearer
     * @apiPermission registered if paid, admin otherwise
     * @apiParam {int} id The section id
     * @apiUse SectionModel
     * @apiUse LessonArray
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

        /** @var Section $section */
        $section = Section::with([
            "lessons" => function (HasMany $query) use ($canSeeInvisible) {
                $query->orderBy("order");

                if (!$canSeeInvisible) {
                    $query->where("visible", true);
                }
            },
            "lessons.module"
        ])
            ->whereId($id)
            ->firstOrFail();

        if (!$section->visible && !$canSeeInvisible) {
            throw new Http404ModelNotFoundException();
        }

        $user->canOrFail("recursive", $section);

        return new JsonResponse($section);
    }


    /**
     * @apiVersion 4.0.2
     * @api {post} /sections/{id}/buy Buy
     * @apiGroup Sections
     * @apiName SectionBuy
     * @apiDescription Buy an section
     * @apiUse Bearer
     * @apiPermission registered
     * @apiParam {int} id The section id
     * @apiParam (Data) {int} stripe_token The stripe token
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
        $section = Section::whereId($id)->firstOrFail();
        $stripeToken = $request->input("stripe_token");

        if ($stripeToken === null) {
            throw new Http400InvalidRequestException("stripe_token cannot be null");
        }

        /** @var User $user */
        $user = $request->user();

        if ($user->hasPurchased($section->id, "section")) {
            throw new Http400InvalidRequestException("Already bought the section '{$section->name}'");
        }

        // Charge the user by creating him if he does not exists
        $user->updateCardOrCreateStripeCustomer($stripeToken);

        // Pipe Stripe exception to our HTTP 400 Bad Request exception
        try {
            $description = $section->name;

            /** @var int $totalPrice Total price to pay, including premium surcharge if needed */
            $totalPrice = StripeUtils::SECTION_PRICE;

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
                "stripe_id" => $response->id,
                "price" => StripeUtils::SECTION_PRICE,
                "user_id" => $user->id,
                "purchasable_id" => $section->id,
                "purchasable_type" => "section"
            ]);
            $purchase->save();

            Log::log("user_payment_succeed_for_section", $user, [
                "loggable_id" => $section->id,
                "loggable_type" => "section"
            ]);

            return new JsonResponse(
                $user->with(["class_", "subscriptions", "purchases", "purchases.purchasable"])
                    ->find($user->id)
            );
        } else {
            Log::log("user_payment_failed_for_section", $user, [
                "loggable_id" => $section->id,
                "loggable_type" => "section"
            ]);

            return new JsonResponse([
                "status" => "failure"
            ], 400);
        }
    }
}
