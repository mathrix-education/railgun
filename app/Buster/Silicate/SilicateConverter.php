<?php

declare(strict_types=1);

namespace Railgun\Buster\Silicate;

use Illuminate\Support\Collection;
use function array_values;
use function str_replace;
use function strlen;
use function strpos;
use function substr;
use function trim;
use const INF;

class SilicateConverter
{
    private const TAGS   = [
        '$$'         => 'equation',
        '<BR/><BR/>' => 'text',
    ];
    private const DELETE = [
        "<span class='v2_mcq_enonce'></span>",
        "<span class='v2_test_enonce'></span>",
    ];

    /** @var Collection|string[] $tags The tags map. */
    protected Collection $tags;
    /** @var Collection|string[] $delete The strings to delete. */
    protected Collection $delete;
    /** @var Collection|string[] $stack The parser stack. */
    protected Collection $stack;
    /** @var mixed[] $wall The output wall. */
    private array        $wall = [];
    /** @var string $working The current working string. */
    private string       $working;

    public function __construct()
    {
        $this->tags   = new Collection(self::TAGS);
        $this->delete = new Collection(self::DELETE);
        $this->stack  = new Collection();
    }

    public static function new(): SilicateConverter
    {
        return new self();
    }

    /**
     * @param string $input The v5 text format.
     *
     * @return mixed[] The Silicate wall.
     */
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

    /**
     * @return mixed[]|bool The next tag and position, false if no tag was found.
     */
    private function nextTag()
    {
        $tag      = null;
        $position = INF;

        $this->tags->keys()->each(function (string $theTag) use (&$tag, &$position): void {
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

    /**
     * @param string|null $tag      The tag to extract, if any.
     * @param int|null    $position The tag position, if any.
     */
    private function extract(?string $tag = null, ?int $position = null): void
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
                $this->wall = [...$this->wall, ...SilicateTextExtractor::new()->extract($extract)];
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
