<?php
namespace Tests\Generics;

/**
 * Trait Delete
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @copyright Mathrix
 * @version 1.0.0
 * @package Tests\GenericTests
 */
trait Delete
{
    /** @var int model id */
    protected $deleteModelId;

    /**
     * Generic delete call
     */
    public function genericDelete()
    {
        $this->genericUsableOrFail();

        $this->deleteModelId = ($this->modelClass)::inRandomOrder()->firstOrFail()->id;

        $this->call('delete', '/' . $this->modelPlural . '/' . $this->deleteModelId);
    }

    /**
     * Generic delete; expect HTTP 200 and data present in database
     */
    public function genericDeleteSuccess()
    {
        $this->genericDelete();

        // Assertions
        $this->assertHttp200Success();
        $this->assertNotInDatabase($this->modelPlural, ['id' => $this->deleteModelId]);
    }

    /**
     * Generic delete; expect HTTP 200 and data present in database
     */
    public function genericSoftDeleteSuccess()
    {
        $this->genericDelete();

        $deletedAt = ($this->modelClass)::where('id', $this->deleteModelId)->withTrashed()->first()->deleted_at;

        // Assertions
        $this->assertHttp200Success();
        $this->assertInDatabase($this->modelPlural, ['id' => $this->deleteModelId, 'deleted_at' => $deletedAt]);
    }

    /**
     * Generic delete; expect HTTP 200 and data present in database
     */
    public function genericDeleteUnauthorized()
    {
        $this->genericDelete();

        // Assertions
        $this->assertHttp401Unauthorized();
        $this->assertInDatabase($this->modelPlural, ['id' => $this->deleteModelId]);
    }
}