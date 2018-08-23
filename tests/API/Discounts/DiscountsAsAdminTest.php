<?php

namespace Tests\API\Discounts;

/**
 * Class DiscountsAsAdminTest
 * Test discounts as admin.
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @copyright Mathrix Education SA
 *
 * @version 4.0.3
 */
class DiscountsAsAdminTest extends DiscountsAsModeratorTest
{
    /**
     * GET /discounts.
     *
     * @throws \Exception
     */
    public function testIndex()
    {
        $this->genericIndexSuccess();
    }

    /**
     * GET /discounts/{id}.
     *
     * @throws \Exception
     */
    public function testGet()
    {
        $this->genericGetSuccess();
    }

    /**
     * POST /discounts.
     *
     * @throws \Exception
     */
    public function testAdd()
    {
        $this->genericAddSuccess();
    }

    /**
     * PATCH /discounts/{id}.
     *
     * @throws \Exception
     */
    public function testEdit()
    {
        $this->genericEditSuccess();
    }

    /**
     * DELETE /discounts/{id}.
     *
     * @throws \Exception
     */
    public function testDelete()
    {
        $this->genericDeleteSuccess();
    }
}
