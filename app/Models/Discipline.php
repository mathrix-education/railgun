<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\NoPivot;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Mathrix\Lumen\Zero\Models\BaseModel;

/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property bool $beta
 * ---
 * @property Collection|Grade[] $grades
 * @property Collection|Chapter[] $chapters
 */
class Discipline extends BaseModel
{
    use NoPivot;

    /** @var array The attributes that are mass assignable */
    protected $fillable = ['id', 'name', 'slug', 'beta'];
    /** @var array Validation rules */
    protected $rules = [
        'name' => 'required|max:255',
        'slug' => 'required|max:255',
        'beta' => 'boolean',
    ];
    protected $casts = [
        'visible' => 'boolean',
        'beta' => 'boolean',
    ];


    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    /**
     * BelongsToMany Grade.
     */
    public function grades() : BelongsToMany
    {
        return $this->belongsToMany(Grade::class, 'chapters')
            ->groupBy(['grade_id', 'discipline_id']);
    }

    /**
     * HasMany Chapter.
     */
    public function chapters() : HasMany
    {
        return $this->hasMany(Chapter::class);
    }
}
