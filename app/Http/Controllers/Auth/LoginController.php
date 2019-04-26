<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use JWTAuth;
use Auth;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     * @api {POST} /api/login 登录接口
     * @apiGroup Auth
     * @apiversion 0.1.0
     * @apiParam (请求参数:) {String} engineer_code 工程师编号
     * @apiParam (请求参数:) {String} password  密码
     * @apiParam (请求参数:) {String} captcha  验证码
     *
     * @apiSuccess (返回字段:) {Number} StatusCode 状态码
     * @apiSuccess (返回字段:) {JSON} message  提示信息
     * @apiSuccess (返回字段:) {JSON} data  status_code为200时返回的数据包
     *
     * @apiParamExample {json} 请求参数示例：
     *   {
     *     "engineer_code": "A03999",
     *     "password":"1234qwER",
     *     "captcha":"afe5i"
     *   }
     *
     * @apiSuccessExample 成功时返回的数据:
     *  HTTP/1.1 200 Success
     *  {
     *      "statusCode": 200,
     *      "message": {
     *          "info": "Success"
     *      },
     *      "data": {
     *          "name" "陈雪兵",
     *          "engineer_code": "A03999"
     *      }
     *
     *  }
     * @apiErrorExample 失败时返回的数据
     * HTTP/1.1 200 Success
     * {
     *       "statusCode": 403,
     *       "message": {
     *          "info": "token is invalid"
     *      },
     *      "data": []
     * }
     */
    public function apiLogin(Request $request)
    {
        $rules = ['email' => 'required', 'password' => 'required'];
        $this->validate($request, $rules);
        $credentials = $request->only('email', 'password');
        $jwt = JWTAuth::attempt($credentials);//验证用户并返回json web token
        if ($jwt) {
            $user = Auth::user();
            $data = ['name' => $user->name, 'email' => $user->email];
            $response = $this->success($data);
        } else {
            $response = $this->error(10010401, '用户名或密码错误');
        }
        $response->headers->set('Authorization', 'Bearer ' . $jwt);
        $response->headers->set('Access-Control-Expose-Headers', 'Authorization');

        return $response;
    }
}
