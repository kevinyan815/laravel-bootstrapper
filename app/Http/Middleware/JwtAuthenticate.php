<?php

namespace App\Http\Middleware;

use Carbon\Carbon;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use JWTAuth;
use Tymon\JWTAuth\Middleware\BaseMiddleware as JwtBaseMiddleware;
use App\Foundation\Traits\ApiTrait;

class JwtAuthenticate extends JwtBaseMiddleware
{
    use ApiTrait;
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, \Closure $next)
    {
        //前置操作
        if (! $token = $this->auth->setRequest($request)->getToken()) {
            return $this->error(10010402, 'token is absent');
        }
        try {
            $user = $this->auth->authenticate($token);
        } catch (TokenExpiredException $e) {
            return $this->error(10010403, 'token is expired');
        } catch (JWTException $e) {
            return $this->error(10010405, 'token is invalid');
        }

        if (! $user) {
            return $this->error(10010404, 'user not found');
        }

        $response = $next($request);

        //后置操作
        $tokenCreateTime = $tokenCreateTime = $this->auth->decode($token)->get('iat');
        $refreshInterval = config('jwt.refresh_interval');
        //如果(token发布时间 ＋ token刷新时间间隔) 早于当前时间, 生成新token给客户端 (防止客户端频繁操作但token仍过期的情况)
        if (Carbon::createFromTimestamp($tokenCreateTime)->addMinutes($refreshInterval) < Carbon::now()) {
            $newToken = JWTAuth::fromUser($user);
            $token = $newToken;
        }
        // 把token通过响应头发回给客户端
        $response->headers->set('Authorization', 'Bearer '.$token);
        $response->headers->set('Access-Control-Expose-Headers', 'Authorization');

        return $response;
    }
}
