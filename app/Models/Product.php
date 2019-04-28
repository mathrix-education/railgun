<?php

namespace App\Models;

use App\Models\Traits\NoPivot;
use Braintree\Transaction;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;
use Mathrix\Lumen\Bases\BaseModel;

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
 * @property Collection|Product[] $children
 * @property Collection|Product[] $items
 * @property Collection|Discount[] $discounts
 */
class Product extends BaseModel
{
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
     * @return Purchase
     */
    public function deliver(User $customer, ?Transaction $transaction): Purchase
    {
        if ($this->type === "pack") {
            $this->items->each(function (Product $item) use ($customer, $transaction) {
                $item->deliver($customer, $transaction);
            });
        } else if ($this->type === "premium") {
            // Premium product
            $customer->premium_bypass_expires_at = new Carbon(self::MODE_MACHINE_2019_END);
            $customer->save();
        }

        $this->children->each(function (Product $child) use ($customer, $transaction) {
            $child->deliver($customer, $transaction);
        });

        $purchase = new Purchase([
            "transaction_id" => $transaction !== null ? $transaction->id : null,
            "user_id" => $customer->id,
            "product_id" => $this->id,
        ]);
        $purchase->save();

        return $purchase;
    }


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
        return $this->belongsTo(self::class, "parent_id");
    }


    /**
     * HasMany child Products.
     *
     * @return HasMany|BelongsToMany
     */
    public function children()
    {
        return $this->hasMany(self::class, "parent_id")
            ->orderBy("order");
    }


    /**
     * BelongsToMany products
     *
     * @return BelongsToMany
     */
    public function items()
    {
        return $this->belongsToMany(
            self::class,
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
    public function discounts()
    {
        return $this->belongsToMany(Discount::class);
    }


    /**
     * Product::owned attribute
     *
     * @return bool
     */
    public function getOwnedAttribute()
    {
        $user = Auth::user();

        if ($user !== null && $user instanceof User) {
            return $user->hasPurchased($this);
        }

        return false;
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
