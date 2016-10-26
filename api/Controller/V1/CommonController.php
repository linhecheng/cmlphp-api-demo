<?php namespace api\Controller\V1;

use Cml\Controller;
use api\Server\ResponseServer;

class CommonController extends Controller
{
    protected $requestData = [];

    public function __construct(&$requestData = [])
    {
        $this->requestData = $requestData;
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
        ResponseServer::renderJsonWithLog($code, $msg, $data, $this->requestData);
    }
}