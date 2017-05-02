<?php
/**
 * V1版本测试接口
 *
 * @date 2015-10-26
 * @author linhecheng
 */

namespace api\Controller\V1;

use api\Logic\V1\TestLogic;

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
        $this->validate->rule('require', ['username', 'password', 'nickname'])
            ->rule('email', 'username');
        $this->runValidate(['username', 'password', 'nickname']);

        //校验通过
        $params['username'] = strip_tags($params['username']);
        $params['nickname'] = strip_tags($params['nickname']);

        $this->renderJson(TestLogic::register($params['username'], $params['password'], $params['nickname']));
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
        $this->validate->rule('require', ['username', 'password'])
            ->rule('email', 'username');
        $this->runValidate(['username', 'password']);

        list($code, $data) = TestLogic::login($params['username'], $params['password']);
        $this->renderJson($code, $data);

    }
}