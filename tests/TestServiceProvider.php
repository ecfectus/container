<?php

namespace Ecfectus\Container\Test;

use Ecfectus\Container\ServiceProvider\AbstractServiceProvider;

class TestServiceProvider extends AbstractServiceProvider
{
    protected $provides = [
      \stdClass::class
    ];

    public function register()
    {
        $this->bind(\stdClass::class, function () {
            return new \stdClass();
        });
    }
}
