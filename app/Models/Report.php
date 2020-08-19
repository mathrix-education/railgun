<?php

declare(strict_types=1);

namespace Railgun\Models;

use Carbon\Carbon;

/**
 * @property-read int    $id
 * @property-read string $type
 * @property      string $content
 * @property      string $status
 * @property      string $reportable_type
 * @property      int    $reportable_id
 * @property-read Carbon $created_at
 * @property-read Carbon $updated_at
 */
class Report extends BaseModel
{
    public const STATUSES = ['todo', 'done', 'validated'];

    /** @var array The attributes that are mass assignable */
    protected $fillable = ['context', 'type', 'content', 'status', 'module_id'];
}
