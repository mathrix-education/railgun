<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Validator;
use Laravel\Lumen\Routing\Controller;
use App\Http\HttpJsonResponseBuilderTrait;

/**
 * Class BaseController
 * Add a few methods to all controllers
 * @author Mathrix Team
 * @package App\Http\Controllers
 */
abstract class BaseController extends Controller
{
    use HttpJsonResponseBuilderTrait;

    /**
     * Check if a request contains the needed inputs
     *
     * @param Request $request the request
     * @param array $requiredInputs the required inputs
     */
    public function validateRequire(Request $request, array $requiredInputs): void
    {
        $this->validate($request, array_fill_keys($requiredInputs, 'required'));
    }

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
}
