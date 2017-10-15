<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;


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
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Comment[] $comments
 * @mixin \Eloquent
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Log[] $logs
 */
class Module extends BaseModel
{
    /** @var array The fillable attributes */
    protected $fillable = ['name', 'slug', 'description', 'type', 'content', 'questions', 'choices', 'solutions',
        'corrections', 'options'];

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
        'type' => 'required|in:course,fill,match,mcq,mcq-table,test,flashcard'
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
        return $this->belongsToMany(Activity::class);
    }


    /**
     * MorphMany Comment
     *
     * @return MorphMany
     */
    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }


    /**
     * Get all of the post's logs.
     */
    public function logs()
    {
        return $this->morphMany(Log::class, 'loggable');
    }
}