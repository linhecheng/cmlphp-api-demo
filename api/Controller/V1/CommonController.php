<?php namespace api\Controller\V1;

use Cml\Cml;
use Cml\Controller;
use api\Service\ResponseService;
use Cml\Plugin;
use Cml\Vendor\Validate;

class CommonController extends Controller
{
    protected $validate = null;

    public function __construct(&$params = [])
    {
        $this->validate = new Validate($params);

        Plugin::hook('before_run_api_action', $params);
    }

    /**
     * 设置提示语并-执行验证
     *
     * @param array $labels 要设置提示语的参数
     */
    public function runValidate($labels = [])
    {
        if ($labels) {
            //处理错误提示语
            $paramErrorMsg = Cml::requireFile(Cml::getApplicationDir('apps_path') . DIRECTORY_SEPARATOR
                . Cml::getContainer()->make('cml_route')->getAppName()
                . DIRECTORY_SEPARATOR . Cml::getApplicationDir('app_config_path_name') . DIRECTORY_SEPARATOR . 'paramErrorMsg.php');

            $label = [];
            foreach ($labels as $param => $tip) {
                if (is_int($param)) {
                    isset($paramErrorMsg[$tip]) && $label[$tip] = $paramErrorMsg[$tip];
                } else {
                    $label[$param] = $tip;
                }
            }
            $this->validate->label($label);
        }
        if (!$this->validate->validate()) {
            $this->renderJson(1, $this->validate->getErrors(2));
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
}