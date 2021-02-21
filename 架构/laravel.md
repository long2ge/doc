0. 先复习composer原理

1. 代码运行方式
    系统组件   
        路由，中间件，对象模型，控制器
    应用组件
2. 这些组件都是直接注入IOC容器里面
3. 单入口 - index.php
    第一步，初始化容器
    第二步，路由服务




    instance 实例绑定
    singleton  单例绑定
    bind 每次返回新的实例


/**
     * Resolve the given type from the container. 从容器中解析出给定服务具体实现
     *
     * @param  string  $abstract
     * @param  array  $parameters
     * @return mixed
     */
    protected function resolve($abstract, $parameters = [])
    {
        $abstract = $this->getAlias($abstract);

        // 如果绑定时基于上下文绑定，此时需要解析出上下文实现类
        $needsContextualBuild = ! empty($parameters) || ! is_null(
            $this->getContextualConcrete($abstract)
        );

        // 如果给定的类型已单例模式绑定，直接从服务容器中返回这个实例而无需重新实例化
        if (isset($this->instances[$abstract]) && ! $needsContextualBuild) {
            return $this->instances[$abstract];
        }

        $this->with[] = $parameters;

        $concrete = $this->getConcrete($abstract);

        // 已准备就绪创建这个绑定的实例。下面将实例化给定实例及内嵌的所有依赖实例。
        // 到这里我们已经做好创建实例的准备工作。只有可以构建的服务才可以执行 build 方法去实例化服务；
        // 否则也就是说我们的服务还存在依赖，然后不断的去解析嵌套的依赖，知道它们可以去构建（isBuildable）。
        if ($this->isBuildable($concrete, $abstract)) {
            $object = $this->build($concrete);
        } else {
            $object = $this->make($concrete);
        }

        // 如果我们的服务存在扩展（extend）绑定，此时就需要去执行扩展。
        // 扩展绑定适用于修改服务的配置或者修饰（decorating）服务实现。
        foreach ($this->getExtenders($abstract) as $extender) {
            $object = $extender($object, $this);
        }

        // 如果我们的服务已单例模式绑定，此时无要将已解析的服务缓存到单例对象池中（instances），
        // 后续便可以直接获取单例服务对象了。
        if ($this->isShared($abstract) && ! $needsContextualBuild) {
            $this->instances[$abstract] = $object;
        }

        $this->fireResolvingCallbacks($abstract, $object);

        $this->resolved[$abstract] = true;

        array_pop($this->with);

        return $object;
    }

    /**
     * Determine if the given concrete is buildable. 判断给定的实现是否立马进行构建
     *
     * @param  mixed   $concrete
     * @param  string  $abstract
     * @return bool
     */
    protected function isBuildable($concrete, $abstract)
    {
        // 仅当实现类和接口相同或者实现为闭包时可构建
        return $concrete === $abstract || $concrete instanceof Closure;
    }

    /**
     * Instantiate a concrete instance of the given type. 构建（实例化）给定类型的实现类(匿名函数)实例
     *
     * @param  string  $concrete
     * @return mixed
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function build($concrete)
    {
        // 如果给定的实现是一个闭包，直接执行并闭包，返回执行结果
        if ($concrete instanceof Closure) {
            return $concrete($this, $this->getLastParameterOverride());
        }

        $reflector = new ReflectionClass($concrete);

        // 如果需要解析的类无法实例化，即试图解析一个抽象类类型如: 接口或抽象类而非实现类，直接抛出异常。
        if (! $reflector->isInstantiable()) {
            return $this->notInstantiable($concrete);
        }

        $this->buildStack[] = $concrete;

        // 通过反射获取实现类构造函数
        $constructor = $reflector->getConstructor();

        // 如果实现类并没有定义构造函数，说明这个实现类没有相关依赖。
        // 我们可以直接实例化这个实现类，而无需自动解析依赖（自动注入）。
        if (is_null($constructor)) {
            array_pop($this->buildStack);

            return new $concrete;
        }

        // 获取到实现类构造函数依赖参数
        $dependencies = $constructor->getParameters();

        // 解析出所有依赖
        $instances = $this->resolveDependencies(
            $dependencies
        );

        array_pop($this->buildStack);

        // 这是我们就可以创建服务实例并返回。
        return $reflector->newInstanceArgs($instances);
    }

    /**
     * Resolve all of the dependencies from the ReflectionParameters. 从 ReflectionParameters 解析出所有构造函数所需依赖
     *
     * @param  array  $dependencies
     * @return array
     */
    protected function resolveDependencies(array $dependencies)
    {
        $results = [];

        foreach ($dependencies as $dependency) {
            // If this dependency has a override for this particular build we will use
            // that instead as the value. Otherwise, we will continue with this run
            // of resolutions and let reflection attempt to determine the result.
            if ($this->hasParameterOverride($dependency)) {
                $results[] = $this->getParameterOverride($dependency);

                continue;
            }

            // 构造函数参数为非类时，即参数为 string、int 等标量类型或闭包时，按照标量和闭包解析；
            // 否则需要解析类。
            $results[] = is_null($dependency->getClass())
                            ? $this->resolvePrimitive($dependency)
                            : $this->resolveClass($dependency);
        }

        return $results;
    }

    /**
     * Resolve a non-class hinted primitive dependency. 依据类型提示解析出标量类型（闭包）数据
     *
     * @param  \ReflectionParameter  $parameter
     * @return mixed
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    protected function resolvePrimitive(ReflectionParameter $parameter)
    {
        if (! is_null($concrete = $this->getContextualConcrete('$'.$parameter->name))) {
            return $concrete instanceof Closure ? $concrete($this) : $concrete;
        }

        if ($parameter->isDefaultValueAvailable()) {
            return $parameter->getDefaultValue();
        }

        $this->unresolvablePrimitive($parameter);
    }

    /**
     * Resolve a class based dependency from the container. 从服务容器中解析出类依赖（自动注入）
     *
     * @param  \ReflectionParameter  $parameter
     * @return mixed
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    protected function resolveClass(ReflectionParameter $parameter)
    {
        try {
            return $this->make($parameter->getClass()->name);
        }

        catch (BindingResolutionException $e) {
            if ($parameter->isOptional()) {
                return $parameter->getDefaultValue();
            }

            throw $e;
        }
    }