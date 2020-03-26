<?php

declare(strict_types=1);

namespace Railgun\MigrationV7\Silicate;

use Illuminate\Support\Collection;
use Railgun\MigrationV7\Exams\BrickFactory;
use const INF;
use function array_values;
use function str_replace;
use function strlen;
use function strpos;
use function substr;
use function trim;

class Converter
{
    private const TAGS   = [
        '$$'         => 'equation',
        '<BR/><BR/>' => 'text',
    ];
    private const DELETE = ['<span class=\'v2_mcq_enonce\'></span>'];

    protected Collection $tags;
    protected Collection $delete;
    protected Collection $stack;
    private array        $wall = [];
    private string       $working;

    private function __construct()
    {
        $this->tags   = new Collection(self::TAGS);
        $this->delete = new Collection(self::DELETE);
        $this->stack  = new Collection();
    }

    public static function new(): Converter
    {
        return new self();
    }

    public function convert(string $input): array
    {
        $this->wall    = [];
        $this->stack   = new Collection();
        $this->working = $input;
        $this->delete->each(fn($r) => $this->working = str_replace($r, '', $this->working));

        while ($this->nextTag() !== false) {
            $this->extract();
        }

        $this->extract('', strlen($this->working));

        return array_values($this->wall);
    }

    private function nextTag()
    {
        $tag      = null;
        $position = INF;

        $this->tags->keys()->each(function (string $theTag) use (&$tag, &$position) {
            $thePosition = strpos($this->working, $theTag);

            if ($thePosition === false || $thePosition >= $position) {
                return;
            }

            $tag      = $theTag;
            $position = $thePosition;
        });

        if ($tag === null) {
            return false;
        }

        return [$tag, $position];
    }

    private function extract($tag = null, $position = null): void
    {
        $next = $this->nextTag();

        if ($this->stack->isEmpty()) {
            $this->stack->push('text');
        }

        if ($next !== false) {
            $tag      ??= $next[0];
            $position ??= $next[1];
        }

        $extract       = trim(substr($this->working, 0, $position));
        $this->working = trim(substr($this->working, $position + strlen($tag)));

        $context = $this->stack->pop();

        switch ($context) {
            case 'text':
                $this->wall = [...$this->wall, ...Extractor::new()->extract($extract)];
                break;
            case 'equation':
                $this->wall[] = BrickFactory::equation($extract);
                break;
        }

        if ($this->stack->isNotEmpty()) {
            return;
        }

        if ($tag === '$$' && $context !== 'equation') {
            $this->stack->push('equation');
        } elseif ($tag === '<BR/><BR/>') {
            $this->stack->push('text');
        }
    }
}
