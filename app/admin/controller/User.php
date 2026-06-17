<?php
/**
 * Created by IntelliJ IDEA.
 * User: Administrator
 * Date: 2019/3/26
 * Time: 18:09
 */

namespace app\admin\controller;

use app\admin\model\SoftList;
use app\admin\model\SoftUsers;
use app\BaseController as Controller;
use think\Request;
use think\facade\Db;
use think\log;
use app\admin\model\Users;
use think\facade\Session;




class User extends Controller
{
    public function profile(){
        $user=Session::get('user');
        if(empty($user))
            {$this->assign('title',"超时");$this->assign('keywords',"超时"); return $this->fetch('index/timeout');}
        if(!empty($user))
            $user=Users::findCompat($user->id);
        $softTotal=SoftList::where(['uid'=>$user->id])->count();
        $onlineExpireTime = time() - 300;
        SoftUsers::where(['authorid'=>$user->id])
            ->where('isonline', 1)
            ->where('heart_time', '<=', $onlineExpireTime)
            ->update(['isonline' => 0]);
            
        // 同时将登录记录表中相对应的过期记录状态设为失效
        Db::name('su_login_record')
            ->where('authorid', $user->id)
            ->where('status', 0)
            ->where('heart_time', '<=', $onlineExpireTime)
            ->update(['status' => 1]);

        $userTotal=SoftUsers::where(['authorid'=>$user->id])->count();
        $onlineTotal=SoftUsers::where(['authorid'=>$user->id])
            ->where('isonline', 1)
            ->where('heart_time', '>', $onlineExpireTime)
            ->count();
        $timeoutTotal=SoftUsers::where(['authorid'=>$user->id])
            ->where('out_time', '<', time())
            ->count();
        $this->assign('sumMoney', 0);
        $this->assign('todayMoney', 0);
        $this->assign('timeoutTotal',$timeoutTotal);
        $this->assign('onlineTotal',$onlineTotal);
        $this->assign('softTotal',$softTotal);
        $this->assign('userTotal',$userTotal);
        $this->assign('user',$user);
        $this->assign('location',$user->loginlist);
        $this->assign('msg',$user->msgbox);
        $this->assign('title',$user->username." - 用户信息");
        $this->assign('keywords',$user->username. "- 个人信息");
        return $this->fetch('index/profile');
    }
    public function changePwd(){
        $user=Session::get('user');
        if(empty($user))
            {$this->assign('title',"超时");$this->assign('keywords',"超时"); return $this->fetch('index/timeout');}
        if(!empty($user))
            $user=Users::findCompat($user->id);
        $this->assign('user',$user);
        $this->assign('title',$user->username." - 修改密码");
        $this->assign('keywords',$user->username. "- 修改密码");
        return $this->fetch('index/change_pwd');
    }
    public function doChangePwd(){
        $user=Session::get('user');
        if(empty($user))
            {$this->assign('title',"超时");$this->assign('keywords',"超时"); return $this->fetch('index/timeout');}
        if(!empty($user))
            $user=Users::findCompat($user->id);
        if(md5(md5(input('oldPwd')))!=$user->password)
            return json( [ "msg"=>"旧密码错误","code"=>-1]);
        $user->password=md5(md5(input('newPwd')));
        $user->save();
        return json( [ "msg"=>"密码修改成功","code"=>0]);
    }
    //用户修改资料
    public function changeInfo(){
        $user=Session::get('user');
        if(empty($user))
            {$this->assign('title',"超时");$this->assign('keywords',"超时"); return $this->fetch('index/timeout');}
        if(!empty($user))
            $user=Users::findCompat($user->id);
        $this->assign('user',$user);
        $this->assign('title',$user->username." - 修改密码");
        $this->assign('keywords',$user->username. "- 修改密码");
        return $this->fetch('index/change_info');
    }
    public function doChangeInfo(){
        $user=Session::get('user');
        if(empty($user))
            {$this->assign('title',"超时");$this->assign('keywords',"超时"); return $this->fetch('index/timeout');}
        if(!empty($user))
            $user=Users::findCompat($user->id);
        if(md5(md5(input('password')))!=$user->password)
            return json( [ "msg"=>"密码错误","code"=>-1]);
        $user->name=input('name')==""?$user->name:input('name');
        $user->email=input('email')==""?$user->email:input('email');
        $user->qq=input('qq')==""?$user->qq:input('qq');
        $user->alipay=input('alipay')==""?$user->alipay:input('alipay');
        $user->save();
        return json( [ "msg"=>"资料修改成功","code"=>0]);
    }
    //忘记密码
    public function forgotpassword(){
        $this->assign('title'," 找回密码");
        $this->assign('keywords', "找回密码");
        return $this->fetch('index/forgotpassword');
    }
    public function doforgotpassword(){
        //检查验证码是否正确
        //if(!verifyMsg(input('phone'),input('code')))
        //    return json(["msg" => "手机验证码错误", "code" => -4]);
	    $request = request();
	    $param = $request->only(['username', 'password', 'email', 'phone', 'code']);
	  //检查邮箱验证码是否正确
	   if(!verifyMailMsg($param['email'],input('code')))
		        return json(["msg" => "邮箱验证码错误", "code" => -4]);
        //手机是否存在
        $user=Users::getByEmail(input('email'));
        if(empty($user)){
            return json(["msg" => "该邮箱未注册", "code" => -2]);
        }
        $user->password=md5(md5(input('password')));
        if($user->save()){
            return json( [ "msg"=>"密码找回成功","password"=>input('password'),'username'=>$user->username,"code"=>0]);
        }else{
            return json( [ "msg"=>"密码找回失败","code"=>-1]);
        }
    }

    //充值
        public function shop()
    {
        $this->assign('title',"用户商城");
        $this->assign('keywords',"xx商城");
        return $this->fetch('index/shop');
    }
}
