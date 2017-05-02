<?php
/* * *********************************************************
* 测试逻辑层
* @Author  linhecheng<linhechengbush@live.com>
* @Date: 2017/5/2 14:22
* *********************************************************** */
namespace api\Logic\V1;

use api\Model\UserModel;
use api\Service\LockService;
use Cml\Cml;
use Cml\Encry;

class TestLogic
{
    /**
     * 用户注册相关逻辑
     *
     * @param string $username
     * @param string $password
     * @param string $nickname
     *
     * @return bool
     */
    public static function register($username, $password, $nickname)
    {
        $userModel = new UserModel();
        //判断用户是否注册过
        //这边可能存在并发的情况，所以需要上锁
        $lockUsernameKey = 'user-register-lock-username' . md5($username);

        //以这个用户名为锁
        if (!LockService::lockWait($lockUsernameKey)) {
            return 10001;
        }

        $isExist = $userModel->getByColumn($username, 'username');
        if ($isExist) {//已存在
            LockService::unLockWait($lockUsernameKey);
            return 10001;//用户名已被注册
        }

        //判断昵称是否注册过
        $lockNickKey = 'user-register-lock-nickname' . md5($nickname);
        if (LockService::lockWait($lockNickKey)) {//以这个用户名为锁
            $isExist = $userModel->getByColumn($nickname, 'nickname');
            if ($isExist) {//已存在
                LockService::unLockWait($lockNickKey);
                return 10002;//昵称已被注册
            }

            //校验通过
            //入库
            $userModel->set([
                'username' => $username,
                'nickname' => $nickname,
                'ctime' => Cml::$nowTime,
                'password' => md5(md5($password) . 'ei3nns-dx,ngen-xelekn')//这边密码salt只是示范用，最好写到配置里
            ]);
        }

        LockService::unLockWait($lockUsernameKey);
        LockService::unLockWait($lockNickKey);
        return 0;
    }

    public static function login($username, $password)
    {
        //校验通过
        $userModel = new UserModel();
        $user = $userModel->getByColumn($username, 'username');
        if (!$user) {
            return [10003, []];//用户不存在
        }

        if ($user['password'] != md5(md5($password) . 'ei3nns-dx,ngen-xelekn')) {
            return [10005, []];//用户密码错!
        }

        //登录成功
        //生成sid
        $data = [
            'username' => $user['username'],
            'nickname' => $user['nickname'],
            'sid' => Encry::encrypt($user['id'], 'emnnt,lp3ere-elng.e-ere,.snf.er')//只是一个例子加密方式有很多,加密key也不适合放这边
        ];

        return [0, $data];
    }
}