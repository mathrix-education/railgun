<?php

declare(strict_types=1);

namespace Railgun\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Railgun\Models\Traits\Commentable;
use Railgun\Models\Traits\Content;
use Railgun\Models\Traits\Freezable;
use Railgun\Models\Traits\Interactable;
use Railgun\Models\Traits\Labelable;
use Railgun\Models\Traits\Lockable;
use Railgun\Models\Traits\Notable;
use Railgun\Models\Traits\Randomizable;
use Railgun\Models\Traits\Reorderable;

/**
 * @property-read int     $id
 * @property      string  $type
 * @property      array   $questions
 * @property      array   $choices
 * @property      array   $solutions
 * @property      array   $explanations
 * @property      int     $order
 * @property      bool    $premium
 * @property      bool    $visible
 * @property      int     $subject_id
 * @property-read Carbon  $created_at
 * @property-read Carbon  $updated_at
 * ---
 * @property-read Subject $subject
 */
class Exercise extends Model implements Content
{
    use Commentable;
    use Freezable;
    use Interactable;
    use Labelable;
    use Lockable;
    use Notable;
    use Randomizable;
    use Reorderable;

    protected       $attributes = ['visible' => false];
    protected       $fillable   = [
        'type',
        'questions',
        'choices',
        'solutions',
        'explanations',
        'order',
        'visible',
        'subject_id',
    ];
    protected       $casts      = [
        'questions'    => 'array',
        'choices'      => 'array',
        'solutions'    => 'array',
        'explanations' => 'array',
        'premium'      => 'boolean',
        'visible'      => 'boolean',
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
     * @return mixed[] The potentially locked "questions" field.
     */
    public function getQuestionsAttribute(): array
    {
        return $this->lock('questions', []);
    }

    /**
     * @return mixed[] The potentially locked "choices" field.
     */
    public function getChoicesAttribute(): array
    {
        return $this->lock('choices', []);
    }

    /**
     * @return mixed[] The potentially locked "solutions" field.
     */
    public function getSolutionsAttribute(): array
    {
        return $this->lock('solutions', []);
    }

    /**
     * @return mixed[] The potentially locked "explanations" field.
     */
    public function getExplanationsAttribute(): array
    {
        return $this->lock('explanations', []);
    }

    /**
     * Get the model parent chapter.
     */
    public function getChapter(): Chapter
    {
        return $this->subject->notion->chapter;
    }
}
