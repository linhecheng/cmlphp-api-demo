<?php namespace api\Server;

use Cml\Cml;
use Cml\Config;
use Cml\Model;
use Cml\Secure;
use Cml\Server;
use Cml\View;

class ResponseServer extends Server
{
    /**
     * 渲染json输出
     *
     * @param int $code
     * @param int $msg
     * @param array $data
     */
    public static function renderJson($code, $msg, $data = array())
    {
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
     * @param int $msg
     * @param array $data
     * @param array $req
     */
    public static function renderJsonWithLog($code, $msg, &$data = array(), $req = array())
    {
        if (Config::get('default_cache.driver') == 'Redis') {
            $config = Config::load('api', false);
            if ($config['api_log_on_redis_list_name']) {
                Model::getInstance()->cache()->getInstance()->lPush($config['api_log_on_redis_list_name'], json_encode([
                    'api' => $req['method'],
                    'version' => $req['system']['version'],
                    'params' => json_encode(Secure::stripTags($req), JSON_UNESCAPED_UNICODE),
                    'res' => json_encode([
                        'code' => $code,
                        'method' => $msg,
                        'data' => $data
                    ], JSON_UNESCAPED_UNICODE),
                    'ctime' => Cml::$nowTime
                ], JSON_UNESCAPED_UNICODE ));
            }
        }

        View::getEngine('Json')
            ->assign('code', $code)
            ->assign('msg', $msg)
            ->assignByRef('data', $data)
            ->display();
    }
}