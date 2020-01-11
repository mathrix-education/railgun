<?php

declare(strict_types=1);

namespace App\Models;

use Braintree\Transaction;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Mathrix\Lumen\Zero\Models\BaseModel;

/**
 * @property int                          $id
 * @property string                       $name
 * @property string                       $slug
 * @property string|null                  $image
 * @property string|null                  $type
 * @property string|null                  $description
 * @property float|null                   $price
 * @property bool                         $visible
 * @property bool                         $buyable
 * @property int|null                     $order
 * @property int|null                     $parent_id
 * @property int|null                     $module_id
 * @property int[]|null                   $pack_product_ids
 * @property Carbon|null                  $created_at
 * @property Carbon|null                  $updated_at
 * ---
 * @property Module|null                  $module
 * @property Product|null                 $parent
 * @property EloquentCollection|Product[] $children
 * @property EloquentCollection|Product[] $parents
 * @property EloquentCollection|Product[] $items
 */
class Product extends BaseModel
{
    public const MODE_MACHINE_2019_END = '2019-07-31 00:00:00';

    /** @var array The attributes that are mass assignable */
    protected $fillable = [
        'name',
        'slug',
        'image',
        'type',
        'description',
        'price',
        'visible',
        'buyable',
        'order',
        'parent_id',
        'module_id',
    ];
    protected $rules    = [
        'name'        => 'required|max:35',
        'slug'        => 'required|max:35',
        'image'       => 'nullable',
        'type'        => 'nullable',
        'description' => 'nullable|max:127',
        'price'       => 'nullable|numeric',
        'visible'     => 'required|boolean',
        'order'       => 'required|integer',
        'module_id'   => 'nullable|exists:modules,id',
    ];
    /** @var array The attributes that should be cast to native types */
    protected $casts   = [
        'visible' => 'boolean',
        'buyable' => 'boolean',
    ];
    protected $appends = ['owned'];

    /**
     * Deliver a product to a customer.
     *
     * @param User        $customer    The customer.
     * @param Transaction $transaction The Braintree transaction.
     *
     * @return Purchase|null
     *
     * @throws Exception
     */
    public function deliver(User $customer, ?Transaction $transaction = null): ?Purchase
    {
        if ($this->type === 'pack') {
            $this->items->each(static function (Product $item) use ($customer, $transaction): void {
                $item->deliver($customer, $transaction);
            });
        } elseif ($this->type === 'premium') {
            // Premium product
            $customer->premium_bypass_expires_at = new Carbon(self::MODE_MACHINE_2019_END);
            $customer->save();

            return null;
        }

        $purchase = new Purchase([
            'transaction_id' => $transaction !== null ? $transaction->id : null,
            'user_id'        => $customer->id,
            'product_id'     => $this->id,
        ]);
        $purchase->save();

        return $purchase;
    }

    /**
     * Get the parent products of this product.
     *
     * @param null $lineage The existing lineage.
     *
     * @return Collection|Product[]
     */
    public function lineage($lineage = null): Collection
    {
        if ($lineage === null) {
            $lineage = new Collection();
        }

        if ($lineage->pluck('id')
                ->search($this->id) === false
        ) {
            $lineage->push($this);
        }

        /** @var Collection $parents The parent products */
        $parents = $this->parents;

        if ($this->parent !== null) {
            $parents->push($this->parent);
        }

        foreach ($parents as $parent) {
            $lineage = $parent->lineage($lineage);
        }

        return $lineage->map(static function (Product $product) {
            return $product
                ->unsetRelation('parent')
                ->unsetRelation('parents');
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    /**
     * BelongsTo Module.
     */
    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }

    /**
     * BelongsTo parent Products.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /**
     * HasMany child Products.
     *
     * @return HasMany|BelongsToMany
     */
    public function children()
    {
        return $this->hasMany(self::class, 'parent_id')
            ->orderBy('order');
    }

    /**
     * BelongsToMany products.
     * This relation is only "Pack" typed products.
     */
    public function parents(): BelongsToMany
    {
        return $this->belongsToMany(
            self::class,
            'product_product',
            'child_id',
            'parent_id'
        );
    }

    /**
     * BelongsToMany products.
     * This relation is only "Pack" typed products.
     */
    public function items(): BelongsToMany
    {
        return $this->belongsToMany(
            self::class,
            'product_product',
            'parent_id',
            'child_id'
        );
    }

    /**
     * BelongsToMany User.
     *
     * @return BelongsToMany
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'purchases')
            ->as('purchase')
            ->using(Purchase::class)
            ->wherePivot('transaction_id')
            ->withTimestamps();
    }

    /**
     * BelongsToMany Discount.
     */
    public function discounts(): BelongsToMany
    {
        return $this->belongsToMany(Discount::class);
    }

    /**
     * Product::$owned getter attribute
     */
    public function getOwnedAttribute(): bool
    {
        /** @var User $user */
        $user = Auth::user();

        return $user && ($user->hasScope('products:read') || $user->hasPurchased($this));
    }

    /**
     * Get the product price after the application of the given discount.
     *
     * @return float|int|null
     */
    public function getNewPrice(?Discount $discount = null)
    {
        if ($discount === null) {
            return $this->price;
        }

        if (!$discount->isApplicableOn($this)) {
            return $this->price;
        }

        if ($discount->type === 'percentage') {
            return $this->price * (1 - $discount->value / 100);
        }

        if ($discount->type === 'absolute') {
            return $this->price - $discount->value;
        }

        return $this->price;
    }
}
