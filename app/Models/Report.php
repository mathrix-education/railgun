<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Mathrix\Lumen\Zero\Models\BaseModel;
use function implode;

/**
 * @property int $id
 * @property string $content
 * @property string $status
 * @property int $module_id
 * @property string $created_at
 * @property string $updated_at
 * ---
 * @property Module $module
 */
class Report extends BaseModel
{
    public const STATUSES = ['todo', 'done', 'validated'];

    /** @var array The attributes that are mass assignable */
    protected $fillable = ['context', 'type', 'content', 'status', 'module_id'];
    /** @var array The validation rules */
    protected $rules = [
        'context' => 'max:255',
        'type' => 'required|max:255',
        'content' => 'required|max:255',
        'module_id' => 'exists:modules,id',
    ];

    /** @var array Relation included by default */
    protected $with = ['module'];

    public function getValidationRules()
    {
        $this->rules['status'] = 'required|in:' . implode(',', self::STATUSES);

        return parent::getValidationRules();
    }

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    /**
     * BelongsTo Module.
     */
    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }
}
