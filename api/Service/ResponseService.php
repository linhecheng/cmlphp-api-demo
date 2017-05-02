<?php namespace api\Service;

use api\Controller\BootstrapController;
use api\Controller\V1\CommonController;
use Cml\Cml;
use Cml\Config;
use Cml\Http\Request;
use Cml\Model;
use Cml\Secure;
use Cml\Service;
use Cml\View;

class ResponseService
{
    /**
     * 渲染json输出
     *
     * @param int $code
     * @param mixed $msg
     * @param array $data
     */
    public static function renderJson($code, $msg, $data = [])
    {
        if (is_array($msg)) {
            $data = $msg;
            $msg = '';
        }
        if (!$msg) {
            $api = Config::load('api', false);
            $msg = $api['code'][$code];
        }

        View::getEngine('Json')
            ->assign('code', $code)
            ->assign('msg', $msg)
            ->assign('data', $data)
            ->display();
    }

    /**
     * 渲染json输出并记录log
     *
     * @param int $code
     * @param mixed $msg
     * @param array $data
     */
    public static function renderJsonWithLog($code, $msg = '', &$data = [])
    {
        if (Config::get('default_cache.driver') == 'Redis') {
            $config = Config::load('api', false);
            $req = BootstrapController::getRequestData();

            isset($reg['params']['img']) && $reg['params']['img'] = '';
            isset($reg['params']['pic']) && $reg['params']['pic'] = '';
            isset($reg['params']['logo']) && $reg['params']['logo'] = '';
            isset($reg['params']['bg']) && $reg['params']['bg'] = '';
            isset($reg['params']['ad']) && $reg['params']['ad'] = '';

            $req['ip'] = Request::ip();
            $req['server'] = $_SERVER['HTTP_USER_AGENT'];

            if ($config['api_log_on_redis_list_name']) {
                Model::getInstance()->cache()->getInstance()->lPush($config['api_log_on_redis_list_name'], json_encode([
                    'api' => $req['method'],
                    'ver' => $req['system']['version'],
                    'uid' => CommonController::$uid,
                    'params' => json_encode(Secure::stripTags($req), JSON_UNESCAPED_UNICODE),
                    'response' => json_encode([
                        'code' => $code,
                        'method' => $msg,
                        'data' => $data
                    ], JSON_UNESCAPED_UNICODE),
                    'ctime' => Cml::$nowTime
                ], JSON_UNESCAPED_UNICODE));
            }
        }

        self::renderJson($code, $msg, $data);
    }
}