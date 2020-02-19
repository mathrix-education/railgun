<?php

declare(strict_types=1);

namespace Railgun\Models;

use Illuminate\Database\Eloquent\Model;
use Railgun\Models\Traits\Randomizable;

/**
 * @property-read  int    $id
 * @property-read  string $type
 * @property string       $content
 * @property string       $status
 * @property string       $reportable_type
 * @property int          $reportable_id
 * @property-read string  $created_at
 * @property-read string  $updated_at
 */
class Report extends Model
{
    use Randomizable;

    public const STATUSES = ['todo', 'done', 'validated'];

    /** @var array The attributes that are mass assignable */
    protected $fillable = ['context', 'type', 'content', 'status', 'module_id'];
}
