<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\Meta
 *
 * @property int $id
 * @property int $user_id
 * @property int $country_id
 * @property int $discipline_id
 * @property int $notion_id
 * @property int $activity_id
 * @property bool $favorite
 * @property string|null $status
 * @property-read User $user
 * @property-read Country $country
 * @property-read Chapter $chapter
 * @property-read Notion $notion
 * @property-read Activity $activity
 * @property-read Discipline $discipline
 * @method static Builder|Meta whereId($value)
 * @method static Builder|Meta whereFavorite($value)
 * @method static Builder|Meta whereStatus($value)
 * @method static Builder|Meta whereUserId($value)
 * @method static Builder|Meta whereCountryId($value)
 * @method static Builder|Meta whereDisciplineId($value)
 * @method static Builder|Meta whereNotionId($value)
 * @method static Builder|Meta whereActivityId($value)
 * @mixin \Eloquent
 */
class Meta extends BaseModel
{
    public $fillable = ['status', 'favorite', 'user_id', 'country_id', 'class_id', 'discipline_id', 'chapter_id',
        'notion_id', 'activity_id'];

    /** @var array The validation rules on create */
    protected $rules = [
        'user_id' => 'required|email|unique:|max:60',
        'country_id' => 'nullable|exists:countries,id',
        'class_id' => 'nullable|exists:classes,id',
        'discipline_id' => 'nullable|exists:disciplines,id',
        'chapters_id' => 'nullable|exists:chapters,id',
        'notion_id' => 'nullable|exists:notions,id',
        'activity_id' => 'nullable|exists:activities,id'
    ];
    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */
    /**
     * BelongsTo User
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }


    /**
     * BelongsTo Country
     *
     * @return BelongsTo
     */
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }


    /**
     * BelongsTo Class_
     *
     * @return BelongsTo
     */
    public function class_(): BelongsTo
    {
        return $this->belongsTo(Class_::class, 'class_id');
    }


    /**
     * BelongsTo Discipline
     *
     * @return BelongsTo
     */
    public function discipline(): BelongsTo
    {
        return $this->belongsTo(Discipline::class);
    }


    /**
     * BelongsTo Chapter
     *
     * @return BelongsTo
     */
    public function chapter(): BelongsTo
    {
        return $this->belongsTo(Chapter::class);
    }


    /**
     * BelongsTo Notion
     *
     * @return BelongsTo
     */
    public function notion(): BelongsTo
    {
        return $this->belongsTo(Notion::class);
    }


    /**
     * BelongsTo Activity
     *
     * @return BelongsTo
     */
    public function activity(): BelongsTo
    {
        return $this->belongsTo(Activity::class);
    }
}