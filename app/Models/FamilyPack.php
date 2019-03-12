<?php

namespace App\Models;

use App\Exceptions\Http400InvalidRequestException;
use App\Exceptions\Http401UnauthorizedException;
use App\Exceptions\Http422ModelSaveException;
use App\Exceptions\Http422ModelValidationException;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class FamilyPack.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @author    Jérémie Levain <munezero999@live.fr>
 * @since     4.1.0
 * @copyright Mathrix Education SA
 * @package   App\Models
 *
 * @property int $id
 * @property string $braintree_id
 * @property int $parent_id
 * @property int $children_count
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property User $parent
 * @property Collection|User[] $children
 */
class FamilyPack extends BaseModel
{
    public $timestamps = true;
    /** @var array The attributes that are mass assignable */
    protected $fillable = ["braintree_id", "children_count", "parent_id", "created_at", "updated_at"];

    /*
    |--------------------------------------------------------------------------
    | Methods
    |--------------------------------------------------------------------------
    */
    /**
     * BelongsTo User.
     *
     * @return BelongsTo
     */
    public function parent()
    {
        return $this->belongsTo(User::class, "parent_id");
    }


    /**
     * Check if the family pack is active
     *
     * @return bool
     */
    public function isActive()
    {
        if (!app()->environment("master") && $this->braintree_id === "sub_mock_valid") {
            return true;
        } else {
            return $this->parent->premium;
        }
    }


    /**
     * Add a child to the family pack
     *
     * @param User $child
     *
     * @return bool
     * @throws Http400InvalidRequestException
     * @throws Http401UnauthorizedException
     * @throws Http422ModelSaveException
     * @throws Http422ModelValidationException
     */
    public function addChild(User $child): bool
    {
        $childrenCount = $this->children()->count();

        if ($childrenCount >= $this->children_count) {
            throw new Http401UnauthorizedException("This family pack is already full.");
        }

        if ($child->family_pack_id !== null) {
            throw new Http400InvalidRequestException("User with `id` is already a member of a family pack.");
        }

        $child->family_pack_id = $this->id;

        return $child->save();
    }


    /**
     * BelongsToMany User (children)
     *
     * @return HasMany
     */
    public function children()
    {
        return $this->hasMany(User::class);
    }


    /**
     * Remove a child from this family pack
     *
     * @param User $child
     *
     * @return bool
     * @throws Http400InvalidRequestException
     * @throws Http422ModelSaveException
     * @throws Http422ModelValidationException
     */
    public function removeChild(User $child): bool
    {
        if ($this->children()->where("id", "=", $child->id)->exists()) {
            $child->family_pack_id = null;

            return $child->save();
        } else {
            throw new Http400InvalidRequestException("User with `id` {$child->id} is not a member of the " .
                "family-pack with `id` {$this->id}");
        }
    }
}
