<?php

declare(strict_types=1);

namespace Railgun\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Railgun\Billing\Contracts\Sellable as SellableContract;
use Railgun\Models\Traits\Content;
use Railgun\Models\Traits\Randomizable;
use Railgun\Models\Traits\Reorderable;
use Railgun\Models\Traits\SaveWithoutEvents;
use Railgun\Models\Traits\Sluggable;
use Railgun\Billing\Models\Sellable;

/**
 * A chapter is the base wrapper of all the Mathrix Education content.
 *
 * @property-read int                    $id
 * @property      string                 $name
 * @property      string                 $slug
 * @property      int                    $order
 * @property      bool                   $visible
 * @property      int                    $theme_id
 * @property-read Carbon                 $created_at
 * @property-read Carbon                 $updated_at
 * ---
 * @property-read Theme                  $theme
 * @property-read Collection|Notion[]    $notions
 * @property-read Collection|Flashcard[] $flashcards
 * @property-read Collection|Exam[]      $exams
 * @property-read Collection|Session[]   $sessions
 */
class Chapter extends Model implements Content, SellableContract
{
    use Randomizable;
    use Reorderable;
    use SaveWithoutEvents;
    use Sellable;
    use Sluggable;

    protected       $fillable   = [
        'name',
        'slug',
        'order',
        'visible',
        'theme_id',
    ];
    protected       $casts      = ['visible' => 'boolean'];
    protected array $orderGroup = ['theme_id'];

    /**
     * @return BelongsTo<Theme>
     */
    public function theme(): BelongsTo
    {
        return $this->belongsTo(Theme::class);
    }

    /**
     * @return HasMany<Notion>
     */
    public function notions(): HasMany
    {
        return $this->hasMany(Notion::class)
            ->where('type', '=', Notion::TYPE_STANDARD)
            ->orderBy('order');
    }

    /**
     * @return HasOne<Notion>
     */
    public function summary(): HasOne
    {
        return $this->hasOne(Notion::class)
            ->where('type', '=', Notion::TYPE_SUMMARY);
    }

    /**
     * @return HasMany<Flashcard>
     */
    public function flashcards(): HasMany
    {
        return $this->hasMany(Flashcard::class)->orderBy('order');
    }

    /**
     * @return HasMany<Exam>
     */
    public function exams(): HasMany
    {
        return $this->hasMany(Exam::class)->orderBy('order');
    }

    /**
     * @return HasMany<Session>
     */
    public function sessions(): HasMany
    {
        return $this->hasMany(Session::class);
    }

    /**
     * Get the model parent chapter.
     */
    public function getChapter(): Chapter
    {
        return $this;
    }
}
