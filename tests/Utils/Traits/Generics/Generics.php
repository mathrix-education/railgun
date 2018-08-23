<?php
namespace Tests\Utils\Traits\Generics;

/**
 * Trait Generics
 * Generic tests
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @copyright Mathrix Education SA
 * @since 1.0.0
 * @package Tests\GenericTests
 */
trait Generics
{
    use Index, Get, Add, Edit, Delete, By, Search;

    /** @var string|null the subfactory to use; null for default factory */
    protected $subFactory = null;


    /**
     * Check if generic tests are usable; otherwise throw exception
     *
     * @throws \Exception
     */
    protected function genericUsableOrFail()
    {
        if (!$this->genericUsable()) {
            throw new \Exception("Generic tests are not usable in this context");
        }
    }


    /**
     * Check if generic tests are usable
     *
     * @return bool
     */
    protected function genericUsable(): bool
    {
        return !is_null($this->modelClass) && !is_null($this->modelSingular) && !is_null($this->modelPlural);
    }
}
