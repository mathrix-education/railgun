<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;

class Controller extends BaseController
{
    /**
     * Check if a request contains the needed inputs
     * @param Request $request the request
     * @param array $neededInputs the needed inputs
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkRequestInputs(Request $request, array $neededInputs) {
        $presentInputs = array_keys($request->all());
        $missingInputs = [];

        foreach ($neededInputs as $neededInput) {
            if(!in_array($neededInput, $presentInputs)) {
                $missingInputs[] = $neededInput;
            }
        }

        if(!empty($missingInputs)) {
            return response()->json([
                'error' => 'missing_input',
                'message' => 'Missing inputs: ' . implode(', ', $missingInputs)
            ], 400);
        }
    }
}
