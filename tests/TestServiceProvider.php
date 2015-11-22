<?php
/**
 * Created by PhpStorm.
 * User: leemason
 * Date: 22/11/15
 * Time: 21:32
 */

namespace LeeMason\Container\Test;


use LeeMason\Container\ServiceProvider\AbstractServiceProvider;

class TestServiceProvider extends AbstractServiceProvider
{

    protected $provides = [
      \stdClass::class
    ];

    public function register(){
        $this->getContainer()->bind('stdClass', function(){
            return new \stdClass();
        });
    }

}