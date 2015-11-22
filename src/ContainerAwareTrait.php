<?php
/**
 * Created by PhpStorm.
 * User: leemason
 * Date: 22/11/15
 * Time: 13:56
 */

namespace LeeMason\Container;


use Interop\Container\ContainerInterface;

trait ContainerAwareTrait
{

    /**
     * @var
     */
    protected $container;

    /**
     * @param ContainerInterface $container
     * @return $this
     */
    public function setContainer(ContainerInterface $container)
    {
        $this->container = $container;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getContainer()
    {
        return $this->container;
    }

}