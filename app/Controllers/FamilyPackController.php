<?php
namespace App\Controllers;

use App\Exceptions\Http400InvalidRequestException;
use App\Exceptions\Http401UnauthorizedException;
use App\Exceptions\Http422ModelSaveException;
use App\Exceptions\Http422ModelValidationException;
use App\Models\FamilyPack;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * Class FamilyPackController
 *
 * @author    Mathieu Bour <mathieu.tin.bour@ŋmail.com>
 * @package   App\Controllers
 * @since     4.1.1
 * @copyright Mathrix Education SA
 */
class FamilyPackController extends BaseController
{
    /**
     * Get a family pack with the parent and its children.
     *
     * @param Request    $request
     * @param int|string $key
     * @param null       $value
     *
     * @return JsonResponse
     * @throws Http401UnauthorizedException
     */
    public function get(Request $request, $key, $value = null): JsonResponse
    {
        $familyPack = FamilyPack::with(["parent", "children"])
            ->where("id", $key)
            ->firstOrFail();

        if ($request->user() !== null && $request->user()
                ->cant("get", $familyPack)) {
            throw new Http401UnauthorizedException();
        }

        $response = new JsonResponse($familyPack);
        return $response;
    }

    /**
     * @param Request $request
     * @param int     $familyPackId
     * @param string  $action
     *
     * @return FamilyPack
     * @throws Http400InvalidRequestException
     * @throws Http401UnauthorizedException
     * @throws Http422ModelSaveException
     * @throws Http422ModelValidationException
     * @throws ValidationException
     */
    private function manageChildren(Request $request, int $familyPackId, string $action)
    {
        $this->validate($request, [
            "email" => "required|exists:users,email"
        ]);

        /** @var User $parent */
        $parent = $request->user();
        $familyPack = FamilyPack::findOrFail($familyPackId);

        $parent->canOrFail("addChild", $familyPack);

        $childEmail = $request->input("email");
        $child = User::where("email", "=", $childEmail)->firstOrFail();

        switch ($action) {
            case "add":
                $familyPack->addChild($child);
                break;
            case "remove":
                $familyPack->removeChild($child);
                break;
        }

        $familyPack = FamilyPack::with(["parent", "children"])
            ->findOrFail($familyPack->id);

        return $familyPack;
    }

    /**
     * Add a child to an existing family pack
     *
     * @param Request $request
     * @param int     $familyPackId
     *
     * @return JsonResponse
     * @throws ValidationException
     * @throws Http400InvalidRequestException
     * @throws Http401UnauthorizedException
     * @throws Http422ModelSaveException
     * @throws Http422ModelValidationException
     */
    public function addChild(Request $request, int $familyPackId)
    {
        $familyPack = $this->manageChildren($request, $familyPackId, "add");
        $response = new JsonResponse($familyPack);

        return $response;
    }

    /**
     * Remove a child from an existing family pack.
     *
     * @param Request $request
     * @param int     $familyPackId
     *
     * @return JsonResponse
     * @throws ValidationException
     * @throws Http400InvalidRequestException
     * @throws Http401UnauthorizedException
     * @throws Http422ModelSaveException
     * @throws Http422ModelValidationException
     */
    public function removeChild(Request $request, int $familyPackId)
    {
        $familyPack = $this->manageChildren($request, $familyPackId, "remove");
        $response = new JsonResponse($familyPack);

        return $response;
    }
}
