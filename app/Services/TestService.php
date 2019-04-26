<?php
/**
 * 示例代码，请删除
 */
namespace App\Services;

use App\Contracts\Repositories\UserRepositoryInterface;

class TestService 
{
    /**
     * @var UserRepositoryInterface
     */
    public $userRepo;

    public function __construct(UserRepositoryInterface $userRepo)
    {
        $this->userRepo = $userRepo;
    }

    /**
     * 执行复杂的操作
     * @param $something
     * @return mixed
     */
    public function complicatedJob($something)
    {
        if ($something == UserRepositoryInterface::TYPE_ADMIN) {
            $this->userRepo->create();
        }
        // TODO

    }


}
