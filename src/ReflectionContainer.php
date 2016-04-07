<?php
namespace Ecfectus\Container;

use Interop\Container\ContainerInterface;

class ReflectionContainer implements ContainerInterface, ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * @param string $id
     * @return object
     */
    public function get($id)
    {
        if (!class_exists($id)) {
            throw new NotFoundException(
                sprintf('Alias (%s) is not an existing class and therefore cannot be resolved', $id)
            );
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
            throw new NotFoundException(sprintf(
                'Unable to resolve a value for parameter (%s) in the function/method (%s)',
                $name,
                $method->getName()
            ));
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
    public function has($id)
    {
        return class_exists($id);
    }
}
