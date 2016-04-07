<?php
namespace Ecfectus\Container;

use Interop\Container\ContainerInterface;

class Container implements ContainerInterface
{
    /**
     * @var array
     */
    protected $definitions = [];

    /**
     * @var array
     */
    protected $sharedDefinitions = [];

    /**
     * @var array
     */
    protected $sharedInstances = [];

    /**
     * @var array
     */
    protected $delegates = [];

    /**
     * @var array
     */
    protected $extenders = [];

    /**
     * @param string $id
     * @return bool|mixed|object
     */
    public function get($id)
    {
        //if we have an instance return it
        if (array_key_exists($id, $this->sharedInstances)) {
            return $this->sharedInstances[$id];
        }

        //if its a shared item build it, save it and return
        if (array_key_exists($id, $this->sharedDefinitions)) {
            $instance = $this->makeFromDefinition($this->sharedDefinitions[$id]);

            //apply any extenders
            $instance = $this->applyExtenders($id, $instance);

            $this->sharedInstances[$id] = $instance;
            return $instance;
        }

        //if its a normal item build it, and return
        if (array_key_exists($id, $this->definitions)) {
            $instance = $this->makeFromDefinition($this->definitions[$id]);
            //apply any extenders
            $instance = $this->applyExtenders($id, $instance);
            return $instance;
        }

        //delegate
        if ($resolved = $this->getFromDelegate($id)) {
            //apply any extenders
            $resolved = $this->applyExtenders($id, $resolved);
            return $resolved;
        }

        //not found
        throw new NotFoundException(
            sprintf('Alias (%s) is not being managed by the container', $id)
        );
    }

    /**
     * @param string $id
     * @return bool
     */
    public function has($id)
    {
        if (array_key_exists($id, $this->definitions)) {
            return true;
        }

        if (array_key_exists($id, $this->sharedDefinitions)) {
            return true;
        }

        if (array_key_exists($id, $this->sharedInstances)) {
            return true;
        }

        return $this->hasInDelegate($id);
    }

    /**
     * Binds an id and value into the container.
     *
     * This simply adds them to the various properties, we don't want to instanciate anything yet to conserve memory
     *
     * @param $id
     * @param null $concrete
     * @param bool|false $shared
     */
    public function bind($id, $concrete = null, $shared = false)
    {
        if (null === $concrete) {
            $concrete = $id;
        }

        $id = $this->normalizeString($id);

        //if its an instance save is as shared regardless of variable
        if (is_object($concrete) && !$concrete instanceof \Closure) {
            //apply any extenders - may not be that useful with an instance but hey ho.
            $instance = $this->applyExtenders($id, $concrete);
            $this->sharedInstances[$id] = $instance;
            return;
        }

        $concrete = $this->normalizeString($concrete);

        //if its callable, or is array and first item is callable
        if (is_callable($concrete) || (is_array($concrete) && is_callable($concrete[0]))) {
            if (false === $shared) {
                $this->definitions[$id] = (array) $concrete;
                return;
            }
            $this->sharedDefinitions[$id] = (array) $concrete;
            return;
        }

        //if its a class string and it exists, or its an array and the first item is a class string
        if (is_string($concrete) && class_exists($concrete) || (is_array($concrete) && class_exists($concrete[0]))) {
            if (false === $shared) {
                $this->definitions[$id] = (array) $concrete;
                return;
            }
            $this->sharedDefinitions[$id] = (array) $concrete;
            return;
        }

        //if were here its arbitary data to assign on the container
        $this->sharedInstances[$id] = $concrete;
    }

    /**
     * Simple semantic wrapper around bind to share an item
     *
     * @param $id
     * @param null $concrete
     */
    public function share($id, $concrete = null)
    {
        return $this->bind($id, $concrete, true);
    }

    /**
     * Allows registration of callbacks run before the instance is resolved from the container.
     *
     * Useful for things like setting setters etc on the resolved object.
     *
     * @param $id
     * @param callable $extender
     */
    public function extend($id, callable $extender)
    {
        if (!$this->has($id)) {
            throw new NotFoundException(
                sprintf('Alias (%s) is not being managed by the container', $id)
            );
        }
        if (!is_callable($extender)) {
            throw new \InvalidArgumentException('You must provide a callable to the extend method.');
        }
        $this->extenders[$id][] = $extender;
    }

    /**
     * @param ContainerInterface $container
     * @return $this
     */
    public function delegate(ContainerInterface $container)
    {
        $this->delegates[] = $container;
        if ($container instanceof ContainerAwareInterface) {
            $container->setContainer($this);
        }
        return $this;
    }

    /**
     * @param $id
     * @return bool
     */
    private function hasInDelegate($id)
    {
        foreach ($this->delegates as $container) {
            if ($container->has($id)) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param $id
     * @return bool
     */
    protected function getFromDelegate($id)
    {
        foreach ($this->delegates as $container) {
            if ($container->has($id)) {
                return $container->get($id);
            }
            continue;
        }
        return false;
    }

    /**
     * @param $definition
     * @return bool|mixed|object
     */
    private function makeFromDefinition($definition)
    {
        $target = array_shift($definition);

        //if there are no arguments, just call/make it with the container as an argument and return
        if (empty($definition)) {
            if (is_callable($target)) {
                return $target($this);
            }
            if (class_exists($target)) {
                $instance = new $target;
                return $instance;
            }
            return false;
        }

        //build arguments array from container - if a key exists it will be used instead
        foreach ($definition as $key => $value) {
            if ($this->has($value)) {
                $definition[$key] = $this->get($value);
                continue;
            }
        }

        if (is_callable($target)) {
            return $target(...$definition);
        }

        if (class_exists($target)) {

            return new $target(...$definition);
        }

        return false;
    }

    /**
     * @param $string
     * @return string
     */
    private function normalizeString($string)
    {
        return (is_string($string) && strpos($string, '\\') === 0) ? substr($string, 1) : $string;
    }

    /**
     * @param $id
     * @param $instance
     * @return mixed
     */
    private function applyExtenders($id, $instance)
    {
        if (isset($this->extenders[$id]) && !empty($this->extenders[$id])) {
            foreach ($this->extenders[$id] as $extender) {
                $instance = $extender($instance, $this);
            }
        }
        return $instance;
    }

    public function __call($method, $arguments){
        foreach ($this->delegates as $container) {
            if (is_callable([$container, $method])) {
                return $container->$method(...$arguments);
            }
        }
    }
}
