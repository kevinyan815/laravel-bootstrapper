<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Contracts\Repositories\UserRepositoryInterface;

class TestController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth.jwt');
    }

    /**
     * @param Request $request
     * @param UserRepositoryInterface $userRepo
     * @return \Illuminate\Http\JsonResponse
     */
    public function test(Request $request, UserRepositoryInterface $userRepo)
    {
        $this->validate($request, [
            'test' => 'required'
        ]);
        $data = $userRepo->all();
        return $this->success($data);

    }
}
