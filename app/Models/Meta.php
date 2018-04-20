<?php
namespace App\Models;


use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\Meta
 *
 * @property int $id
 * @property bool $favorite
 * @property bool $to_revise
 * @property bool $completed
 * @property int $user_id
 * @property int|null $country_id
 * @property int|null $class_id
 * @property int|null $discipline_id
 * @property int|null $chapter_id
 * @property int|null $notion_id
 * @property int|null $activity_id
 * @property int|null $module_id
 * @property-read User $user
 * @property-read Country $country
 * @property-read Discipline $discipline
 * @property-read Class_|null $class_
 * @property-read Chapter $chapter
 * @property-read Notion $notion
 * @property-read Activity $activity
 * @property-read Module $module
 * @method static Builder|Meta whereId($value)
 * @method static Builder|Meta whereFavorite($value)
 * @method static Builder|Meta whereToRevise($value)
 * @method static Builder|Meta whereCompleted($value)
 * @method static Builder|Meta whereUserId($value)
 * @method static Builder|Meta whereCountryId($value)
 * @method static Builder|Meta whereDisciplineId($value)
 * @method static Builder|Meta whereNotionId($value)
 * @method static Builder|Meta whereChapterId($value)
 * @method static Builder|Meta whereClassId($value)
 * @method static Builder|Meta whereActivityId($value)
 * @method static Builder|Meta whereModuleId($value)
 * @method static Builder|Meta whereCreatedAt($value)
 * @method static Builder|Meta whereUpdatedAt($value)
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class Meta extends BaseModel
{
    public $fillable = ["id", "favorite", "to_revise", "completed", "user_id", "country_id", "class_id", "discipline_id",
        "chapter_id", "notion_id", "activity_id", "module_id"];
    /** @var bool Indicates if the model should be timestamped */
    public $timestamps = true;
    protected $casts = [
        "favorite" => "boolean",
        "to_revise" => "boolean",
        "completed" => "boolean"
    ];
    /** @var array The validation rules on create */
    protected $rules = [
        "favorite" => "boolean",
        "to_revise" => "boolean",
        "completed" => "boolean",
        "user_id" => "required|exists:users,id",
        "country_id" => "required|exists:countries,id",
        "class_id" => "required|exists:classes,id",
        "discipline_id" => "required|exists:disciplines,id",
        "chapters_id" => "required|exists:chapters,id",
        "notion_id" => "required|exists:notions,id",
        "activity_id" => "required|exists:activities,id",
        "module_id" => "nullable|exists:modules,id"
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
        return $this->belongsTo(Class_::class, "class_id");
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


    /**
     * BelongsTo Module
     *
     * @return BelongsTo
     */
    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }
}
