<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class Path.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @since     4.2.0
 * @copyright Mathrix Education SA
 *
 * @property int        $id
 * @property string     $content
 * @property int        $country_id
 * @property int        $class_id
 * @property int        $discipline_id
 * @property int        $chapter_id
 * @property int        $notion_id
 * @property int        $activity_id
 * @property int        $module_id
 * @property bool       $seo_index
 *
 * @property Country    $country
 * @property Class_     $class
 * @property Discipline $discipline
 * @property Chapter    $chapter
 * @property Notion     $notion
 * @property Activity   $activity
 * @property Module     $module
 */
class Path extends BaseModel
{
    protected $guarded = [];
    protected $rules = [
        "country_id" => "required|exists:countries,id",
        "class_id" => "nullable|exists:classes,id",
        "discipline_id" => "nullable|exists:disciplines,id",
        "chapter_id" => "nullable|exists:chapters,id",
        "notion_id" => "nullable|exists:notions,id",
        "activity_id" => "nullable|exists:activities,id",
        "module_id" => "nullable|exists:modules,id"
    ];
    protected $casts = [
        "seo_index" => "boolean"
    ];

    /**
     * @param array $contentIds The array containing the key/value
     *
     * @return Path
     */
    public static function retrieve(array $contentIds)
    {
        $wheres = [];
        foreach ($contentIds as $k => $v) {
            $wheres[] = [$k, "=", $v];
        }

        /** @var Path $path */
        $path = self::where($wheres)->firstOrCreate($contentIds);

        return $path;
    }

    /**
     * @param int $countryId
     * @param int $classId
     * @param int $disciplineId
     * @param int $chapterId
     * @param int $notionId
     * @param int $activityId
     *
     * @return bool
     */
    public static function isIndexed(int $countryId, int $classId, int $disciplineId, int $chapterId, int $notionId,
                                     int $activityId)
    {
        $path = self::where([
            ["country_id", "=", $countryId],
            ["class_id", "=", $classId],
            ["discipline_id", "=", $disciplineId],
            ["chapter_id", "=", $chapterId],
            ["notion_id", "=", $notionId],
            ["activity_id", "=", $activityId]
        ])
            ->whereNull("module_id")
            ->first();

        return $path !== null && $path->seo_index;
    }

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */
    /**
     * BelongsTo Country.
     *
     * @return BelongsTo
     */
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    /**
     * BelongsTo Class_.
     *
     * @return BelongsTo
     */
    public function class_(): BelongsTo
    {
        return $this->belongsTo(Class_::class, "class_id");
    }

    /**
     * BelongsTo Discipline.
     *
     * @return BelongsTo
     */
    public function discipline(): BelongsTo
    {
        return $this->belongsTo(Discipline::class);
    }

    /**
     * BelongsTo Chapter.
     *
     * @return BelongsTo
     */
    public function chapter(): BelongsTo
    {
        return $this->belongsTo(Chapter::class);
    }

    /**
     * BelongsTo Notion.
     *
     * @return BelongsTo
     */
    public function notion(): BelongsTo
    {
        return $this->belongsTo(Notion::class);
    }

    /**
     * BelongsTo Activity.
     *
     * @return BelongsTo
     */
    public function activity(): BelongsTo
    {
        return $this->belongsTo(Activity::class);
    }

    /**
     * BelongsTo Module.
     *
     * @return BelongsTo
     */
    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }
}
