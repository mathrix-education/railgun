<?php

namespace Tests\API\References;

/**
 * Class ReferencesAsModeratorTest.
 * Test references as moderator.
 *
 * @author    Mathieu Bour <mathieu@mathrix.fr>
 * @author    Jérémie Levain <munezero999@live.fr>
 * @since     4.3.0
 * @copyright Mathrix Education SA
 * @package   App\Console\Commands\references
 */
class ReferencesAsModeratorTest extends ReferencesAsRedactorTest
{
    /**
     * POST /quotes.
     *
     * @throws \Exception
     */
    public function testAdd()
    {
        $this->genericAddSuccess();
    }


    /**
     * PATCH /quotes/{id}.
     *
     * @throws \Exception
     */
    public function testEdit()
    {
        $this->genericEditSuccess();
    }


    /**
     * DELETE /quotes/{id}.
     *
     * @throws \Exception
     */
    public function testDelete()
    {
        $this->genericDeleteSuccess();
    }
}
