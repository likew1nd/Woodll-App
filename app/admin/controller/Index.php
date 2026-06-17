<?php
namespace app\admin\controller;

use app\BaseController as Controller;
use think\Request;
use think\facade\Db;
use think\log;
use app\admin\model\Users;
use think\facade\Session;


class Index extends Controller
{
    public function index()
    {
        if (cookie('autoLogin') == 1) {
            //自动登录
            $user = Users::getByUsername(cookie('username'));
            if (!empty($user)) {
                if (cookie('token') == md5($user->username . $user->password . "" . SECRET))
                    Session::set('user', $user);
            }
        }
        $user = Session::get('user');
        if (!empty($user))
            $user = Users::findCompat($user->id);
        if (empty($user)) { return redirect('/login.html'); }
        $this->assign('user', $user);
        $this->assign('title', "WOODLL 网络验证");
        $this->assign('keywords', "WOODLL 网络验证");
        return $this->fetch();
    }

    public function doLogin()
    {
        $request = request();
        $param = $request->only(['username', 'password']);
        $param['password'] = md5(md5($param['password']));
        //检查用户名 和 密码是否匹配
        $user = Users::findCompat($param);
        if (empty($user)) {
            return json(["msg" => "用户名或者密码错误", "code" => -2]);
        } else {
            Session::set('user', $user);
            if (input('autoLogin') == "on") {
                $tokenSecret = defined('SECRET') ? SECRET : 'default_salt';
                cookie('username', $user->username, 86400);//保持1天
                cookie('autoLogin', 1, 86400);//保持1天
                cookie('token', md5($user->username . $user->password . "" . $tokenSecret), 86400);//保持1天
            }
            //记录登录IP信息
            try {
                // 检查函数是否存在，防止 500 错误
                $ip = function_exists('getIp') ? getIp() : $request->ip();
                $city = findCityByIp($ip);
                $location = $city ?: '';
                $user->loginlist()->save(['ip' => $ip, 'location' => $location, 'login_time' => time()]);
            } catch (\Throwable $e) {
                // Keep login successful even if geo lookup or login-log persistence fails.
            }
            return json(["msg" => "登录成功" . $user->username, "code" => 0]);
        }
    }

    //注册
    public function doRegister()
    {
        return json(["msg" => "系统已关闭注册功能！", "code" => -4]);
    }

    //发送注册短信
    public function doSendMsg()
    {
        $captcha = input('captcha');
        if (!captcha_check($captcha)) {
            //验证失败
            return json(["msg" => "图片验证码错误", "code" => -1]);
        };
        //检查手机号是否注册
        if (!empty(Users::getByPhone(input('phone')))) {
            return json(["msg" => "手机已经存在", "code" => -2]);
        }
        //发送短信验证码
        return SendMsg(input('phone')) ? json(["msg" => "发送成功", "code" => 0]) : json(["msg" => "发送失败", "code" => 0]);

    }
	    //发送注册短信
    public function doSendMailMsg()
    {
        $captcha = input('captcha');
        if (!captcha_check($captcha)) {
            //验证失败
            return json(["msg" => "图片验证码错误", "code" => -1]);
        };
        //检查手机号是否注册
        if (!empty(Users::getByEmail(input('email')))) {
            return json(["msg" => "邮箱已经存在", "code" => -2]);
        }
        //发送短信验证码
        return SendMailCode(input('email')) ? json(["msg" => "发送成功", "code" => 0]) : json(["msg" => "发送失败,可能发送频繁", "code" => -1]);

    }
	//发送找回密码短信
   public function doSendMailMsg1()
    {
        $captcha = input('captcha');
        if (!captcha_check($captcha)) {
            //验证失败
            return json(["msg" => "图片验证码错误", "code" => -1]);
        };
        //检查手机号是否注册
        if (empty(Users::getByEmail(input('email')))) {
            return json(["msg" => "邮箱不存在", "code" => -2]);
        }
        //发送短信验证码
        return SendMailCode(input('email')) ? json(["msg" => "发送成功", "code" => 0]) : json(["msg" => "发送失败,可能发送频繁", "code" => -1]);

    }
    public function doSendMsg1()
    {
        $captcha = input('captcha');
        if (!captcha_check($captcha)) {
            //验证失败
            return json(["msg" => "图片验证码错误", "code" => -1]);
        };
        //检查手机号是否注册
        if (empty(Users::getByPhone(input('phone')))) {
            return json(["msg" => "手机号未注册", "code" => -2]);
        }
        //发送短信验证码
        return SendMsg(input('phone')) ? json(["msg" => "发送成功", "code" => 0]) : json(["msg" => "发送失败", "code" => 0]);

    }

    //登出
    public function logout()
    {
        cookie('username', null);
        cookie('autoLogin', null);
        cookie('token', null);
        session('user', null);
        return redirect('/login.html');
    }

    public function index_v1()
    {
        $this->assign('title', "首页");
        $this->assign('keywords', "首页");
        return $this->fetch();
    }

    public function Update_avatar()
    {
        $file = request()->file('__avatar1');
        if ($file) {
            $id = time();
            $destDir = app()->getRootPath() . 'public' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'avatar';
            $fileName = $id . ".jpg";
            try {
                validate([
                    '__avatar1' => 'fileSize:2097152|fileExt:jpg,png,gif,jpeg'
                ])->check(['__avatar1' => $file]);

                $info = $file->move($destDir, $fileName);
                if ($info) {
                    $sessionUser = session('user');
                    if ($sessionUser) {
                        $user = Users::findCompat($sessionUser->id);
                        if ($user) {
                            $user->avatar = $info->getFilename();
                            $user->save();
                            session('user', $user);
                        }
                    }
                    return json(["success" => true, "avatarUrls" => array("/uploads/avatar/" . $info->getFilename())]);
                } else {
                    return json(["success" => false, "msg" => "上传失败"]);
                }
            } catch (\Throwable $e) {
                return json(["success" => false, "msg" => $e->getMessage()]);
            }
        }
    }

        public function login()
    {
        $user = Session::get('user');
        if (!empty($user)) {
            return redirect('/');
        }
        $this->assign('title', "登录");
        $this->assign('keywords', "登录");
        return $this->fetch('index/login');
    }

    function APIDOC()
    {
        $this->assign('title', "API");
        $this->assign('keywords', "API");
        return $this->fetch("api");
    }
}
