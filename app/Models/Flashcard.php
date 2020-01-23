<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property-read int    $id
 * @property array       $fronts
 * @property array       $backs
 * @property int         $order
 * @property bool        $visible
 * @property int         $chapter_id
 * @property-read Carbon $created_at
 * @property-read Carbon $updated_at
 */
class Flashcard extends Model
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
