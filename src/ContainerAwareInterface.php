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

    /**
     * @param ContainerInterface $container
     * @return mixed
     */
    public function setContainer(ContainerInterface $container);

    /**
     * @return mixed
     */
    public function getContainer();

}