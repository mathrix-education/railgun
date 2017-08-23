<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Mathrix\Lumen\Models\BaseModel;

/**
 * App\Models\Module
 *
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string $description
 * @property string $type
 * @property string $content
 * @property array $questions
 * @property array $choices
 * @property array $solutions
 * @property array $corrections
 * @property string $options
 * @property-read Collection|Activity[] $activities
 * @method static Builder|Module whereChoices($value)
 * @method static Builder|Module whereContent($value)
 * @method static Builder|Module whereCorrections($value)
 * @method static Builder|Module whereDescription($value)
 * @method static Builder|Module whereId($value)
 * @method static Builder|Module whereName($value)
 * @method static Builder|Module whereOptions($value)
 * @method static Builder|Module whereQuestions($value)
 * @method static Builder|Module whereSlug($value)
 * @method static Builder|Module whereSolutions($value)
 * @method static Builder|Module whereType($value)
 */
class Module extends BaseModel
{
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'questions' => 'array',
        'choices' => 'array',
        'solutions' => 'array',
        'corrections' => 'array',
        'options' => 'array'
    ];

    /** @var array The validation rules */
    protected $rules = [
        'name' => 'required|max:255',
        'slug' => 'required|max:255',
        'description' => 'required|max:255',
        'type' => 'required|in:course,fill,match,mcq,mcq-table,test,video'
    ];


    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */
    /**
     * BelongsToMany Activity
     *
     * @return BelongsToMany
     */
    public function activities(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\Activity');
    }
}