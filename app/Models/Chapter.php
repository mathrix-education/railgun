<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Mathrix\Lumen\Zero\Models\BaseModel;

/**
 * @property-read  int           $id
 * @property string              $name
 * @property string              $slug
 * @property int                 $order
 * @property bool                $visible
 * @property int                 $discipline_id
 * @property int                 $grade_id
 * @property-read Carbon         $created_at
 * @property-read Carbon         $updated_at
 * ---
 * @property Grade               $grade
 * @property Discipline          $discipline
 * @property Collection|Notion[] $notions
 */
class Chapter extends BaseModel
{
    /** @var array The attributes that are mass assignable */
    protected $fillable = ['name', 'slug', 'order', 'visible', 'grade_id', 'discipline_id'];

    /** @var array Validation rules */
    protected $rules = [
        'name'          => 'required|max:255',
        'slug'          => 'max:255',
        'grade_id'      => 'required|exists:grades,id',
        'discipline_id' => 'required|exists:disciplines,id',
        'visible'       => 'boolean',
    ];
    protected $casts = ['visible' => 'boolean'];

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    /**
     * BelongsTo Grade.
     */
    public function grade(): BelongsTo
    {
        return $this->belongsTo(Grade::class);
    }

    /**
     * BelongsTo Discipline.
     */
    public function discipline(): BelongsTo
    {
        return $this->belongsTo(Discipline::class);
    }

    /**
     * The chapter notions.
     */
    public function notions(): HasMany
    {
        return $this->hasMany(Notion::class)->orderBy('order');
    }

    public function courseNotions(): HasMany
    {
        return $this->notions()->where('type', '=', 'course');
    }

    public function subjectNotions(): HasMany
    {
        return $this->notions()->where('type', '=', 'subject');
    }
}
