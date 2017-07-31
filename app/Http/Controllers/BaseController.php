<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Validator;
use Laravel\Lumen\Routing\Controller;

/**
 * Abstract Class BaseController
 * @author Mathrix Team
 * @package App\Http\Controllers
 */
abstract class BaseController extends Controller
{
    /**
     * Override validation response format
     *
     * @param Validator $validator
     *
     * @return array
     */
    protected function formatValidationErrors(Validator $validator)
    {
        return [
            'error' => 'validation',
            'message' => [
                $validator->getMessageBag()->toArray()
            ]
        ];
    }

    /**
     * Check if a request contains the needed inputs
     *
     * @param Request $request the request
     * @param array $requiredInputs the required inputs
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function validateRequire(Request $request, array $requiredInputs)
    {
        $validationRules = [];

        foreach ($requiredInputs as $requiredInput) {
            $validationRules[$requiredInput] = 'required';
        }

        $this->validate($request, $validationRules);
    }
}
