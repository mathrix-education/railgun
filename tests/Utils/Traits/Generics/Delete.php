<?php

namespace Tests\Utils\Traits\Generics;

/**
 * Trait Delete.
 *
 * @author    Mathieu Bour <mathieu@mathrix.fr>
 * @author    Jérémie Levain <munezero999@live.fr>
 * @since     1.0.0
 * @copyright Mathrix Education SA
 * @package   Tests\Utils\Traits\Generics
 */
trait Delete
{
    /** @var int model id */
    protected $deleteModelId;


    /**
     * Generic delete; expect HTTP 200 and data present in database.
     *
     * @throws \Exception
     */
    public function genericDeleteSuccess()
    {
        $this->genericDelete();

        // Assertions
        $this->assertHttp200Success();
        $this->assertNotInDatabase($this->modelPlural, ["id" => $this->deleteModelId]);
    }


    /**
     * Generic delete call.
     *
     * @throws \Exception
     */
    public function genericDelete()
    {
        $this->genericUsableOrFail();

        if (empty($this->deleteModelId)) {
            $this->deleteModelId = ($this->modelClass)::inRandomOrder()->firstOrFail()->id;
        }

        $this->json("delete", "/" . $this->modelPlural . "/" . $this->deleteModelId);
    }


    /**
     * Generic delete; expect HTTP 200 and data present in database.
     *
     * @throws \Exception
     */
    public function genericSoftDeleteSuccess()
    {
        $this->genericDelete();

        $deletedAt = ($this->modelClass)::where("id", $this->deleteModelId)->withTrashed()->first()->deleted_at;

        // Assertions
        $this->assertHttp200Success();
        $this->assertInDatabase($this->modelPlural, ["id" => $this->deleteModelId, "deleted_at" => $deletedAt]);
    }


    /**
     * Generic delete; expect HTTP 200 and data present in database.
     *
     * @throws \Exception
     */
    public function genericDeleteUnauthorized()
    {
        $this->genericDelete();

        // Assertions
        $this->assertHttp401Unauthorized();
        $this->assertInDatabase($this->modelPlural, ["id" => $this->deleteModelId]);
    }
}
