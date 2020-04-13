<?php

declare(strict_types=1);

namespace Railgun\Models\Traits;

use Illuminate\Database\Eloquent\Concerns\HasAttributes;
use Illuminate\Database\Eloquent\Concerns\HasEvents;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use RuntimeException;
use function method_exists;
use function property_exists;

/**
 * @property-read string $slugColumn       The slug column.
 * @property-read string $slugSourceColumn The slug generation source (default name, then title).
 * @method string sluggify(string $raw) Generate the slug from the source.
 * @mixin HasAttributes
 * @mixin HasEvents
 */
trait Sluggable
{
    private array $defaultSlugSources = ['name', 'title'];

    public static function bootSluggable(): void
    {
        self::creating(static function ($model): void {
            /** @var Sluggable|Model $model */
            if (!$model->getSlug()) {
                $source = $model->getSlugSource();

                if (method_exists($model, 'sluggify')) {
                    $slug = $model->sluggify($source);
                } else {
                    $slug = Str::slug($source);
                }

                $model->setSlug($slug);
            }
        });
    }

    public function getSlug(): ?string
    {
        return $this->getAttribute($this->getSlugColumn());
    }

    public function setSlug(string $newSlug): void
    {
        $this->setAttribute($this->getSlugColumn(), $newSlug);
    }

    public function getSlugColumn(): string
    {
        return property_exists($this, 'slugColumn') ? $this->slugColumn : 'slug';
    }

    public function getSlugSourceColumn(): string
    {
        if (property_exists($this, 'slugSourceColumn')) {
            return $this->slugSourceColumn;
        }

        foreach ($this->defaultSlugSources as $field) {
            if ($this->getAttribute($field) !== null) {
                return $field;
            }
        }

        throw new RuntimeException('Unable to retrieve the slug source column');
    }

    public function getSlugSource(): string
    {
        return $this->getAttribute($this->getSlugSourceColumn());
    }
}
