<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Mathrix\Lumen\Zero\Models\BaseModel;
use function array_fill;
use function count;
use function implode;

/**
 * @property int $id
 * @property bool $premium
 * @property string $type
 * @property string $content
 * @property array $questions
 * @property array $choices
 * @property array $solutions
 * @property array $corrections
 * @property string $options
 * @property bool $visible
 * @property bool $seo_index
 * @property string $seo_title
 * @property string $seo_slug
 * @property string $seo_keywords
 * @property string $seo_description
 * @property int $order
 * @property int $activity_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * ---
 * @property string|null $status
 * @property string|null $mark
 * ---
 * @property Activity $activity
 * @property Collection|Comment[] $comments
 * @property Collection|Log[] $logs
 * @property Collection|Mark[] $marks
 * @property Collection|Product[] $products
 * @property Collection|Report[] $reports
 */
class Module extends BaseModel
{
    public const TYPES = ['course', 'test', 'mcq', 'flashcard'];
    /** @var array The attributes that are mass assignable */
    protected $fillable = [
        'premium',
        'type',
        'content',
        'questions',
        'choices',
        'solutions',
        'corrections',
        'options',
        'visible',
        'seo_index',
        'seo_title',
        'seo_slug',
        'seo_keywords',
        'seo_description',
        'order',
        'activity_id',
    ];
    /** @var array The attributes that should be cast to native types */
    protected $casts = [
        'visible' => 'boolean',
        'questions' => 'array',
        'choices' => 'array',
        'solutions' => 'array',
        'corrections' => 'array',
        'options' => 'array',
        'seo_index' => 'boolean',
    ];
    /** @var array The validation rules */
    protected $rules = [
        'visible' => 'required|boolean',
        'seo_index' => 'boolean',
    ];
    /** @var array The accessors to append to the model's array form */
    protected $appends = ['mark'];

    public function getValidationRules()
    {
        // Dynamic rules
        $this->rules['type'] = 'required|in:' . implode(',', self::TYPES);

        return $this->rules;
    }

    /**
     * Allow module data striping if the user is not premium.
     *
     * @return array
     */
    public function toArray(): array
    {
        if (! $this->shouldStrip()) {
            return parent::toArray();
        }

        $keep = ['id', 'type', 'visible'];
        $data = parent::toArray();
        $data = Arr::only($data, $keep);

        if ($this->type === 'flashcard') {
            $data['questions'] = array_fill(0, count($this->questions), '');
        }

        return $data;
    }

    /**
     * Determine if the module should be striped.
     */
    public function shouldStrip(): bool
    {
        /** @var User $user */
        $user = Auth::user();

        /** @var bool $belongsToPremiumActivity If the module's parent activity is premium */
        $belongsToPremiumActivity = $this->activity !== null && $this->activity->premium;
        /** @var bool $belongsToProduct If the module is linked to a product */
        $belongsToProduct = $this->products->isNotEmpty();
        /** @var bool $userIsPremium If the user is premium */
        $userIsPremium = $user && ($user->premium || $user->hasScope('modules:read'));
        /** @var bool $userOwnsProduct If the user has purchased one of the related products */
        $userOwnsProduct = $user && ($user->hasScope('modules:read') || $this->products()
                    ->get()
                    ->reduce(static function (bool $current, Product $product) use ($user) {
                        return $current || $user->hasPurchased($product);
                    }, false));

        $this->unsetRelation('activity');
        $this->unsetRelation('products');

        if ($belongsToProduct) {
            return ! $userOwnsProduct && (! $belongsToPremiumActivity || ! $userIsPremium);
        }

        if ($belongsToPremiumActivity) {
            if ($userIsPremium) {
                return false;
            }

            return true;
        }

        return false;
    }

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    /**
     * HasMany Product.
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function activity(): BelongsTo
    {
        return $this->belongsTo(Activity::class);
    }

    /**
     * HasMany Comment.
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class)
            ->orderBy('created_at');
    }

    /**
     * HasMany Post.
     */
    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    /**
     * HasMany Report.
     */
    public function reports(): HasMany
    {
        return $this->hasMany(Report::class);
    }

    /**
     * Get the mark attribute (view|to_revise|completed)
     */
    public function getMarkAttribute(): ?string
    {
        /** @var User $user */
        $user = Auth::user();

        if ($user === null) {
            return null;
        }

        /** @var Mark $mark */
        $mark = $this->marks()->where('user_id', '=', $user->id)->first();

        return $mark !== null ? $mark->status : null;
    }

    /*
    |--------------------------------------------------------------------------
    | Attributes
    |--------------------------------------------------------------------------
    */

    /**
     * MorphMany Mark.
     */
    public function marks(): MorphMany
    {
        return $this->morphMany(Mark::class, 'markable');
    }
}
