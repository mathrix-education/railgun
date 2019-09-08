<?php

namespace App\Models;

use App\Models\Traits\NoPivot;
use App\Services\Braintree\Models\LineItem;
use Braintree\Transaction;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Mathrix\Lumen\Zero\Models\BaseModel;

/**
 * Class Product.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 1.0.0
 *
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string|null $image
 * @property string|null $type
 * @property string|null $description
 * @property float|null $price
 * @property bool $visible
 * @property bool $buyable
 * @property int|null $order
 * @property int|null $parent_id
 * @property int|null $module_id
 * @property int[]|null $pack_product_ids
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * ---
 * @property Module|null $module
 * @property Product|null $parent
 * @property EloquentCollection|Product[] $children
 * @property EloquentCollection|Product[] $items
 * @property EloquentCollection|Discount[] $discounts
 * @property EloquentCollection|LineItem[] $lineItems
 */
class Product extends BaseModel
{
    private const MAX_CHILDREN_DEPTH = 5;
    public const MODE_MACHINE_2019_END = "2019-07-31 00:00:00";
    use NoPivot;

    /** @var bool Indicates if the model should be timestamped */
    public $timestamps = true;
    /** @var array The attributes that are mass assignable */
    protected $fillable = [
        "name",
        "slug",
        "image",
        "type",
        "description",
        "price",
        "visible",
        "buyable",
        "order",
        "parent_id",
        "module_id"
    ];
    protected $rules = [
        "name" => "required|max:35",
        "slug" => "required|max:35",
        "image" => "nullable",
        "type" => "nullable",
        "description" => "nullable|max:127",
        "price" => "nullable|numeric",
        "visible" => "required|boolean",
        "order" => "required|integer",
        "module_id" => "nullable|exists:modules,id",
    ];
    /** @var array The attributes that should be cast to native types */
    protected $casts = [
        "visible" => "boolean",
        "buyable" => "boolean"
    ];
    protected $appends = ["owned"];


    /**
     * Deliver a product to a customer.
     *
     * @param User $customer The customer.
     * @param Transaction $transaction The Braintree transaction.
     *
     * @return Purchase|null
     * @throws \Exception
     */
    public function deliver(User $customer, ?Transaction $transaction = null): ?Purchase
    {
        if ($this->type === "pack") {
            $this->items->each(function (Product $item) use ($customer, $transaction) {
                $item->deliver($customer, $transaction);
            });
        } else if ($this->type === "premium") {
            // Premium product
            $customer->premium_bypass_expires_at = new Carbon(self::MODE_MACHINE_2019_END);
            $customer->save();

            return null;
        }

        $purchase = new Purchase([
            "transaction_id" => $transaction !== null ? $transaction->id : null,
            "user_id" => $customer->id,
            "product_id" => $this->id,
        ]);
        $purchase->save();

        return $purchase;
    }


    /**
     * Get the parent products of this product.
     *
     * @return Collection|Product[]
     */
    public function lineage(): Collection
    {
        $lineage = new Collection([$this]);

        $iterator = $this;

        while (
            $iterator->parent_id !== null
            && $lineage->pluck("id")->search($iterator->parent_id) === false
        ) {
            $iterator = $iterator->parent;

            if ($lineage->pluck("id")->contains($iterator->id)) {
                // Prevent endless loops
                break;
            }

            $lineage->push($iterator);
        }

        return $lineage->map(function (Product $product) {
            return $product->unsetRelation("parent");
        });
    }


    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */
    /**
     * BelongsTo Module.
     *
     * @return BelongsTo
     */
    public function module()
    {
        return $this->belongsTo(Module::class);
    }


    /**
     * BelongsTo parent Products.
     *
     * @return BelongsTo
     */
    public function parent()
    {
        return $this->belongsTo(Product::class, "parent_id");
    }


    /**
     * HasMany child Products.
     *
     * @return HasMany|BelongsToMany
     */
    public function children()
    {
        return $this->hasMany(Product::class, "parent_id")
            ->orderBy("order");
    }


    /**
     * BelongsToMany products.
     * This relation is only "Pack" typed products.
     *
     * @return BelongsToMany
     */
    public function items()
    {
        return $this->belongsToMany(
            Product::class,
            "product_product",
            "parent_id",
            "child_id"
        );
    }


    /**
     * BelongsToMany Discount.
     *
     * @return BelongsToMany
     */
    public function discounts(): BelongsToMany
    {
        return $this->belongsToMany(Discount::class);
    }


    /**
     * Product::$owned getter attribute
     *
     * @return bool
     */
    public function getOwnedAttribute(): bool
    {
        /** @var User $user */
        $user = Auth::user();

        return $user && ($user->hasScope("products:read") || $user->hasPurchased($this));
    }


    /**
     * Get the product price after the application of the given discount.
     *
     * @param Discount $discount
     *
     * @return float|int|null
     */
    public function getNewPrice(Discount $discount = null)
    {
        if ($discount === null) {
            return $this->price;
        } else if (!$discount->isApplicableOn($this)) {
            return $this->price;
        } else if ($discount->type === "percentage") {
            return $this->price * (1 - $discount->value / 100);
        } else if ($discount->type === "absolute") {
            return $this->price - $discount->value;
        }

        return $this->price;
    }
}
