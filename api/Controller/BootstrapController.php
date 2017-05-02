<?php
/**
 * Api总调度
 *
 * @date 2015-04-23
 * @author linhecheng
 */

namespace api\Controller;

use Cml\Cml;
use Cml\Config;
use Cml\Controller;
use Cml\Http\Input;
use Cml\Http\Request;
use Cml\Tools\Apidoc\AnnotationToDoc;
use api\Service\ResponseService;

class BootstrapController extends Controller
{
    /**
     * 请求数据
     *
     * @var array
     */
    private static $requestData = [];


    /**
     * 获取请求数据
     *
     * @return mixed
     */
    public static function getRequestData()
    {
        return self::$requestData;
    }

    /**
     * 设置请求数据
     *
     * @param $requestData
     */
    public static function setRequestData(&$requestData)
    {
        self::$requestData = $requestData;
    }

    public function run()
    {
        $requestData = Request::getBinaryData();

        if (empty($requestData) || !$requestData = json_decode($requestData, true)) {
            $this->renderJson(1001);//error args root
        }

        self::setRequestData($requestData);

        if (!isset($requestData['system']) || !isset($requestData['method']) || !isset($requestData['params'])) {
            $this->renderJson(1002);//error args
        }

        $config = Config::load('api', false);

        //判断来源和key
        if (!isset($requestData['system']['from']) || !isset($requestData['system']['sign']) || !isset($requestData['system']['time'])
            || !isset($config['key'][$requestData['system']['from']])
            || md5($config['key'][$requestData['system']['from']] . $requestData['system']['time']) != $requestData['system']['sign']

        ) {
            $this->renderJson(1003);//error args sign
        }

        //判断请求是否过期
        if (
            isset($config['token_expire'][$requestData['system']['from']]) &&
            $config['token_expire'][$requestData['system']['from']] > 0 &&
            $config['token_expire'][$requestData['system']['from']] + $requestData['system']['time'] < Cml::$nowTime
        ) {
            $this->renderJson(1004);//sign time out!
        }

        //判断ip限制
        if (
            isset($config['ip_deny'][$requestData['system']['from']]) &&
            count($config['ip_deny'][$requestData['system']['from']]) > 0 &&
            !in_array(Request::ip(), $config['ip_deny'][$requestData['system']['from']])
        ) {
            $this->renderJson(1005);//ip deny
        }

        //判断请求的接口版本 及 方法名是否存在
        if (
            !isset($requestData['system']['version']) ||
            !isset($config['version'][$requestData['system']['version']]) ||
            !isset($config['version'][$requestData['system']['version']][$requestData['method']])
        ) {
            $this->renderJson(1006);//error args version/method
        }

        //判断来源是否有某个方法的权限-白名单
        if (
            isset($config['white_list'][$requestData['system']['from']]) &&
            count($config['white_list'][$requestData['system']['from']]) > 0 &&
            (
                !isset($config['white_list'][$requestData['system']['from']][$requestData['system']['version']]) ||
                !in_array($requestData['method'], $config['white_list'][$requestData['system']['from']][$requestData['system']['version']])
            )
        ) {
            $this->renderJson(1007);//access deny
        }

        //判断来源是否有某个方法的权限-黑名单
        if (
            isset($config['black_list'][$requestData['system']['from']]) &&
            count($config['black_list'][$requestData['system']['from']]) > 0 &&
            isset($config['black_list'][$requestData['system']['from']][$requestData['system']['version']]) &&
            in_array($requestData['method'], $config['black_list'][$requestData['system']['from']][$requestData['system']['version']])
        ) {
            $this->renderJson(1007);//'access deny'
        }


        $action = $config['version'][$requestData['system']['version']][$requestData['method']];
        $pos = strrpos($action, '\\');
        $controller = substr($action, 0, $pos);
        $action = substr($action, $pos + 1);

        class_exists($controller) || $this->renderJson(10002, 'not found');
        $api = new $controller($requestData['params']);

        if (method_exists($api, $action)) {
            $api->$action($requestData['params']);
            exit();
        } else {
            $this->renderJson(1008);//not found
        }
    }

    /**
     * 渲染json
     *
     * @param $code
     * @param $msg
     * @param array $data
     */
    protected function renderJson($code = 0, $msg = '', &$data = [])
    {
        ResponseService::renderJsonWithLog($code, $msg, $data);
    }

    /**
     * 从注释生成文档
     *
     */
    public function doc()
    {
        $api = Config::load('api', false);
        in_array(
            Request::ip(),
            $api['access_look_doc_ip_list']
        ) || exit('access deny');

        if ($api['annotation_to_doc'] && Input::getString('key') == $api['annotation_to_doc']) {
            AnnotationToDoc::parse();
        } else {
            exit('access deny');
        }
    }
}
