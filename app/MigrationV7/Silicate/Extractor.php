<?php

declare(strict_types=1);

namespace Railgun\MigrationV7\Silicate;

use Railgun\MigrationV7\Exams\BrickFactory;
use RuntimeException;
use const INF;
use function array_map;
use function array_shift;
use function count;
use function explode;
use function preg_match;
use function preg_match_all;
use function strpos;
use function trim;

class Extractor
{
    private const HANDLERS = [
        'image'      => '/(?:<figure.*?)?<img.*?src=[\'"](.*?)[\'"].*?alt=[\'"](.*?)[\'"].*?(?:.*?\/figure>)?/sm',
        'imageR'     => '/(?:<figure.*?)?<img.*?alt=[\'"](.*?)[\'"].*?src=[\'"](.*?)[\'"].*?(?:.*?\/figure>)?/sm',
        'imageNoAlt' => '/(?:<figure.*?)?<img.*?src=[\'"](.*?)[\'"].*?(?:.*?\/figure>)?/sm',
        'table'      => '/<table.*?>(.*?)<\/table>/sm',
        'list'       => '/^(?:[-+](?:.*))(?:\n[-+](?:.*))$/m',
    ];

    private array  $wall;
    private string $working;

    public function image(array $matches): void
    {
        [$src, $alt]  = $matches;
        $this->wall[] = BrickFactory::image($src, $alt);
    }

    public function imageR(array $matches): void
    {
        [$alt, $src]  = $matches;
        $this->wall[] = BrickFactory::image($src, $alt);
    }

    public function imageNoAlt(array $matches): void
    {
        $this->wall[] = BrickFactory::image($matches[0]);
    }

    public function table(array $matches): void
    {
        $table  = $matches[0];
        $matrix = [];

        $trCount = preg_match_all('/<tr.*?>(.*?)<\/tr>/sm', $table, $trMatches);

        if ($trCount === 0) {
            return;
        }

        foreach ($trMatches[1] as $trIndex => $trString) {
            $tdCount = preg_match_all('/<(?:td|th).*?>(.*?)<\/(?:td|th)>/sm', $trString, $tdMatches);

            if ($tdCount <= 0) {
                continue;
            }

            $matrix[] = [];

            foreach ($tdMatches[1] as $tdIndex => $tdString) {
                $matrix[$trIndex][] = self::new()->extract($tdString);
            }
        }

        $this->wall[] = BrickFactory::table($matrix);
    }

    public function extract(string $in): array
    {
        $this->wall    = [];
        $this->working = trim($in);

        while ($this->working !== '') {
            $this->advance();
        }

        return $this->wall;
    }

    /**
     * Advance the extraction
     */
    private function advance(): void
    {
        $position = INF;
        $full     = null;
        $method   = null;
        $matches  = [];

        foreach (self::HANDLERS as $theMethod => $thePattern) {
            $result = preg_match($thePattern, $this->working, $theMatches);

            if ($result !== 1) {
                continue;
            }

            $theFull     = array_shift($theMatches);
            $thePosition = strpos($this->working, $theFull);

            if ($thePosition >= $position) {
                continue;
            }

            $position = $thePosition;
            $method   = $theMethod;
            $full     = $theFull;
            $matches  = array_map(fn($m) => trim($m), $theMatches);
        }

        if ($method === null && $this->working !== '') {
            // No pattern found, assuming it is just text
            $this->wall[]  = BrickFactory::text($this->working);
            $this->working = '';
        } else {
            // A pattern was found, execute its callback
            $parts = explode($full, $this->working);

            if (count($parts) > 2) {
                throw new RuntimeException('Edgy case');
            }

            [$before, $after] = $parts;

            if ($before !== '') {
                $this->wall[] = BrickFactory::text($before);
            }

            $this->{$method}($matches, $full, $position);
            $this->working = trim($after);
        }
    }

    public static function new(): Extractor
    {
        return new self();
    }

    public function list(array $matches, string $full): void
    {
        $items = explode("\n", $full);
        $items = array_map(fn($item) => trim($item, " \t\n\r\0\x0B-+"), $items);

        $this->wall[] = BrickFactory::list($items, 'ul');
    }
}
