<?php

namespace Tests\API\Quotes;

/**
 * Class QuotesAsAdminTest.
 * Test quotes as admin.
 *
 * @author    Mathieu Bour <mathieu@mathrix.fr>
 * @author    Jérémie Levain <munezero999@live.fr>
 * @since     1.0.0
 * @copyright Mathrix Education SA
 * @package   Tests\API\Quotes
 */
class QuotesAsAdminTest extends QuotesAsModeratorTest
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
