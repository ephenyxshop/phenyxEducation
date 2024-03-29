<?php
/**
 * Class Core_Foundation_IoC_Container
 *
 * @since 1.9.1.0
 */
// @codingStandardsIgnoreStart
class Core_Foundation_IoC_Container {

    // @codingStandardsIgnoreStartingStandardsIgnoreEnd

    protected $bindings = [];
    protected $instances = [];
    protected $namespaceAliases = [];

    /**
     * @param string $serviceName
     *
     * @return bool
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     */
    public function knows($serviceName) {

        return array_key_exists($serviceName, $this->bindings);
    }

    /**
     * @param string $alias
     *
     * @return bool
     */
    protected function knowsNamespaceAlias($alias) {

        return array_key_exists($alias, $this->namespaceAliases);
    }

    /**
     * @param string $serviceName
     * @param string $constructor
     * @param bool   $shared
     *
     * @return $this
     * @throws Core_Foundation_IoC_Exception
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     */
    public function bind($serviceName, $constructor, $shared = false) {

        if ($this->knows($serviceName)) {
            throw new Core_Foundation_IoC_Exception(
                sprintf('Cannot bind `%s` again. A service name can only be bound once.', $serviceName)
            );
        }

        $this->bindings[$serviceName] = [
            'constructor' => $constructor,
            'shared'      => $shared,
        ];

        return $this;
    }

    /**
     * @param $alias
     * @param $namespacePrefix
     *
     * @return $this
     * @throws Core_Foundation_IoC_Exception
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     */
    public function aliasNamespace($alias, $namespacePrefix) {

        if ($this->knowsNamespaceAlias($alias)) {
            throw new Core_Foundation_IoC_Exception(
                sprintf(
                    'Namespace alias `%1$s` already exists and points to `%2$s`',
                    $alias, $this->namespaceAliases[$alias]
                )
            );
        }

        $this->namespaceAliases[$alias] = $namespacePrefix;
        return $this;
    }

    /**
     * @param $className
     *
     * @return string
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     */
    public function resolveClassName($className) {

        $colonPos = strpos($className, ':');

        if (0 !== $colonPos) {
            $alias = substr($className, 0, $colonPos);

            if ($this->knowsNamespaceAlias($alias)) {
                $class = ltrim(substr($className, $colonPos + 1), '\\');
                return $this->namespaceAliases[$alias] . '\\' . $class;
            }

        }

        return $className;
    }

    /**
     * @param       $className
     * @param array $alreadySeen
     *
     * @return object
     * @throws Core_Foundation_IoC_Exception
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     */
    protected function makeInstanceFromClassName($className, array $alreadySeen) {

        $className = $this->resolveClassName($className);

        try {
            $refl = new ReflectionClass($className);
        } catch (ReflectionException $re) {
            throw new Core_Foundation_IoC_Exception(sprintf('This doesn\'t seem to be a class name: `%s`.', $className));
        }

        $args = [];

        if ($refl->isAbstract()) {
            throw new Core_Foundation_IoC_Exception(sprintf('Cannot build abstract class: `%s`.', $className));
        }

        $classConstructor = $refl->getConstructor();

        if ($classConstructor) {

            foreach ($classConstructor->getParameters() as $param) {
                $paramClass = $param->getType() && !$param->getType()->isBuiltin() ? new ReflectionClass($param->getType()->getName()) : null;

                if ($paramClass) {
                    $args[] = $this->doMake($param->getClass()->getName(), $alreadySeen);
                } else if ($param->isDefaultValueAvailable()) {
                    $args[] = $param->getDefaultValue();
                } else {
                    throw new Core_Foundation_IoC_Exception(sprintf('Cannot build a `%s`.', $className));
                }

            }

        }

        if (count($args) > 0) {
            return $refl->newInstanceArgs($args);
        } else {
            // newInstanceArgs with empty array fails in PHP 5.3 when the class
            // doesn't have an explicitly defined constructor
            return $refl->newInstance();
        }

    }

    /**
     * @param       $serviceName
     * @param array $alreadySeen
     *
     * @return mixed|object
     * @throws Core_Foundation_IoC_Exception
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     */
    protected function doMake($serviceName, array $alreadySeen = []) {

        if (array_key_exists($serviceName, $alreadySeen)) {
            throw new Core_Foundation_IoC_Exception(sprintf(
                'Cyclic dependency detected while building `%s`.',
                $serviceName
            ));
        }

        $alreadySeen[$serviceName] = true;

        if (!$this->knows($serviceName)) {
            $this->bind($serviceName, $serviceName);
        }

        $binding = $this->bindings[$serviceName];

        if ($binding['shared'] && array_key_exists($serviceName, $this->instances)) {
            return $this->instances[$serviceName];
        } else {
            $constructor = $binding['constructor'];

            if (is_callable($constructor)) {
                $service = call_user_func($constructor);
            } else if (!is_string($constructor)) {
                // user already provided the value, no need to construct it.
                $service = $constructor;
            } else {
                // assume the $constructor is a class name
                $service = $this->makeInstanceFromClassName($constructor, $alreadySeen);
            }

            if ($binding['shared']) {
                $this->instances[$serviceName] = $service;
            }

            return $service;
        }

    }

    /**
     * @param string $serviceName
     *
     * @return mixed|object
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     */
    public function make($serviceName) {

        return $this->doMake($serviceName, []);
    }

}
