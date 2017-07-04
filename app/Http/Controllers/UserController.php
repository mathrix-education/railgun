<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

/**
 * Class UserController
 * @package App\Http\Controllers
 */
class UserController extends Controller
{
    public function register(Request $request)
    {

    }

    public function login(Request $request)
    {
        $inputs = $request->all();

        $user = null;


        if (isset($inputs['username']) && $inputs['grant_type'] == 'password') {
            $user = User::where('email', $inputs['username'])->first();
            if ($user instanceof User) {
                return app()->dispatch($request->create('/oauth/token', 'post', $inputs));
            } else {
                return response()->json([
                    'message' => 'User not found'
                ], 404);
            }
        } else {
            return response()->json([], 400);
        }

    }

    public function logout(Request $request)
    {

    }

    public function update(Request $request, int $id)
    {

    }

    public function settings(Request $request, int $id)
    {

    }
}