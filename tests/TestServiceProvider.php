<?php

namespace Conformity\Container\Test;

use Conformity\Container\ServiceProvider\AbstractServiceProvider;

class TestServiceProvider extends AbstractServiceProvider
{
    protected $provides = [
      \stdClass::class
    ];

    public function register()
    {
        $this->getContainer()->bind('stdClass', function () {
            return new \stdClass();
        });
    }
}
