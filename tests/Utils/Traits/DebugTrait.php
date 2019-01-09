<?php

namespace Tests\Utils\Traits;

/**
 * Class Debug.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 *
 * @since     4.1.0
 *
 * @copyright Mathrix Education SA
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
        echo "HTTP " . $this->response->getStatusCode() . "\n";
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
