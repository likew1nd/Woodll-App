<?php
/**
 * Created by IntelliJ IDEA.
 * User: Administrator
 * Date: 2019/4/25
 * Time: 22:40
 */

namespace app\admin\controller;
use app\admin\model\Feedback;
use app\admin\model\SoftList;
use app\admin\model\SoftUsers;
use app\admin\model\SoftVer;
use app\admin\model\SuLoginRecord;
use app\BaseController as Controller;
use think\Request;
use think\facade\Db;
use think\log;
use app\admin\model\Users;
use think\facade\Session;


//软件用户API
class SoftUser extends Controller{
    private function syncOnlineStatus($authorId)
    {
        $timeoutTime = time() - 300;

        Db::name('su_login_record')
            ->where('authorid', $authorId)
            ->where('status', 0)
            ->where('heart_time', '<', $timeoutTime)
            ->update(['status' => 1]);

        $activeUserIds = Db::name('su_login_record')
            ->where('authorid', $authorId)
            ->where('status', 0)
            ->where('heart_time', '>=', $timeoutTime)
            ->group('uid')
            ->column('uid');

        if (empty($activeUserIds)) {
            Db::name('soft_users')
                ->where('authorid', $authorId)
                ->where('isonline', 1)
                ->update(['isonline' => 0]);
            return;
        }

        Db::name('soft_users')
            ->where('authorid', $authorId)
            ->whereNotIn('id', $activeUserIds)
            ->where('isonline', 1)
            ->update(['isonline' => 0]);

        foreach ($activeUserIds as $uid) {
            $heartTime = Db::name('su_login_record')
                ->where('authorid', $authorId)
                ->where('uid', $uid)
                ->where('status', 0)
                ->where('heart_time', '>=', $timeoutTime)
                ->max('heart_time');

            Db::name('soft_users')
                ->where('authorid', $authorId)
                ->where('id', $uid)
                ->update(['isonline' => 1, 'heart_time' => $heartTime]);
        }
    }

