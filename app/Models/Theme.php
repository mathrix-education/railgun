<?php

declare(strict_types=1);

namespace Railgun\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Railgun\Models\Traits\Reorderable;
use function collect;

/**
 * @property-read int                  $id
 * @property      string               $name
 * @property      int                  $order
 * @property      bool                 $visible
 * @property      int                  $discipline_id
 * @property      int                  $grade_id
 * @property-read Carbon               $created_at
 * @property-read Carbon               $updated_at
 * ---
 * @property-read Grade                $grade
 * @property-read Discipline           $discipline
 * @property-read Collection|Chapter[] $chapters
 */
class Theme extends BaseModel
{
    use Reorderable;

    protected       $fillable   = [
        'name',
        'order',
        'visible',
        'grade_id',
        'discipline_id',
    ];
    protected       $casts      = ['visible' => 'boolean'];
    protected array $orderGroup = ['discipline_id', 'grade_id'];

    /**
     * @return BelongsTo<Grade>
     */
    public function grade(): BelongsTo
    {
        return $this->belongsTo(Grade::class);
    }

    /**
     * @return BelongsTo<Discipline>
     */
    public function discipline(): BelongsTo
    {
        return $this->belongsTo(Discipline::class);
    }

    /**
     * @return BelongsToMany<Chapter>
     */
    public function chapters(): BelongsToMany
    {
        return $this->belongsToMany(Chapter::class)->orderBy('order');
    }

    /**
     * @param mixed[] $links The chapters links
     * @param int     $delta The order delta (default "0", used for syncWithoutDetaching)
     *
     * @return mixed[] The Laravel "sync" data.
     */
    public function parseChapterLinks(array $links, int $delta = 0): array
    {
        return collect($links)
            ->mapWithKeys(static function (string $id, int $order) use ($delta) {
                return [
                    $id => ['order' => $order + $delta],
                ];
            })
            ->toArray();
    }
}
