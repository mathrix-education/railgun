<?php
namespace Tests\Comments;

use Tests\ScopeTestCase;

/**
 * Class CommentsAsAdminTest
 * Test Comments as admin
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @copyright Mathrix
 * @version 1.0.0
 * @package Tests\Comments
 */
class CommentsAsAdminTest extends ScopeTestCase
{
    public function testIndex()
    {
        $this->genericIndexSuccess();
    }

    public function testAdd()
    {
        $this->genericAddSuccess();
    }

    public function testEdit()
    {
        $this->genericEditSuccess();
    }

    public function testDelete()
    {
        $this->genericSoftDeleteSuccess();
    }
}