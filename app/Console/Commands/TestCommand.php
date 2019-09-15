<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\User;
use Mathrix\Lumen\JWT\Auth\JWT;
use Mathrix\Lumen\Zero\Console\Commands\BaseCommand;

/**
 * Test command which is mainly used as a sandbox during development.
 * The content of the TestCommand::handle() may be kept empty.
 */
class TestCommand extends BaseCommand
{
    /** @var string The command signature. */
    protected $signature = 'test';

    public function handle(): void
    {
        $user = User::findByOrFail("id", 155);
        JWT::actingAs($user);
        $products = Product::query()
            ->where([
                ["buyable", "=", true],
                ["type", "=", "training"]
            ])
            ->get()
            ->toArray();
        dd($products);
    }
}
