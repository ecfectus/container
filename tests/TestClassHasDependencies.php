<?php

namespace Ecfectus\Container\Test;

class TestClassHasDependencies
{
    public function __construct(\stdClass $argument)
    {
        $this->argument = $argument;
    }
}
