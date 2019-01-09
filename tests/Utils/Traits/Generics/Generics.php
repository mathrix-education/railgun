<?php
namespace Tests\Utils\Traits\Generics;

/**
 * Trait Generics.
 * Generic tests.
 *
 * @author    Mathieu Bour <mathieu@mathrix.fr>
 * @author    Jérémie Levain <munezero999@live.fr>
 * @since     1.0.0
 * @copyright Mathrix Education SA
 * @package   Tests\Utils\Traits\Generics
 */
trait Generics
{
    use Index, Get, Add, Edit, Delete, By, Search;

    /** @var string|null the subfactory to use; null for default factory */
    protected $subFactory = null;

    /**
     * Check if generic tests are usable; otherwise throw exception.
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
     * Check if generic tests are usable.
     *
     * @return bool
     */
    protected function genericUsable(): bool
    {
        return null !== $this->modelClass && null !== $this->modelSingular && null !== $this->modelPlural;
    }
}
