<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Mathrix\Lumen\Zero\Models\BaseModel;

/**
 * @property int $id
 * @property string $name
 * @property string $name_short
 * @property string $slug
 * @property string $slug_short
 * @property bool $visible
 * ---
 * @property Collection|Grade[] $grades
 */
class Country extends BaseModel
{
    /** @var array The attributes that are mass assignable */
    protected $fillable = ['id', 'name', 'slug', 'visible'];
    /** @var array Validation rules */
    protected $rules = [
        'name' => 'required|max:255',
        'slug' => 'required|max:255',
        'visible' => 'boolean',
    ];
    protected $casts = ['visible' => 'boolean'];

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    /**
     * The country grades.
     */
    public function grades() : HasMany
    {
        return $this->hasMany(Grade::class);
    }
}
