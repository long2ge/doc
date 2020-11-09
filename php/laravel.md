跨数据库关联查询：
    
    public function templates() 
    { 
        $instance = new StoreTemplate; // new 实例
        $instance->setTable('store_power.store_template'); // 设置该实例的表。store_power是我的另一个数据库
        $query =$instance->newQuery(); 
        return new BelongsTo($query,$this,'id','store_id',null); // BelongsTo是laravel自带类
    }

框架的生命周期:
    
    Laravel 的生命周期从public\index.php开始，从public\index.php结束。
    
    1. 文件载入composer生成的自动加载设置，包括所有你 composer require的依赖。
    
    2. 生成容器Container，Application实例，并向容器注册核心组件（HttpKernel，ConsoleKernel ，ExceptionHandler）。
    
    3. 处理请求，生成并发送响应（对应代码3，毫不夸张的说，你99%的代码都运行在这个小小的handle 方法里面）。
    
    4. 请求结束，进行回调（对应代码4，还记得可终止中间件吗？没错，就是在这里回调的）。