<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Mathrix\Lumen\Zero\Models\BaseModel;

/**
 * @property-read int $id
 * @property array    $fronts
 * @property array    $backs
 */
class Flashcard extends BaseModel
{
    protected $fillable = ['fronts', 'backs', 'order', 'visible', 'chapter_id'];

    /**
     * @return BelongsTo<Chapter>
     */
    public function chapter(): BelongsTo
    {
        return $this->belongsTo(Chapter::class);
    }
}
