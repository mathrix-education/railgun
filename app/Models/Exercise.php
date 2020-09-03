<?php

declare(strict_types=1);

namespace Railgun\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Railgun\Models\Contracts\BelongsToChapter;
use Railgun\Models\Contracts\Ownable as OwnableContract;
use Railgun\Models\Traits\Clonable;
use Railgun\Models\Traits\Commentable;
use Railgun\Models\Traits\Freezable;
use Railgun\Models\Traits\Interactable;
use Railgun\Models\Traits\Labelable;
use Railgun\Models\Traits\Notable;
use Railgun\Models\Traits\Ownable;
use Railgun\Models\Traits\Reorderable;

/**
 * @property-read int     $id
 * @property      string  $type
 * @property      mixed[] $content
 * @property      int     $order
 * @property      bool    $premium
 * @property      bool    $protected
 * @property      bool    $visible
 * @property      int     $subject_id
 * @property-read Carbon  $created_at
 * @property-read Carbon  $updated_at
 * ---
 * @property-read Subject $subject
 */
class Exercise extends BaseModel implements BelongsToChapter, OwnableContract
{
    use Clonable;
    use Commentable;
    use Freezable;
    use Interactable;
    use Labelable;
    use Notable;
    use Ownable;
    use Reorderable;

    protected       $attributes = [
        'premium'   => false,
        'protected' => false,
        'visible'   => false,
    ];
    protected       $fillable   = [
        'content',
        'order',
        'visible',
        'subject_id',
    ];
    protected       $casts      = [
        'content'   => 'array',
        'premium'   => 'boolean',
        'protected' => 'boolean',
        'visible'   => 'boolean',
    ];
    protected array $orderGroup = ['subject_id'];

    public static function booting(): void
    {
        $premiumSync = static function (Exercise $exercise): void {
            // Set the premium state to the same as the parent subject
            $exercise->premium = $exercise->subject->premium;
        };

        self::creating($premiumSync);
        self::updating($premiumSync);
    }

    /**
     * @return BelongsTo<Subject>
     */
    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    /**
     * Get the model parent chapter.
     */
    public function getChapter(): Chapter
    {
        return $this->subject->notion->chapter;
    }
}
