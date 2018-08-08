<?php
namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Class Product
 *
 * @author Mathieu Bour <mathieu.tin.bour@ŋmail.com>
 * @package App\Models
 * @since
 * @copyright Mathrix Education SA
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string|null $image
 * @property string|null $type
 * @property string|null $description
 * @property float|null $price
 * @property bool $visible
 * @property int|null $module_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property array $link
 * @property-read Collection|Product[] $children
 * @property-read Collection|Product[] $parents
 * @method static Builder|Product whereId($value)
 * @method static Builder|Product whereName($value)
 * @method static Builder|Product whereSlug($value)
 * @method static Builder|Product whereImage($value)
 * @method static Builder|Product whereType($value)
 * @method static Builder|Product whereDescription($value)
 * @method static Builder|Product wherePrice($value)
 * @method static Builder|Product whereVisible($value)
 * @method static Builder|Product whereModuleId($value)
 * @method static Builder|Product whereCreatedAt($value)
 * @method static Builder|Product whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Product extends BaseModel
{
    /** @var array The attributes that are mass assignable */
    protected $fillable = ["name", "slug", "image", "type", "description", "price", "visible", "order", "module_id",
        "created_at", "updated_at"];
    protected $rules = [
        "name" => "required|max:255",
        "slug" => "required|max:255",
        "image" => "nullable|max:255",
        "type" => "nullable",
        "description" => "required|min:10",
        "price" => "nullable|numeric",
        "visible" => "required|boolean",
        "module_id" => "nullable|exists:modules,id"
    ];
    /** @var array The attributes that should be cast to native types */
    protected $casts = [
        "visible" => "boolean"
    ];
    /** @var bool Indicates if the model should be timestamped */
    public $timestamps = true;


    /**
     * BelongsTo Module
     *
     * @return BelongsTo
     */
    public function module()
    {
        return $this->belongsTo(Module::class);
    }


    /**
     * BelongsToMany Products
     *
     * @return BelongsToMany
     */
    public function parents()
    {
        return $this->belongsToMany(Product::class, "product_product", "child_id", "parent_id");
    }


    /**
     * BelongsToMany Products
     *
     * @return BelongsToMany
     */
    public function children()
    {
        return $this->belongsToMany(Product::class, "product_product", "parent_id", "child_id");
    }


    /**
     * BelongsToMany Discount
     *
     * @return BelongsToMany
     */
    public function discounts()
    {
        return $this->belongsToMany(Discount::class);
    }


    /**
     * Get the product price after the application of the given discount
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
        } else {
            return $this->price;
        }
    }
}
