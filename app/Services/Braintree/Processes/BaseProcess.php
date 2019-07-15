<?php

namespace App\Controllers\Processes;

abstract class BaseProcess
{
    protected abstract function validate(): void;


    protected abstract function execute(): void;


    protected abstract function finalize(): void;


    public final function run(): void
    {
        $this->validate();
        $this->execute();
        $this->finalize();
    }
}