    function register()
    {
        return json(['code'=>0,'msg'=>'注册成功']);
    }
    //用户列表页面
    function softUsers()
    {
        $user=Session::get('user');
        if(empty($user))
            {$this->assign('title',"超时");$this->assign('keywords',"超时"); return $this->fetch('index/timeout');}

        $this->assign('user',$user);
        $this->assign('title',$user->username." - 用户列表");
        $this->assign('keywords',$user->username. "- 用户列表");
        return $this->fetch('soft/softUsersList');
    }
    //获取所有软件用户
    function getSoftUsersList()
    {
        $user=Session::get('user');
        if(empty($user))
            return json(["msg"=>'登录超时',"code"=>-1]);
        $pageSize = input('limit');
        $sname=input('sname');
        $username=input('username');
        $id=input('id');
        $this->syncOnlineStatus($user->id);

        // 在查询列表前，清理该管理员名下所有超时未心跳的用户为下线状态
        $timeoutTime = time() - 300;
        Db::name('soft_users')
            ->where('authorid', $user->id)
            ->where('isonline', 1)
            ->where('heart_time', '<', $timeoutTime)
            ->update(['isonline' => 0]);

        $where=[
            ['authorid', '=', $user->id],
        ];
        if(!empty($sname)){
            $where[] = ['sname', 'like', '%'.$sname.'%'];
        }
        if(!empty($username)){
            $where[] = ['username', 'like', '%'.$username.'%'];
        }
        if(!empty($id)){
            $where[] = ['id', '=', $id];
            $list = SoftUsers::where($where)->order('id desc')->limit(input('offset'),$pageSize)->select();
        }else{
            //过滤掉不需要的字段
            $list = SoftUsers::where($where)->field(['id','username','sname','maccode','out_time','point','isblacklist','modif_num','heart_time','isonline','status','createtime'])->order('id desc')->limit(input('offset'),$pageSize)->select();
        }
        $total=SoftUsers::where($where)->count();
        return json(["total"=>$total,"rows"=>$list]);
    }
    function editSoftUser()
    {
        //用户需要登录
        $user=Session::get('user');
        if(empty($user))
            {$this->assign('title',"超时");$this->assign('keywords',"超时"); return $this->fetch('index/timeout');}
        $id=input('id');
        if($id<=0)return "用户ID非法";
        $softUser=SoftUsers::findCompat($id);
        if(empty($softUser))return '用户不存在';
        $this->assign('softUser',$softUser);
        $this->assign('sbName','确认修改');
        $this->assign('cnName','取消修改');
        $this->assign('title',"用户编辑");
        $this->assign('keywords',"用户编辑");
        return $this->fetch('users/editSoftUser');
    }
    function addSoftUser()
    {
        //用户需要登录
        $user=Session::get('user');
        if(empty($user))
            {$this->assign('title',"超时");$this->assign('keywords',"超时"); return $this->fetch('index/timeout');}
        $softUser=[
            'id'=>0,
            'username'=>'',
            'password'=>'',
            'phone'=>'',
            'email'=>'',
            'qq'=>'',
            'sid'=>'',
            'maccode'=>'',
            'out_time'=>'',
            'point'=>0,
            'status'=>0,
            'remark'=>'',
            'modif_num'=>0
        ];

        $this->assign('softUser',$softUser);
        $this->assign('sbName','确认添加');
        $this->assign('cnName','取消添加');
        $this->assign('title',"用户编辑");
        $this->assign('keywords',"用户编辑");
        return $this->fetch('users/editSoftUser');
    }
    //添加修改用户
    function updateSoftUser()
    {
        $user=Session::get('user');
        if(empty($user))
            return json(["msg"=>'登录超时',"code"=>-1]);
        //获取用户参数
        $id=input('id');
        $sid=input('sid');
        $soft=SoftList::findCompat($sid);
        if(empty($soft))return json(["msg"=>'选择的软件不存在',"code"=>-5]);
        if($sid<=0)return json(["msg"=>'软件编号错误',"code"=>-4]);
        $all =request()->post();
        if (empty($all['username'])) {
            return json(["msg"=>'请输入用户名',"code"=>-6]);
        }
        $userCheckWhere = [
            ['sid', '=', $sid],
            ['username', '=', $all['username']]
        ];
        if ($id > 0) {
            $userCheckWhere[] = ['id', '<>', $id];
        }
        if (SoftUsers::where($userCheckWhere)->find()) {
            return json(["msg"=>"该用户名在此软件下已存在","code"=>-8]);
        }

        unset($all['password']);
        if(strlen(input('maccode'))>32)return json(["msg"=>'机器码最长32位',"code"=>-7]);
        if($id>0) //修改用户
        {
            if(input('password')!='') //如果修改了密码
            {
                if(strlen(input('password'))<6){
                    return json(["msg"=>"密码长度小于6","code"=>-3]);
                }
                $all['password']=md5(md5(input('password')));
            }

            $SoftUsers=SoftUsers::findCompat($id);
            if( $SoftUsers->update($all)){
                return json(["msg"=>"修改成功","code"=>0]);
            }else{
                return json(["msg"=>"修改失败","code"=>-2]);
            }
        }else
        {
            //检查用户密码长度
            if(strlen(input('username'))<6 || strlen(input('password'))<6 )
                return json(["msg"=>"用户名或者密码长度小于6","code"=>-3]);
            //添加用户
            $all['password']=md5(md5(input('password')));
            $all['authorid']=$user->id;
            $all['sid']=$sid;
            $all['sname']=$soft->name;
            $all['isblacklist']=0;
            $all['modif_num']=0;
            $all['isonline']=0;
            $all['createtime']=time();
            if( SoftUsers::create($all)){
                $soft->count+=1;
                $soft->save();
                return json(["msg"=>"添加成功","code"=>0]);
            }else{
                return json(["msg"=>"添加失败","code"=>-3]);
            }
        }
    }
    //删除用户
    function deleteSoftUser()
    {
        $user=Session::get('user');
        if(empty($user))
            return json(["msg"=>'登录超时',"code"=>-1]);
        $id=input('id');
        if($id<=0)return json(["msg"=>'用户编号不正确',"code"=>-2]);
        $softUser=SoftUsers::findCompat($id);
        if(empty($softUser))return json(["msg"=>'没有找到该用户',"code"=>-3]);
        if($softUser->authorid!=$user->id)return json(["msg"=>'你不能删除不属于你的用户',"code"=>-4]);
        $sl=SoftList::findCompat($softUser->sid);
        if($softUser->delete())
        {
            if($sl){
                $sl->count-=1;
                $sl->save();
            }
            //软件用户数量-1

            return json(["msg"=>'删除成功',"code"=>0]);
        }
        else
        {
            return json(["msg"=>'删除失败',"code"=>-5]);
        }
    }
    //软件用户登录记录页面
    function suLoginRecord()
    {
        $user=Session::get('user');
        if(empty($user))
            {$this->assign('title',"超时");$this->assign('keywords',"超时"); return $this->fetch('index/timeout');}
        $this->assign('user',$user);
        $this->assign('title',$user->username." - 登录记录");
        $this->assign('keywords',$user->username. "- 登录记录");
        return $this->fetch('soft/suLoginRecord');
    }
    //登录记录列表数据
    function getSuLoginRecord()
    {
        $user=Session::get('user');
        if(empty($user))
            return json(["msg"=>'登录超时',"code"=>-1]);
        $pageSize = input('limit');
        $sname=input('sname');
        $username=input('username');
        $id=input('id');
        $this->syncOnlineStatus($user->id);
        $where=[
            ['authorid', '=', $user->id],
        ];
        if(!empty($sname)){
            $where[] = ['sname', 'like', '%'.$sname.'%'];
        }
        if(!empty($username)){
            $where[] = ['username', 'like', '%'.$username.'%'];
        }
        if(!empty($id)){
            $where[] = ['id', '=', $id];
            $list = SuLoginRecord::where($where)->order('id desc')->limit(input('offset'),$pageSize)->select();
        }else{
            //过滤掉不需要的字段
            $list = SuLoginRecord::where($where)->field(['id','sid','softMD5','username','sname','maccode','ip','city','login_time','heart_time','status'])->order('id desc')->limit(input('offset'),$pageSize)->select();
        }
        $total=SuLoginRecord::where($where)->count();
        return json(["total"=>$total,"rows"=>$list]);
    }
    //发送用户消息
    function sendMsgByUid()
    {
        $user=Session::get('user');
        if(empty($user))
            return json(["msg"=>'登录超时',"code"=>-1]);
        $login_id=input('id');
        $msg=input('msg');
        $sr= SuLoginRecord::findCompat($login_id);
        if(empty($sr)) return json(["msg"=>'登录记录不存在',"code"=>-2]);
        if($sr->status==1)return json(["msg"=>'用户已经离线，请刷新',"code"=>-3]);
        $soft=SoftList::findCompat($sr->sid);
        if(empty($soft)) return json(["msg"=>'软件不存在',"code"=>-4]);
        if($soft->isSocket==0)return json(["msg"=>'软件没有开启socket心跳',"code"=>-5]);
        // \GatewayWorker\Lib\Gateway::sendToUid($sr->eid,json_encode(["msg" =>$msg , "code" => 200,"type"=>3]));
        return json(["msg"=>'发送完成',"code"=>0]);
    }
    //群发消息
    function sendMsgBySid()
    {
        $user=Session::get('user');
        if(empty($user))
            return json(["msg"=>'登录超时',"code"=>-1]);
        $sid=input('id');
        $msg=input('msg');
        $soft=SoftList::findCompat($sid);
        if(empty($soft)) return json(["msg"=>'软件不存在',"code"=>-4]);
        if($soft->isSocket==0)return json(["msg"=>'软件没有开启socket心跳',"code"=>-5]);
        // \GatewayWorker\Lib\Gateway::sendToGroup($soft->id,json_encode(["msg" =>$msg , "code" => 200,"type"=>3]));
        return json(["msg"=>'发送完成',"code"=>0]);
    }
    //反馈信息列表
    function feedbackList()
    {
        $user=Session::get('user');
        if(empty($user))
        {$this->assign('title',"超时");$this->assign('keywords',"超时"); return $this->fetch('index/timeout');}
        $this->assign('user',$user);
        $this->assign('title',$user->username." - 反馈信息");
        $this->assign('keywords',$user->username. "- 反馈信息");
        return $this->fetch('soft/feedbackList');
    }
    function getFeedbackList()
    {
        $user = Session::get('user');
        if (empty($user))
            return json(["msg" => '登录超时', "code" => -1]);
        $pageSize = input('limit');
        $sname = input('sname');
        $suname = input('suname');
        $where = [
            ['s.uid', '=', $user->id],
        ];
        if (!empty($sname)) {
            $where[] = ['s.name', 'like', '%' . $sname . '%'];
        }
        if (!empty($suname)) {
            //$where['su.username'] = ['like', '%' . $suname . '%'];
            //  $where['su.username'] = ['like', '%' . $suname . '%'];
        } 
       // return json($where);
        $fb = new Feedback();
        $list = $fb->alias("f")
            ->join("soft_list s", "s.id=f.sid", "LEFT")
            ->join("soft_users su", "su.id=f.suid", "LEFT")
            ->field("f.*,s.name as sname,su.username as suname,su.qq,su.email,su.phone,su.point")
            ->where($where)
            ->limit(input('offset'), $pageSize)
            ->order(['f.id' => 'desc'])
            ->select();
        $total = $fb->alias("f")->join("soft_list s", "s.id=f.sid", "LEFT")->where($where)->count();
        return json(["total" => $total, "rows" => $list]);
    }

}
