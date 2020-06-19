<?php

declare(strict_types=1);

namespace Railgun\Console;

use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Relations\Relation;
use Stripe\StripeClient;

class StripePullCommand extends Command
{
    protected $signature = 'stripe:pull';

    public function handle(StripeClient $stripe)
    {
        //        $products = $stripe->products->all(['expand' => ['data.prices']]);
        //
        //        foreach ($products->autoPagingIterator() as $product) {
        //            VarDumper::dump($product->jsonSerialize());
        //        }
        $this->line(Relation::getMorphedModel('training'));
    }
}
