<?php
/**
 * Created by PhpStorm.
 * User: leemason
 * Date: 22/11/15
 * Time: 13:56
 */

namespace LeeMason\Container;


use Interop\Container\ContainerInterface;

interface ContainerAwareInterface
{

    public function setContainer(ContainerInterface $container);

    public function getContainer();

}