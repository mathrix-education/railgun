<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class Training
 *
 * @author Mathrix Back-End Team
 * @package App\Models
 * @since 1.0.1
 * @copyright Mathrix SA
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string $description
 * @property string|null $created_at
 * @property string|null $updated_at
 * @method static Builder|Training whereId($value)
 * @method static Builder|Training whereName($value)
 * @method static Builder|Training whereSlug($value)
 * @method static Builder|Training whereDescription($value)
 * @method static Builder|Training whereCreatedAt($value)
 * @method static Builder|Training whereUpdatedAt($value)
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Section[] $sections
 */
class Training extends BaseModel
{
    /** @var array The guarded attributes */
    protected $guarded = [];


    /** @var array The validation rules on create */
    protected $rules = [
        "name" => "required|max:255",
        "slug" => "required|max:255",
        "description" => "nullable",
    ];

    /**
     * HasMany Section
     * @return HasMany
     */
    public function sections(): HasMany
    {
        return $this->hasMany(Section::class);
    }
}
