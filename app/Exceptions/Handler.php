<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Validation\ValidationException;
use App\Foundation\Traits\ApiTrait;

class Handler extends ExceptionHandler
{
    use ApiTrait;

    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception  $exception
     * @return void
     */
    public function report(Exception $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $exception
     * @return \Illuminate\Http\Response
     */
    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Exception $exception
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $exception)
    {
        //如果客户端预期的是JSON响应,  在API请求未通过Validator验证抛出ValidationException后
        //这里来定制返回给客户端的响应.
        if ($exception instanceof ValidationException && $request->expectsJson()) {
            return $this->error(422, $exception->errors());
        }

        if ($exception instanceof ModelNotFoundException && $request->expectsJson()) {
            //捕获路由模型绑定在数据库中找不到模型后抛出的NotFoundHttpException
            return $this->error(424, 'resource not found.');
        }


        if ($exception instanceof AuthorizationException) {
            //捕获不符合权限时抛出的 AuthorizationException
            return $this->error(403, "Permission does not exist.");
        }

        return parent::render($request, $exception);
    }
}
