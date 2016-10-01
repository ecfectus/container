<?php

namespace Ecfectus\Container;

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
