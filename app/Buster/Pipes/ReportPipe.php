<?php

declare(strict_types=1);

namespace Railgun\Buster\Pipes;

use Closure;
use Illuminate\Support\Facades\Log;
use JsonException;
use stdClass;
use function file_exists;
use function file_put_contents;
use function json_encode;
use function property_exists;
use function random_int;
use function storage_path;
use function unlink;
use const JSON_PRETTY_PRINT;
use const JSON_THROW_ON_ERROR;
use const PHP_INT_MAX;

/**
 * Report errors occured during a conversion.
 */
class ReportPipe extends Pipe
{
    private string $type;

    public function __construct(string $type)
    {
        $this->type = $type;
    }

    /**
     * @param stdClass $model The to model convert.
     * @param Closure  $next  The next pipe.
     *
     * @return stdClass|false The converted model, false if conversion failed.
     *
     * @throws JsonException
     */
    public function handle(stdClass $model, Closure $next)
    {
        if (property_exists($model, 'mapping')) {
            $id = $model->mapping;
        } elseif (property_exists($model, 'id')) {
            $id = $model->id;
        } else {
            $id = random_int(0, PHP_INT_MAX);
        }

        $reportPath = storage_path("buster/reports/{$this->type}-{$id}.txt");
        $original   = clone $model;

        if (file_exists($reportPath)) {
            unlink($reportPath);
        }

        $converted = $next($model);

        if (property_exists($converted, 'debug')) {
            Log::channel('buster')->error($converted->debug->message);

            $message = $converted->debug->message;
            $trace   = $converted->debug->trace;
            unset($converted->debug);

            $originalJson  = json_encode($original, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT, 512);
            $convertedJson = json_encode($converted, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT, 512);

            $report = <<<REPORT
            $message
            Source: $trace
            ===
            
            Original:
            ===
            $originalJson
            
            Converted:
            $convertedJson
            REPORT;

            file_put_contents($reportPath, $report);

            return false;
        }

        return $converted;
    }
}
