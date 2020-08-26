<?php

declare(strict_types=1);

namespace Railgun\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Railgun\Billing\Stripe\Contracts\StripeSellable as StripeSellableContract;
use Railgun\Billing\Stripe\Models\StripeSellable;
use Railgun\Models\Contracts\BelongsToChapter;
use Railgun\Models\Traits\Acquerable;
use Railgun\Models\Traits\Ownable;
use Railgun\Models\Traits\Sellable;
use Railgun\Models\Traits\Sluggable;

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
 * @property-read Collection|Theme[]     $themes
 * @property-read Collection|Notion[]    $notions
 * @property-read Collection|Flashcard[] $flashcards
 * @property-read Collection|Exam[]      $exams
 * @property-read Collection|Session[]   $sessions
 */
class Chapter extends BaseModel implements BelongsToChapter, StripeSellableContract
{
    use Acquerable;
    use Ownable;
    use Sellable;
    use Sluggable;
    use StripeSellable;

    protected       $fillable   = [
        'name',
        'slug',
        'price',
        'order',
        'visible',
        'theme_id',
    ];
    protected       $casts      = ['visible' => 'boolean'];
    protected array $orderGroup = ['theme_id'];

    /**
     * @return BelongsToMany<Theme>
     */
    public function themes(): BelongsToMany
    {
        return $this->belongsToMany(Theme::class)
            ->withPivot('order')
            ->orderBy('pivot_order');
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
