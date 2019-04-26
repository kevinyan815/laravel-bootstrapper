<?php
use App\Foundation\Facades\cURL;

/**
 * 与应用逻辑相关的帮助函数
 */
if (!function_exists('genChatSessionId')) {
    /**
     * 生成会话ID
     *
     * @param string $identifier 标示(通常是用户标示)
     * @return string
     */
    function genChatSessionId($identifier)
    {
        $identifierValue = array_sum(array_map(function ($char) {
            return ord($char);
        }, str_split($identifier)));

        $time = (int)(microtime(true) * 10000);

        //生成规则: base62(当前时间戳) . '-' . base62(用户标示字符串的ASCII和)
        return base62_encode($time) . '-' . base62_encode($identifierValue);
    }
}



if (!function_exists('makeInternalApiRequest')) {
    /**
     * 发起应用内部API请求, 如无特殊情况请不要用此方法, 多数情况下是由于程序设计有严重缺陷才导致的需要调用应用内部其他的API
     *
     * @param string $route 路由字符串
     * @param string $method HTTP请求方法
     * @param array $parameters 请求参数
     * @return \Illuminate\Http\Response
     */
    function makeInternalApiRequest($route, $method = 'GET', $parameters = [])
    {
        $request = \Illuminate\Http\Request::create($route, $method, $parameters, [], [], ['HTTP_Accept' => 'application/json']);
        app()->instance('request', $request);
        $response = \Route::dispatch($request);

        return $response;
    }
}

if (!function_exists('appEnv')) {
    /**
     * 获取项目里的ENV配置，之所以加这个函数是因为一旦在部署的时候打开config:cache 就会导致除了configuration files
     * 以外的地方通过Laravel的env函数获取不到.env里配置的环境变量，所以通过这个函数中转一下
     *
     * @param string $key 要获取的环境变量的KEY
     * @param string $default default value for $key
     * @return string
     */
    function appEnv($key, $default = null)
    {
        return config('env.' . $key, $default);
    }
}

if (!function_exists('sentryMessage')) {
    /**
     * 向Sentry上报Message的帮助函数
     * @param string $message
     * @param array $params
     * @param string $level[debug, info, warning, error, fatal]
     * @param bool $stack
     * @param null $vars
     * @return void
     */
    function sentryCaptureMessage(string $message,  array $params = array(), string $level = 'info', bool $stack = false, $vars = null) : void
    {
        app('sentry')->message($message, $params, $level, $stack, $vars);
    }
}

if (!function_exists('sentryException')) {
    /**
     * 向Sentry上报Exception的帮助函数
     *
     * @param Exception $exception
     * @return void
     */
    function sentryCaptureException(Exception $exception) : void
    {
        app('sentry')->captureException($exception);
    }
}

if (!function_exists('phoneLocation')) {
    /**
     * 通过电话号码获取归属地
     *
     * @param string $phone
     * @return array
     */
    function phoneLocation(string $phone) : array
    {
        $api = 'http://mobsec-dianhua.baidu.com/dianhua_api/open/location?tel=';
        $location = cURL::sendRequest($api . $phone, 'GET', [], [], [CURLOPT_TIMEOUT_MS => 500]);
        if (empty($location['response']) || empty($location['response'][$phone])) {
            return ['province' => '', 'city' => ''];
        }

        return [
            'province' => $location['response'][$phone]['detail']['province'],
            'city' => $location['response'][$phone]['detail']['area'][0]['city']
        ];
    }
}

if (!function_exists('getRealClientIp')) {
    /**
     * 获取客户端IP,(不管中间经过多少成代理)
     */
    function getRealClientIp()
    {
        // 优先获取网关通过cookie透传过来的客户端IP
        if ($cookieClientIp = request()->cookie('cube_client_real_ip')) {
            $ips = explode(',', $cookieClientIp);
            return $ips[0];
        }

        return request()->server('HTTP_X_FORWARDED_FOR') ? request()->server('HTTP_X_FORWARDED_FOR') : request()->ip();
    }
}

if (!function_exists('getRedisDistributedLock')) {
    /**
     * 获取Redis分布式锁
     *
     * @param $lockKey
     * @return bool
     */
    function getRedisDistributedLock(string $lockKey) : bool
    {
        $lockTimeout = 2000;// 锁的超时时间2000毫秒
        $now = intval(microtime(true) * 1000);
        $lockExpireTime = $now + $lockTimeout;
        $lockResult = \Redis::setnx($lockKey, $lockExpireTime);

        if ($lockResult) {
            // 当前进程设置锁成功
            return true;
        } else {
            // 如果当前时间大于进程锁的过期时间, 证明其他进程可能已经出现死锁,
            // 通过get($lockKey)获取$oldLockExpireTime
            // 使用getset($lockKey, $newExpireTime) 会设置$lockKey新的过期时间并返回老的key值$currentExpireTime
            // 如果$oldLockExpireTime与$currentExpireTime, 说明当前进程获取到了锁, 如果不相等说明锁已经被其他进程获取走了
            $oldLockExpireTime = \Redis::get($lockKey);
            if ($now > $oldLockExpireTime && $oldLockExpireTime == \Redis::getset($lockKey, $lockExpireTime)) {
                return true;
            }
        }

        return false;
    }
}

if (!function_exists('freeRedisDistributedLock')) {
    /**
     * 删除Redis中的分布式锁
     *
     * @param string $lockKey
     */
    function freeRedisDistributedLock(string $lockKey)
    {
        $now = intval(microtime(true) * 1000);
        $lockTimeout = \Redis::get($lockKey);
        if ($now < $lockTimeout) {
            \Redis::del($lockKey);
        }
    }
}

if (!function_exists('serialProcessing')) {
    /**
     * 串行执行程序
     *
     * @param string $lockKey Key for lock
     * @param Closure $closure 获得锁后进程要执行的闭包
     * @return mixed
     */
    function serialProcessing(string $lockKey, Closure $closure)
    {
        if (getRedisDistributedLock($lockKey)) {
            $result = $closure();
            freeRedisDistributedLock($lockKey);
        } else {
            // 延迟200毫秒再执行
            usleep(200 * 1000);
            return serialProcessing($lockKey, $closure);
        }

        return $result;
    }
}