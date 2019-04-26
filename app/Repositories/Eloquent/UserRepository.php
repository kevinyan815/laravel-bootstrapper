<?php
/**
 * Created by PhpStorm.
 * User: KevinYan
 * Date: 2019/4/26
 * Time: 3:34 PM
 */

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\UserRepositoryInterface;

class UserRepository extends Repository implements UserRepositoryInterface
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model() : string
    {
        return \App\Models\User::class;
    }
}