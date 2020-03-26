<?php

declare(strict_types=1);

namespace Railgun\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Railgun\Models\Traits\Commentable;
use Railgun\Models\Traits\Interactable;
use Railgun\Models\Traits\Lockable;
use Railgun\Models\Traits\Notable;
use Railgun\Models\Traits\Randomizable;
use Railgun\Models\Traits\Reorderable;

/**
 * @property-read int     $id
 * @property      string  $type
 * @property      array   $content
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
class Exercise extends Model
{
    use Commentable;
    use Interactable;
    use Lockable;
    use Notable;
    use Reorderable;
    use Randomizable;

    protected $fillable = [
        'type',
        'questions',
        'choices',
        'solutions',
        'explanations',
        'order',
        'premium',
        'visible',
        'subject_id',
    ];

    protected $casts = [
        'content'      => 'array',
        'questions'    => 'array',
        'choices'      => 'array',
        'solutions'    => 'array',
        'explanations' => 'array',
        'premium'      => 'boolean',
        'visible'      => 'boolean',
    ];

    protected array $orderGroups = ['subject_id'];

    /**
     * @return BelongsTo<Subject>
     */
    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    /**
     * @return array the content Silicate wall.
     */
    public function getContentAttribute(): array
    {
        return $this->lock('content', []);
    }

    /**
     * @return array
     */
    public function getQuestionsAttribute(): array
    {
        return $this->lock('questions', []);
    }

    /**
     * @return array
     */
    public function getChoicesAttribute(): array
    {
        return $this->lock('choices', []);
    }

    /**
     * @return array
     */
    public function getSolutionsAttribute(): array
    {
        return $this->lock('solutions', []);
    }

    /**
     * @return array
     */
    public function getExplanationsAttribute(): array
    {
        return $this->lock('explanations', []);
    }
}
