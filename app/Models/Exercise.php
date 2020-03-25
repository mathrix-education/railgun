<?php

declare(strict_types=1);

namespace Railgun\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Railgun\Models\Traits\Commentable;
use Railgun\Models\Traits\Interactable;
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
    use Reorderable;
    use Randomizable;

    protected $fillable          = [
        'type',
        'questions',
        'choices',
        'solutions',
        'explanations',
        'order',
        'visible',
        'subject_id',
    ];
    protected $casts             = [
        'content'      => 'array',
        'questions'    => 'array',
        'choices'      => 'array',
        'solutions'    => 'array',
        'explanations' => 'array',
    ];
    protected array $orderGroups = ['subject_id'];

    /**
     * @return BelongsTo<Subject>
     */
    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }
}
