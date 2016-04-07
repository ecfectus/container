<?php

namespace Ecfectus\Container;

use Interop\Container\ContainerInterface;
use Ecfectus\Container\ServiceProvider\BootableServiceProviderInterface;
use Ecfectus\Container\ServiceProvider\ServiceProviderInterface;

class ServiceProviderContainer extends Container implements ContainerInterface, ContainerAwareInterface
{
    use ContainerAwareTrait;

    protected $booted = false;

    protected $providers = [];

    protected $provides = [];

    public function addServiceProvider($provider)
    {
        if ($this->getContainer()->has($provider)) {
            $instance = $this->getContainer()->get($provider);
        } else {
            $instance = new $provider();
        }

        $this->providers[$provider] = $instance;

        if ($instance instanceof ContainerAwareInterface) {
            $instance->setContainer($this->getContainer());
        }

        if ($this->booted && $instance instanceof BootableServiceProviderInterface) {
            $instance->boot();
        }

        if ($instance instanceof ServiceProviderInterface) {
            foreach ($instance->provides() as $service) {
                $this->provides[$service] = $provider;
            }
            return $this;
        }

        throw new \InvalidArgumentException(
            'A service provider must be a fully qualified class name or instance ' .
            'of ('.ServiceProviderInterface::class.')'
        );
    }

    public function bootServiceProviders(){

        foreach($this->providers as $provider){
            if ($provider instanceof BootableServiceProviderInterface) {
                $provider->boot();
            }
        }

        $this->booted = true;
    }

    public function get($id)
    {
        if (!$this->has($id)) {
            throw new NotFoundException(
                sprintf('Alias (%s) is not an existing class and therefore cannot be resolved', $id)
            );
        }

        $provider = $this->provides[$id];
        $instance = $this->providers[$provider];

        //register into the main container, this instance will never be called again so we could destroy it if we wanted to @TODO
        $instance->register();

        //should be registered so lets go back to the main container and fetch it
        return $this->getContainer()->get($id);
    }

    public function has($id)
    {
        return array_key_exists($id, $this->provides);
    }
}
