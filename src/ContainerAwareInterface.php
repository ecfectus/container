<?php

namespace Ecfectus\Container;

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
