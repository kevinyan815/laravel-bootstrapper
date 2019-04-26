<?php
/**
 * @author yanhj<yanhj5@lenovo.com>
 * @copyright (c) 2017, Lenovo
 */

namespace App\Foundation\Traits;

use DB;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Validator;

trait ApiTrait
{
    /**
     * 格式化API的响应数据
     *
     * @param array  $data  返回数据
     * @param int    $statusCode  错误码
     * @param string $message 错误信息
     * @return array
     */
    protected function formatResponse($data = [], $statusCode = 200, $message = 'Success')
    {
        $return['statusCode'] = $statusCode;
        $return['message'] = $this->formatResponseMessage($message);
        $return['data'] = $data;
        //在测试环境下可以通过在请求参数里添加sql_debug参数来获取请求过程中执行的SQL
        if (request()->input('sql_debug', false) && appEnv('APP_DEBUG', false)) {
            //相同条件下在AppServiceProvider中通过\DB::enableQueryLog()开启了SQL记录
            $return['sql'] = [
                \DB::getQueryLog()
            ];
            \DB::disableQueryLog();
        }

        return $return;
    }

    /**
     * 格式化响应中的message
     * @param mixed $message
     * @return array
     */
    private function formatResponseMessage($message)
    {
        if (is_string($message)) {
            return ['info' => $message];
        }
        array_walk($message, function (&$item, $key) {
            $item = is_string($item) ?: array_shift($item);
        });

        return $message;
    }

    /**
     * 返回请求处理成功后的API JSON响应
     *
     * @param mixed array | collection  $data
     * @return \Illuminate\Http\JsonResponse
     */
    protected function success($data = [])
    {
        return \Response::json($this->formatResponse($data));
    }

    /**
     * 返回请求出现错误后的API JSON响应
     *
     * @param $statusCode
     * @param $message
     * @return \Illuminate\Http\JsonResponse
     */
    protected function error($statusCode, $message)
    {
        return \Response::json($this->formatResponse([], $statusCode, $message));
    }
}
