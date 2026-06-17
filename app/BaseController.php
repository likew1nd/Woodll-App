<?php
declare (strict_types = 1);

namespace app;

use think\App;
use think\exception\ValidateException;
use think\Validate;
use think\facade\View;
use think\Response;

/**
 * 控制器基础类
 */
abstract class BaseController
{
    protected $request;
    protected $app;
    protected $batchValidate = false;
    protected $middleware = [];

    public function __construct(App $app)
    {
        $this->app     = $app;
        $this->request = $this->app->request;
        $this->initialize();
    }

    protected function initialize()
    {}

    protected function validate(array $data, $validate, array $message = [], bool $batch = false)
    {
        if (is_array($validate)) {
            $v = new Validate();
            $v->rule($validate);
        } else {
            if (strpos($validate, '.')) {
                [$validate, $scene] = explode('.', $validate);
            }
            $class = false !== strpos($validate, '\\') ? $validate : $this->app->parseClass('validate', $validate);
            $v     = new $class();
            if (!empty($scene)) {
                $v->scene($scene);
            }
        }
        $v->message($message);
        if ($batch || $this->batchValidate) {
            $v->batch(true);
        }
        return $v->failException(true)->check($data);
    }
    
    // ================= TP5 兼容层 =================
    protected function assign($name, $value = '')
    {
        View::assign($name, $value);
        return $this;
    }

    protected function fetch($template = '', $vars = [], $config = [])
    {
        return View::fetch($template, $vars);
    }

    protected function display($content = '', $vars = [], $config = [])
    {
        return View::display($content, $vars);
    }
    
    protected function success($msg = '', $url = null, $data = '', $wait = 3, array $header = [])
    {
        $result = [
            'code' => 1,
            'msg'  => $msg,
            'data' => $data,
            'url'  => $url,
            'wait' => $wait,
        ];
        throw new \think\exception\HttpResponseException(json($result));
    }

    protected function error($msg = '', $url = null, $data = '', $wait = 3, array $header = [])
    {
        $result = [
            'code' => 0,
            'msg'  => $msg,
            'data' => $data,
            'url'  => $url,
            'wait' => $wait,
        ];
        throw new \think\exception\HttpResponseException(json($result));
    }
    
    protected function redirect($url, $params = [], $code = 302, $with = [])
    {
        throw new \think\exception\HttpResponseException(redirect($url));
    }

    public function __get($name)
    {
        if ($name === 'view') {
            return new class {
                public $engine;
                public function __construct() {
                    $this->engine = new class {
                        public function layout($name) {
                            \think\facade\View::layout($name);
                            return $this;
                        }
                    };
                }
            };
        }
        return null;
    }
}
