<?php
namespace Tests\Utils\Traits;

/**
 * Class DebugTrait.
 *
 * @author    Mathieu Bour <mathieu@mathrix.fr>
 * @author    Jérémie Levain <munezero999@live.fr>
 * @since     4.1.0
 * @copyright Mathrix Education SA
 * @package   Tests\Utils\Traits
 */
trait DebugTrait
{
    /**
     * Debug the response.
     *
     * @throws \Exception
     */
    public function debug()
    {
        echo $this->currentUri . "\n";
        echo json_encode($this->getResponseData(), JSON_PRETTY_PRINT);
    }

    /**
     * Handle response printing.
     *
     * @param \Exception|\Throwable $e
     *
     * @throws \Throwable
     */
    protected function onNotSuccessfulTest(\Throwable $e)
    {
        $this->debug();
        parent::onNotSuccessfulTest($e);
    }
}
