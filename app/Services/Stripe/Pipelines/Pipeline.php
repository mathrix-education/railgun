<?php

namespace App\Services\Stripe\Pipelines;

use App\Models\User;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\ProvidesConvenienceMethods;
use Mathrix\Lumen\Zero\Controllers\Traits\HasRequestValidator;

abstract class Pipeline
{
    use ProvidesConvenienceMethods, HasRequestValidator {
        HasRequestValidator::validate insteadof ProvidesConvenienceMethods;
    }

    /** @var Request $request */
    protected $request;
    /** @var User $user */
    protected $user;


    public function __construct()
    {
        $this->request = app(Request::class);
        $this->user = $this->request->user();
    }


    public final function flow()
    {
        $this->initialize();
        $this->exchange();
        $this->finalize();

        return $this;
    }


    protected abstract function initialize(): void;


    protected abstract function exchange(): void;


    protected abstract function finalize(): void;
}
