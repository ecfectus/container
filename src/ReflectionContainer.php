<?php
namespace Ecfectus\Container;


class ReflectionContainer implements ContainerInterface, ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * @param string $id
     * @return object
     */
    public function get(string $id = '')
    {
        if (!class_exists($id)) {
            throw new NotFoundException('Alias (' . $id . ') is not an existing class and therefore cannot be resolved');
        }

        $reflector = new \ReflectionClass($id);
        $construct = $reflector->getConstructor();
        if ($construct === null) {
            return new $id;
        }

        return $reflector->newInstanceArgs(
            $this->reflectArguments($construct)
        );
    }

    /**
     * @param \ReflectionMethod $method
     * @return array
     */
    private function reflectArguments(\ReflectionMethod $method)
    {
        $arguments = array_map(function (\ReflectionParameter $param) use ($method) {
            $name  = $param->getName();
            $class = $param->getClass();
            if (! is_null($class)) {
                return $class->getName();
            }
            if ($param->isDefaultValueAvailable()) {
                return $param->getDefaultValue();
            }
            throw new NotFoundException('Unable to resolve a value for parameter (' . $name . ') in the function/method (' . $method->getName() . ')');
        }, $method->getParameters());

        return $this->resolveArguments($arguments);
    }

    /**
     * @param array $arguments
     * @return array
     */
    private function resolveArguments(array $arguments)
    {
        foreach ($arguments as &$arg) {
            if (! is_string($arg)) {
                continue;
            }
            $container = $this->getContainer();
            if (! is_null($container) && $container->has($arg)) {
                $arg = $container->get($arg);
                continue;
            }
        }
        return $arguments;
    }

    /**
     * @param string $id
     * @return bool
     */
    public function has(string $id = '') : bool
    {
        return class_exists($id);
    }
}
