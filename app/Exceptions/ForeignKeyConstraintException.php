<?php

declare(strict_types=1);

namespace Railgun\Exceptions;

use Illuminate\Database\Connection;
use Illuminate\Support\Str;
use function app;
use function preg_match;
use function trans;
use function trans_def_article;
use function trans_pronoun;
use function trans_undef_article;

/**
 * Thrown when a MySQL foreign key constraint exception is thrown.
 */
class ForeignKeyConstraintException extends QueryException
{
    public static function from(QueryException $exception): QueryException
    {
        $message = $exception->extensions()['errorInfo'][2];
        /** @var Connection $connection */
        $connection = app()->make('db.connection');
        $database   = $connection->getConfig('database');
        $regex      = "/{$database}[\s`\.,]+(.*?)[\s`\.,]+CONSTRAINT.*REFERENCES[\s`\.,]+(.*?)[\s`\.,]+/";
        $found      = preg_match($regex, $message, $matches);

        if ($found) {
            // Build the exception message
            $local   = 'models.' . Str::singular($matches[2]);
            $foreign = 'models.' . Str::singular($matches[1]);

            $message = Str::ucfirst(trans('sql.foreign_key_constraint', [
                'local'   => trans_def_article($local),
                'pronoun' => trans_pronoun($local),
                'foreign' => trans_undef_article($foreign, 2),
            ]));

            return new self($message, $exception);
        }

        // this should never be reached
        return new self($exception->getMessage(), $exception); // @codeCoverageIgnore
    }
}
