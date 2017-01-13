<?php
/**
 * V1版本测试接口
 *
 * @date 2015-10-26
 * @author linhecheng
 */
namespace api\Controller\V1;

use api\Model\V1\UserModel;
use api\Server\LockServer;
use Cml\Cml;
use Cml\Encry;
use Cml\Vendor\Validate;

class TestController extends CommonController
{
    /**
     * @doc
     * @desc 用户注册接口
     * @param email $username 用户名 Y
     * @param string $nickname 昵称 Y
     * @param string $password 密码 Y
     *
     * @req     {
     * "system": {
     * "version":"V1",
     * "from": "web",
     * "sign": "a101f236d049b9ad80e012113e59f9bc",
     * "time": "1445482366"
     * },
     * "method": "1-1",
     * "params": {
     * "username" : "test001@163.com",
     * "password" : "123456",
     * "nickname" : "我是测试账号1"
     * }
     * }
     * @success {
     * code: 0
     * msg: "注册成功"
     * data: []
     * }
     * @error   {
     * code: -1
     * msg: "用户名已被注册"
     * data: []
     * }
     */
    public function register($params = [])
    {
        $v = new Validate($params);
        $v->rule('require', ['username', 'password', 'nickname']);
        $v->rule('email', 'username');
        if (!$v->validate()) {
            $error = $v->getErrors();
            $this->renderJson(1, $error);
        }

        //校验通过
        $params['username'] = strip_tags($params['username']);
        $params['nickname'] = strip_tags($params['nickname']);

        $userModel = new UserModel();
        //判断用户是否注册过
        //这边可能存在并发的情况，所以需要上锁
        $lockUsernameKey = 'user-register-lock-username' . md5($params['username']);
        if (LockServer::lockWait($lockUsernameKey)) {//以这个用户名为锁
            $isExist = $userModel->getByColumn($params['username'], 'username');
            if ($isExist) {//已存在
                LockServer::unLockWait($lockUsernameKey);
                $this->renderJson(10001);//用户名已被注册
            }

            //判断昵称是否注册过
            $lockNickKey = 'user-register-lock-nickname' . md5($params['nickname']);
            if (LockServer::lockWait($lockNickKey)) {//以这个用户名为锁
                $isExist = $userModel->getByColumn($params['nickname'], 'nickname');
                if ($isExist) {//已存在
                    LockServer::unLockWait($lockNickKey);
                    $this->renderJson(10002);//昵称已被注册
                }

                //校验通过
                //入库
                $userModel->set([
                    'username' => $params['username'],
                    'nickname' => $params['nickname'],
                    'ctime' => Cml::$nowTime,
                    'password' => md5(md5($params['password']) . 'ei3nns-dx,ngen-xelekn')//这边密码salt只是示范用，最好写到配置里
                ]);
            }

            LockServer::unLockWait($lockUsernameKey);
            LockServer::unLockWait($lockNickKey);
            $this->renderJson(0);
        }

        $this->renderJson(10001);
    }

    /**
     * @doc
     * @desc 用户登录接口
     * @param email $username 用户名 Y
     * @param string $password 密码 Y
     *
     * @req     {
     * "system": {
     * "version":"V1",
     * "from": "web",
     * "sign": "a101f236d049b9ad80e012113e59f9bc",
     * "time": "1445482366"
     * },
     * "method": "1-2",
     * "params": {
     * "username" : "test001@163.com",
     * "password" : "123456"
     * }
     * }
     * @success {
     * code: 0
     * msg: "登录成功"
     * data: {
     * username: "test001@163.com"
     * nickname: "我是测试账号1"
     * sid: "BwQCVQAACQZCCwALFwYTDg___c___c"
     * }
     *
     * }
     * @error   {
     * code: -1
     * msg: "用户密码错"
     * data: []
     * }
     */
    public function login($params = [])
    {
        $v = new Validate($params);
        $v->rule('require', ['username', 'password']);
        $v->rule('email', 'username');
        if (!$v->validate()) {
            $error = $v->getErrors();
            $this->renderJson(1, $error);
        }

        //校验通过
        $userModel = new UserModel();
        $user = $userModel->getByColumn($params['username'], 'username');
        if (!$user) {
            $this->renderJson(10003);//用户不存在
        }

        if ($user['password'] != md5(md5($params['password']) . 'ei3nns-dx,ngen-xelekn')) {
            $this->renderJson(10005);//用户密码错!
        }

        //登录成功
        //生成sid
        $data = [
            'username' => $user['username'],
            'nickname' => $user['nickname'],
            'sid' => Encry::encrypt($user['id'], 'emnnt,lp3ere-elng.e-ere,.snf.er')//只是一个例子加密方式有很多,加密key也不适合放这边
        ];
        $this->renderJson(0, $data);

    }
}