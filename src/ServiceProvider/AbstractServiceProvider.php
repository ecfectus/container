<?php

namespace Ecfectus\Container\ServiceProvider;

use Ecfectus\Container\ContainerAwareTrait;

abstract class AbstractServiceProvider implements ServiceProviderInterface
{
    use ContainerAwareTrait;
    /**
     * @var array
     */
    protected $provides = [];
    /**
     * {@inheritdoc}
     */
    public function provides($alias = null)
    {
        if (! is_null($alias)) {
            return (in_array($alias, $this->provides));
        }
        return $this->provides;
    }

    protected function bind(...$arguments){
        return $this->getContainer()->bind(...$arguments);
    }

    protected function share(...$arguments){
        return $this->getContainer()->share(...$arguments);
    }
}
