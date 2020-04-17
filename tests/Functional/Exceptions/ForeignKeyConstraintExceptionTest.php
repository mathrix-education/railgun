<?php

declare(strict_types=1);

namespace Tests\Functional\Exceptions;

use Railgun\Exceptions\ForeignKeyConstraintException;
use Railgun\Models\Chapter;
use Railgun\Models\Notion;
use function factory;

/**
 * @testdox ForeignKeyConstraint Exception
 */
class ForeignKeyConstraintExceptionTest extends ExceptionTestCase
{
    public function testDeleteChapter(): void
    {
        /** @var Chapter $chapter */
        $chapter = factory(Chapter::class)->create();
        factory(Notion::class)->create(['chapter_id' => $chapter->id]);

        $this->expectException(ForeignKeyConstraintException::class);
        $this->expectDeprecationMessageMatches(
            '/^Impossible de supprimer (?:le|la|l\').* car (?:il|elle) possède encore des .*$/',
        );

        $this->transform(static fn() => $chapter->delete());
    }
}
