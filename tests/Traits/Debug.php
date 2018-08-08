<?php
namespace Tests\Traits;

/**
 * Class Debug
 *
 * @author Mathieu Bour <mathieu.tin.bour@ŋmail.com>
 * @package Tests\Traits
 * @since 4.1.0
 * @copyright Mathrix Education SA
 */
trait Debug
{
    /**
     * Handle response printing
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


    /**
     * Debug the response
     *
     * @throws \Exception
     */
    public function debug()
    {
        echo $this->currentUri . "\n";
        echo json_encode($this->getResponseData(), JSON_PRETTY_PRINT);
    }
}
