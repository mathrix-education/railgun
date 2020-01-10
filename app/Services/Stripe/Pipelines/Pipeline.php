<?php

declare(strict_types=1);

namespace App\Services\Stripe\Pipelines;

use App\Models\User;
use Illuminate\Http\Request;
use Mathrix\Lumen\Zero\Controllers\Traits\HasRequestValidator;
use function app;

abstract class Pipeline extends AnonymousPipeline
{
    use HasRequestValidator;

    /** @var Request $request */
    protected $request;
    /** @var User $user */
    protected $user;

    public function __construct()
    {
        $this->request = app(Request::class);
        $this->user    = $this->request->user();
    }

    final public function flow(): Pipeline
    {
        $this->initialize();
        $this->exchange();
        $this->finalize();

        return $this;
    }

    abstract protected function initialize(): void;

    abstract protected function exchange(): void;

    abstract protected function finalize(): void;
}
