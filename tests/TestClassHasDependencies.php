<?php

namespace Conformity\Container\Test;


class TestClassHasDependencies
{

    public function __construct(\stdClass $argument){
        $this->argument = $argument;
    }

}