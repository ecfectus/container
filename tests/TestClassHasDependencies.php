<?php
/**
 * Created by PhpStorm.
 * User: leemason
 * Date: 22/11/15
 * Time: 22:27
 */

namespace LeeMason\Container\Test;


class TestClassHasDependencies
{

    public function __construct(\stdClass $argument){
        $this->argument = $argument;
    }

}