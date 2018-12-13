<?php

namespace Tests\API\Reports;

/**
 * Class ReportsAsModeratorTest
 * Test reports as moderator.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @copyright Mathrix Education SA
 *
 * @version   1.0.0
 */
class ReportsAsModeratorTest extends ReportsAsRegisteredTest
{
    /**
     * GET /reports.
     *
     * @throws \Exception
     */
    public function testIndex()
    {
        $this->genericIndexSuccess();
    }

    /**
     * GET /reports/{id}.
     *
     * @throws \Exception
     */
    public function testGet()
    {
        $this->genericGetSuccess();
    }

    /**
     * PATCH /reports/{id}.
     *
     * @throws \Exception
     */
    public function testEdit()
    {
        $this->genericEditSuccess([], function ($data) {
            unset($data["created_at"], $data["updated_at"]);
            return $data;
        });
    }

    /**
     * DELETE /reports/{id}.
     *
     * @throws \Exception
     */
    public function testDelete()
    {
        $this->genericDeleteSuccess();
    }
}
