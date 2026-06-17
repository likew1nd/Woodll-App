<?php
/**
 * Created by IntelliJ IDEA.
 * User: Administrator
 * Date: 2019/4/28
 * Time: 1:07
 */

namespace app\admin\controller;

use app\admin\model\CardRecord;
use app\admin\model\Cards;
use app\admin\model\Feedback;
use app\admin\model\ForwardUrl;
use app\admin\model\RemoteFunction;
use app\admin\model\SoftList;
use app\admin\model\SoftUsers;
use app\admin\model\SoftVer;
use app\admin\model\SuLoginRecord;
use app\admin\model\Variable;
use Crypt\encode;
use Crypt\RSA;
use app\BaseController as Controller;
use think\Request;
use think\facade\Db;
use think\log;
use think\facade\Session;


class Api extends Controller
{
    public $enc;

    private function dbPrefix()
    {
        $default = config('database.default') ?: 'mysql';
        return config('database.connections.' . $default . '.prefix') ?: '';
    }

    function webgateway()
    {
        header("Content-type:text/html; charset=UTF-8");
        $data = file_get_contents("php://input");
        $id = input("server.HTTP_APPID");
        if ($id <= 0) return json(["msg" => "参数不正确", "code" => -10001]);
        $sf=new SoftList;
        $soft = $sf->where(['id'=>$id])->find();
        if (empty($soft)) return json(["msg" => "软件不存在", "code" => -10002]);
        $key = input("server.HTTP_INIT") == 1 ? substr($soft->key, 0, 24) : $soft['privateKey'];
        $privateSalt = $soft->privateSalt;
        $encryptType = input("server.HTTP_INIT") == 1 ? 0 : $soft->encryptType;
        $this->enc = new encode($key, $encryptType);
        $res = json_decode($this->enc->su_decrypt($data), true);
        if ($res['wtype'] == 1) $privateSalt = $key;
        $data = count($res['data']) == 1 ? $res['data'][0] : $res['data'];
        if ($res == null || empty($res)) return json(["msg" => "参数解密失败", "data" => ['key' => $key, 'encryptType' => $encryptType, 'data' => file_get_contents("php://input")], "code" => -10010]);
        //查询封包时间 与 服务器时间相差是否太大
        //$timetiff=($res['timestamp']/1000)-time();
        //if($timetiff>50 || $timetiff<-50)return $this->enc->encodeJson(["msg" => "封包时间与服务器时间相差太多".$timetiff , "data" => [], "code" => -10045]);
        if (!$this->enc->verifySign($res, $privateSalt)) return $this->enc->encodeJson(["msg" => "sign验证失败" . $privateSalt, "data" => [], "code" => -10003]);
        //选择操作类型
        switch ($res['wtype']) {
            case 1: //初始化
                return $this->init($id, $data, $soft);
                break;
            case 2: //注册
                return $this->register($id, $data, $soft);
                break;
            case 3: //登录
                return $this->login($id, $data, $soft);
                break;
            case 4: //心跳
                return $this->heart($id, $data, $soft);
                break;
            case 5: //退出登录
                return $this->quit($id, $data, $soft);
                break;
            case 6: //修改密码
                return $this->changePassword($id, $data, $soft);
                break;
            case 7: //软件用户充值
                return $this->deposit($id, $data, $soft);
                break;
            case 8: //远程变量
                return $this->Variable($id, $data, $soft);
                break;
            case 9: //远程算法
                return $this->RemoteFunction($id, $data, $soft);
                break;
            case 10: //算法转发
                return $this->ForwardUrl($id, $data, $soft);
                break;
            case 11: //扣点
                return $this->makePoint($id, $data, $soft);
                break;
            case 12: //查询用户信息
                return $this->getUserInfo($id, $data, $soft);
                break;
            case 13: //修改绑定
                return $this->changeMaccode($id, $data, $soft);
                break;
            case 14: //扣点
                return $this->makeTime($id, $data, $soft);
                break;
            case 15: //查询版本信息
                return $this->queryVer($id, $data, $soft);
                break;
            case 16: //查询卡密信息
                return $this->queryCard($id, $data, $soft);
                break;
            case 17: //反馈消息
                return $this->feedback($id, $data, $soft);
                break;
            default:
        }

    }

    //初始化
    function init($id, $data, $soft)
    {
        //查询版本
        $ver = SoftVer::where(["sid" => $id, "ver" => $data['version']])->find();
        if (empty($ver)) return $this->enc->encodeJson(["msg" => "版本不存在", "data" => [], "code" => -10004]);
        $soft['ver'] = $ver;
        //版本是否启用
        if ($ver['status'] != 0) return $this->enc->encodeJson(["msg" => "版本停用", "data" => [], "code" => -10005]);
        //RSA加密密钥
        $Rsa=new RSA();
        $keys=$Rsa->new_rsa_key();
        $privkey = $keys['privkey'];
        $pubkey  = $keys['pubkey'];
        $Rsa->init($privkey, $pubkey,TRUE);
        $privateSalt = $Rsa->pub_encode(strrev($soft->privateSalt));
        $privateKey=$Rsa->pub_encode(strrev($soft->privateKey));
        //原文
        $soft->privateSalt=$privateSalt;
        $soft->privateKey=$privateKey;
        $arr=explode("\n",$privkey);
        $priv="";
        for($i=1;$i<count($arr)-2;$i++)
        {
            $priv=$priv. $arr[$i];
        }
        $soft->encKey=$priv;
        //是否强制更新
        if ($ver['checkUpdate'] == 0 && $ver['ver'] != $soft['version']) return $this->enc->encodeJson(["msg" => "请更新到最新版本:" . $soft['version'], "data" => $soft, "code" => -10006]);
        //排除一些字段
       // ->field('status,regMacLimit,regIpLimit,maxProxyLevel,ipPublicProxy,freeChangeBundled,timeFree,uid,count,openReg,regFreePoint,regFree,timeFreePointEnd,timeFreePointStart,verifyMode,pointStep,topLoginType,multiType,isModifyMac,expireTime,isProxy,proxy_remark',true)
        $data=array_diff_key($soft->toArray(), ["status"=>"","regMacLimit"=>"","regIpLimit"=>"","maxProxyLevel"=>"","ipPublicProxy"=>"","freeChangeBundled"=>"","timeFree"=>"","uid"=>"","count"=>"","openReg"=>"","regFreePoint"=>"","regFree"=>"","timeFreePointEnd"=>"","timeFreePointStart"=>"","verifyMode"=>"","pointStep"=>"","topLoginType"=>"","multiType"=>"","isModifyMac"=>"","expireTime"=>"","isProxy"=>"","proxy_remark"]);
        return $this->enc->encodeJson(["msg" => "成功", "data" => $data, "code" => 200]);
    }

    //软件用户注册
    function register($id, $data, $soft)
    {
        //软件是否关闭注册
        if($soft->openReg==1)return $this->enc->encodeJson(["msg" => "软件关闭了注册", "data" => [], "code" => -10048]);
        //用户名是否存在
        if (SoftUsers::where(['sid' => $id, 'username' => $data['username']])->find()) return $this->enc->encodeJson(["msg" => "用户名已经被注册啦", "data" => [], "code" => -10008]);
        if (strlen($data['password']) < 6 || strlen($data['username']) < 6) return $this->enc->encodeJson(["msg" => "用户名或密码长度不能小于6", "data" => [], "code" => -10023]);
        //处理注册限制
        $ip = getIp();
        $prefix=$this->dbPrefix();
        //取出该IP 或者 该 机器码今日注册数据
        $prefix=$this->dbPrefix();
        $userList=Db::query("SELECT * FROM `".$prefix."soft_users` WHERE  `sid` = ".$id."  AND `createtime` >= ".mktime(0,0,0,date('m'),date('d'),date('Y'))." AND ( `maccode` = '".$data['maccode']."'  OR `ip` = '".$ip."')");
        $macCount=0;
        $ipCount=0;
        foreach ($userList as $k => $v) {
            if($v['ip']==$ip)
                $ipCount++;
            if($v['maccode']==$data['maccode'])
                $macCount++;
        }
        //IP注册限制
        if($ipCount>=$soft->regIpLimit && $soft->regIpLimit!=0)
            return $this->enc->encodeJson(["msg" => "今天注册太多次啦,明天再来吧", "data" => [], "code" => -10049]);
        if($macCount>=$soft->regMacLimit && $soft->regMacLimit!=0)
            return $this->enc->encodeJson(["msg" => "今天注册太多次啦,明天再来吧", "data" => [], "code" => -10049]);
        //机器码注册限制
        //$data['maccode']
        //处理注册赠送
        if ($soft['regFree'] == 1) {
            //赠送时间
            $data['point'] =0;
            $data['out_time'] = time() + ($soft['regFreePoint'] * 60);
        } else if ($soft['regFree'] == 2) {
            //赠送点数
            $data['point'] = $soft['regFreePoint'];
            $data['out_time'] = time();
        } else{
            //不赠送的情况
            $data['point'] =0;
            $data['out_time'] = time();
        }

        $city = findCityByIp($ip);
        $data['modif_num']=$soft['freeChangeBundled'];
        $data['ip'] = $ip;
        $data['city'] = $city;
        $data['sid'] = $id;
        $data['sname'] = $soft['name'];
        $data['heart_time'] = time();
        $data['createtime'] = time();
        $data['authorid'] = $soft['uid'];
        $data['password'] = md5(md5($data['password']));
        // $data['city'] = $city;
        if (SoftUsers::create($data)){
            //软件表给用户数量加1
            $soft->count+=1;
            $soft->save();
            return $this->enc->encodeJson(["msg" => "恭喜您注册成功!", "data" => [], "code" => 200]);
        }
        else
        {
            return $this->enc->encodeJson(["msg" => "注册失败!", "data" => [], "code" => -10009]);
        }
    }

    //软件用户登录
    function login($id, $data, $soft)
    {
        if (strlen($data['username']) < 6 || strlen($data['password']) < 6) return $this->enc->encodeJson(["msg" => "用户名或者密码长度小于6", "data" => [], "code" => -10012]);
        //验证软件状态
        if ($soft['status'] == '关闭验证') return $this->enc->encodeJson(["msg" => "软件关闭了验证", "data" => [], "code" => -10011]);
        //检查用户账号密码
        $user = SoftUsers::where(['username' => $data['username'], "password" => md5(md5($data['password'])), "sid" => $id])->find();
        if ($user) {
            //用户是否被禁止
            if ($user->status == 1) return $this->enc->encodeJson(["msg" => "用户被锁定,禁止登陆", "data" => [], "code" => -10016]);
            //顶号登录,仅允许最后登录的在线
            if ($soft->topLoginType == 1) {
                //如果登录过就把之前的下线
                $sr = new SuLoginRecord;
                $sr->save([
                    'status' => 1 //把之前该用户所有在线的都踢下线
                ], ['sid' => $id, "username" => $data['username'], "status" => 0]);
            }

            //是否超出多开现在
            $sr = SuLoginRecord::where(['sid' => $id, "username" => $data['username'], "status" => 0])->select();
            if (count($sr) >= $soft->multiTypeValue) {
                return $this->enc->encodeJson(["msg" => "用户最多同时登陆" . $soft->multiTypeValue . "个软件,当前登录数量" . count($sr).",如果判断错误，请等待5分钟后登录", "data" => [], "code" => -10018]);
            } else {
                //多开情况
                //是否验证账号+机器码,其它直接放行
                if (strtoupper($data['maccode']) != strtoupper($user->maccode) && $soft->multiType == 0) {
                    return $this->enc->encodeJson(["msg" => "软件仅限制在绑定的的机器上使用", "data" => [], "code" => -10021]);
                }
            }
            //如果免费开放,直接通过验证
            if ($soft['status'] == '免费') {
                goto ok;
            }
            //检查用户剩余时间或者点数
            if ($soft->verifyMode == 0)//验证时间
            {
                if (strtotime($user->out_time) <= time()) return $this->enc->encodeJson(["msg" => "软件使用时间" . $user->out_time . "已经过期啦,请续费", "data" => [], "code" => -10014]);
            } else {
                //验证点数
                if ($user->point < $soft['pointStep']) return $this->enc->encodeJson(["msg" => "软件使用点数剩余" . $user->point . ",已经不足啦,至少需要" . $soft['pointStep'] . "点,请续费", "data" => [], "code" => -10015]);
                //扣除点数
                $user->point -= $soft['pointStep'];
                $user->save();
            }
        } else {
            return $this->enc->encodeJson(["msg" => "用户账户或密码不正确", "data" => [], "code" => -10013]);
        }
        ok:
        $ip = getIp();
        $city = findCityByIp($ip);
        //保存登录记录
        $sr = new SuLoginRecord();
        $sr->data([
            "sid" => $id,
            "sname" => $soft['name'],
            "uid" => $user->id,
            "username" => $data['username'],
            "maccode" => $data['maccode'],
            "ip" => $ip,
            "city" => $city,
            "login_time" => time(),
            "heart_time" => time()-20, //减去20是因为 立马登录心跳会 出现 距离上次心跳时间 相差小于15
            "authorid" => $soft->uid,
            "eid"=>md5(guid())
        ]);
        $record = $sr->save();
        if (!$record) $this->enc->encodeJson(["msg" => "用户登录记录保存失败", "data" => [], "code" => -10017]);
        $user['login_record'] = $sr->eid;
        //更改软件用户表用户状态为在线
        $su = new SoftUsers();
        $su->where("id", $user['id'])->update(['isonline' => 1, 'heart_time' => time()]);
        return $this->enc->encodeJson(["msg" => "用户登录成功", "data" => $user, "code" => 200]);
    }

    //将所有300秒内没有心跳的用户设置为下线,可能是意外关闭,心跳终止
    function clearDummy()
    {
        $timeoutTime = time() - 300;
        
        // 1. 更新用户表中的在线状态为下线
        Db::name('soft_users')
            ->where('isonline', 1)
            ->where('heart_time', '<', $timeoutTime)
            ->update(['isonline' => 0]);
            
        // 2. 更新登录记录表中的在线状态为失效
        Db::name('su_login_record')
            ->where('status', 0)
            ->where('heart_time', '<', $timeoutTime)
            ->update(['status' => 1]);
            
        return json(["msg" => "清理完成", "code" => 0]);
    }

    //退出登录
    function quit($id, $data, $soft)
    {
        //login_record
        $sr = SuLoginRecord::getByEid($data['login_record']);
        $sr->status = 1;
        $sr->save();
        $uid = $sr->uid;
        $su = new SoftUsers();
        $su->where("id", $uid)->update(['isonline' => 0, 'heart_time' => time()]);
    }

    //心跳包
    function heart($id, $data, $soft,$enc=null)
    {
        if($enc!=null)$this->enc=$enc;
        if (!array_key_exists('login_record', $data) || !array_key_exists('softMD5', $data)) return $this->enc->encodeJson(["msg" => "参数不正确", "data" => json($data), "code" => -10001,"type"=>2]);
        $sr = SuLoginRecord::getByEid($data['login_record']);
        if (!$sr) return $this->enc->encodeJson(["msg" => "未找到登录记录1", "data" => $data['login_record'], "code" => -10018,"type"=>2]);
        if ($sr->status == 1) return $this->enc->encodeJson(["msg" => "登录状态已经失效", "data" => [], "code" => -10044,"type"=>2]);
        $uid = $sr->uid;
        $su = SoftUsers::findCompat($uid);
        if (!$su) return $this->enc->encodeJson(["msg" => "用户不存在", "data" => [], "code" => -10019,"type"=>2]);
        if ($su->status != 0) return $this->enc->encodeJson(["msg" => "用户状态被锁定", "data" => [], "code" => -10020,"type"=>2]);
        //是否验证账号+机器码,其它直接放行
        if (strtoupper($data['maccode']) != strtoupper($su->maccode) && $soft->multiType == 0)
        {
            return $this->enc->encodeJson(["msg" => "软件仅限制在绑定的的机器上使用", "data" => [], "code" => -10021,"type"=>2]);
        }
        //检验用户是否到期
        if ($soft->verifyMode == 0 && $soft['status'] == '收费')//验证时间
        {
            if (strtotime($su->out_time) <= time()) {
                $sr->status = 1;
                $sr->save();
                $su->where("id", $uid)->update(['isonline' => 0, 'heart_time' => time()]);
                return $this->enc->encodeJson(["msg" => "软件使用时间" . $su->out_time . "已经过期啦,请续费", "data" => [], "code" => -10014,"type"=>2]);
            }
        }
        //检查上次心跳距离现在是否超过15秒
        if (time() - (int)$sr->heart_time < 15) return $this->enc->encodeJson(["msg" => "两次心跳时间小于15秒", "data" => [], "code" => -10040,"type"=>2]);
        $sr->softMD5 = $data['softMD5'];
        //更新心跳时间,登录记录的心跳
        $sr->heart_time = time();
        $sr->save();
        //软件用户的心跳
        $su->heart_time = time();
        $su->isonline = 1;
        $su->save();
        return $this->enc->encodeJson(["msg" => "成功", "type"=>2,"data" => ["timestamp"=>time()], "code" => 200,"type"=>2]);
    }

    //用户修改密码
    function changePassword($id, $data, $soft)
    {
        if (strlen($data['newPassword']) < 6) return $this->enc->encodeJson(["msg" => "新密码长度不能小于6", "data" => [], "code" => -10023]);
        $su = SoftUsers::where(['username' => $data['username'], 'password' => md5(md5($data['oldPassword']))])->find();
        if (!$su) return $this->enc->encodeJson(["msg" => "用户名或密码错误", "data" => [], "code" => -10022]);
        $su->password = md5(md5($data['newPassword']));
        return $su->save() ? $this->enc->encodeJson(["msg" => "密码修改成功", "data" => [], "code" => 200]) : $this->enc->encodeJson(["msg" => "密码修改失败", "data" => [], "code" => -10024]);
    }

    //用户充值
    function  deposit($id, $data, $soft)
    {
        //用户名,充值卡
        $user = SoftUsers::where(["username" => $data['username'], "sid" => $id])->find();
        if (!$user) return $this->enc->encodeJson(["msg" => "用户不存在", "data" => [], "code" => -10013]);
        //查询用户状态
        if ($user->status == 1) return $this->enc->encodeJson(["msg" => "用户被锁定,禁止登陆", "data" => [], "code" => -10016]);
        $card = Cards::where(["card_no"=>$data['card_no'],"sid"=>$id])->find();
        if (!$card) return $this->enc->encodeJson(["msg" => "充值卡有误", "data" => [], "code" => -10025]);
        //查询充值卡类型 点卡 时间卡
        //卡是否被使用或者禁止 !=0
        if ($card->status == 1) return $this->enc->encodeJson(["msg" => "充值卡被使用过了", "data" => [], "code" => -10027]);
        if ($card->status == 2) return $this->enc->encodeJson(["msg" => "充值卡被封停", "data" => [], "code" => -10028]);
        $beforTime = strtotime($user->out_time);
        $beforPoint = $user->point;

        if ($card->type == 0) {
            //点卡充值  直接加上去
            $user->point += $card->card_value;
        } else {
            //时间点卡
            //如果到期时间小于当前时间,充值后的时间登陆 当前时间+充值卡时间 大于等于 到期时间+充值卡时间
            $out = strtotime($user->out_time) < time() ? time() : strtotime($user->out_time);
            $beforTime = $out;
            $value = 0;
            switch ($card->type) {
                case 1: //分
                    $value = $card->card_value * 60;
                    break;
                case 2: //时
                    $value = $card->card_value * 3600;
                    break;
                case 3: //天
                    $value = $card->card_value * 86400;
                    break;
                case 4: //周
                    $value = $card->card_value * 604800;
                    break;
                case 5: //月 31天
                    $value = $card->card_value * 2678400;
                    break;
                case 6: //年 365天
                    $value = $card->card_value * 31536000;
                    break;
                default:
            }
            $o_time=$out + $value;
            //判断是否整型溢出
            if($out==2147483647)return $this->enc->encodeJson(["msg" => "到期时间已经是系统最大到期时间2038", "data" => [], "code" => -10046]);
            if($o_time>2147483647)$o_time=2147483647;
            $user->out_time = $o_time;//date('Y-m-s h:i:s',$o_time);
        }
        //更改卡密状态
        $card->status = 1;//已经使用
        $card->user_account = $data['username'];
        if (!$card->save()) return $this->enc->encodeJson(["msg" => "充值卡状态保存失败", "data" => [], "code" => -10029]);
        if ($user->save()) {
            //添加到充值记录表
            $list = [
                'sid' => $card->sid,
                'sname' => $card->sname,
                'authorid' => $card->authorid,
                'card_no' => $card->card_no,
                'type' => $card->type,
                'cardValue' => $card->card_value,
                'user_account' => $data['username'],
                'depositTime' => time(),
                'beforTime' => $beforTime,
                'ofterTime' => strtotime($user->out_time),
                'beforPoint' => $beforPoint,
                'ofterPoint' => $user->point
            ];
            $cr = new CardRecord();
            $cr->create($list);
            return $this->enc->encodeJson(["msg" => "充值成功", "data" => ['point' => $user->point, 'time' => $user->out_time], "code" => 200]);
        } else {
            return $this->enc->encodeJson(["msg" => "充值卡失败", "data" => ["out_time"=>$o_time], "code" => -10026]);
        }
    }

    //下线某个登录
    function getOut()
    {
        $user = Session::get('user');
        if (empty($user))
            return json(["msg" => '登录超时', "code" => -1]);
        $id = input('id');
        if ($id < 0) return json(["msg" => '登录编号不正确', "code" => -2]);
        $sr = SuLoginRecord::findCompat($id);
        if (empty($sr)) return json(["msg" => '版本不存在', "code" => -5]);
        if ($sr->authorid != $user->id) return json(["msg" => '非法访问数据', "code" => -3]);
        if($sr->status == 1)return json(["msg" => '已经是离线状态', "code" => -6]);
        $sr->status = 1;
        if ($sr->save()) {
                // \GatewayWorker\Lib\Gateway::sendToUid($sr->eid,json_encode(["msg" => '已经被请离下线', "code" => -7,"type"=>1]));
            return json(["msg" => '下线成功', "code" => 200]);
        } else {
            return json(["msg" => '下线失败', "code" => -4]);
        }
    }

    //远程变量
    function Variable($id, $data, $soft)
    {
        if (!array_key_exists('login_record', $data) || !array_key_exists('name', $data)) return $this->enc->encodeJson(["msg" => "参数不正确", "data" => json($data), "code" => -10001]);
        $sr = SuLoginRecord::getByEid($data['login_record']);
        if (!$sr) return $this->enc->encodeJson(["msg" => "未找到登录记录", "data" => $data['login_record'], "code" => -10018]);
        //检查用户登录状态
        $var = Variable::where(["name" => urldecode($data['name']), "sid" => $id])->find();
        if (empty($var)) return $this->enc->encodeJson(["msg" => "变量没有找到", "data" => [], "code" => -10031]);
        if ($soft->uid != $var->authorid) return $this->enc->encodeJson(["msg" => $soft->uid."变量不属于当前软件".$var->authorid, "data" => [], "code" => -10033]);
        //查看是否存在value 存在就设置 不存在就取出
        if (array_key_exists('value', $data)) {
            //设置变量
            $var->value = urldecode($data['value']) ;
            if ($var->save() !== false) {
                return $this->enc->encodeJson(["msg" => "设置成功", "data" => ['value' => $var->value], "code" => 200]);
            } else {
                return $this->enc->encodeJson(["msg" => "变量设置失败", "data" => [], "code" => -10032]);
            }
        } else {
            //取出变量
            return $this->enc->encodeJson(["msg" => "取出成功", "data" => ['value' => $var->value], "code" => 200]);
        }
    }

    //远程算法 9
    function RemoteFunction($id, $data, $soft)
    {
        if (!array_key_exists('name', $data) || !array_key_exists('functionParam', $data) || !array_key_exists('login_record', $data)) return $this->enc->encodeJson(["msg" => "参数不正确", "data" => json($data), "code" => -10001]);
        $sr = SuLoginRecord::getByEid($data['login_record']);
        if (!$sr) return $this->enc->encodeJson(["msg" => "未找到登录记录", "data" => $data['login_record'], "code" => -10018]);
        //查出函数表
        $rf = RemoteFunction::where(["name" => $data['name'], "uid" => $soft->uid])->find();
        if (!$rf) return $this->enc->encodeJson(["msg" => "算法不存在,请确认算法标签是否正确", "data" => [], "code" => -10034]);
        if ($soft->id != $rf->sid) return $this->enc->encodeJson(["msg" => "算法不属于当前软件", "data" => [], "code" => -10035]);
        return $this->enc->encodeJson(["msg" => "执行成功", "data" => ['result' => v8JS($rf->uid . "-" . $rf->name,urldecode($data['functionParam']) )], "code" => 200]);
    }

    //算法转发 10
    function ForwardUrl($id, $data, $soft)
    {
        if (!array_key_exists('name', $data) || !array_key_exists('Param', $data) || !array_key_exists('login_record', $data)) return $this->enc->encodeJson(["msg" => "参数不正确", "data" => json($data), "code" => -10001]);
        $cookie='';
        $head='';
        if(array_key_exists('cookie',$data))$cookie=$data['cookie'];
        if(array_key_exists('head',$data))$head=$data['head'];
        $sr = SuLoginRecord::getByEid($data['login_record']);
        if (!$sr) return $this->enc->encodeJson(["msg" => "未找到登录记录", "data" => $data['login_record'], "code" => -10018]);
        $fu = ForwardUrl::where(["name" => $data['name'], "uid" => $soft->uid])->find();
        if (!$fu) return $this->enc->encodeJson(["msg" => "远程算法不存在", "data" => [], "code" => -10036]);
        if ($soft->id != $fu->sid) return $this->enc->encodeJson(["msg" => "远程算法不属于当前软件", "data" => [], "code" => -10037]);
        if ($fu->status != 0)return $this->enc->encodeJson(["msg" => "远程算法状态非可用", "data" => [], "code" => -10037]);
        if ($fu->type == 0)//get
        {
            $res = curl_get($fu->url, $data['Param'],$cookie,$head);
        } else {
            $res = curl_post($fu->url, $data['Param'],$cookie,$head);
        }
        return $this->enc->encodeJson(["msg" => "取出成功","head"=>$head, "data" => ['result' => base64_encode($res) ], "code" => 200]);
    }

    //作者扣点11
    function makePoint($id, $data, $soft)
    {
        //取出登录记录
        if (!array_key_exists('point', $data) || !array_key_exists('type', $data)) return $this->enc->encodeJson(["msg" => "参数不正确", "data" => json($data), "code" => -10001]);
        $sr = SuLoginRecord::getByEid($data['login_record']);
        if (!$sr) return $this->enc->encodeJson(["msg" => "未找到登录记录", "data" => $data['login_record'], "code" => -10018]);
        if ($sr->status == 1) return $this->enc->encodeJson(["msg" => "登录状态已经失效", "data" => [], "code" => -10019]);
        $uid = $sr->uid;
        $su = SoftUsers::findCompat($uid);
        if (!$su) return $this->enc->encodeJson(["msg" => "用户不存在", "data" => [], "code" => -10019]);
        if ($su->status != 0) return $this->enc->encodeJson(["msg" => "用户状态被锁定", "data" => [], "code" => -10020]);
        if($data['type']==0)
        {  //扣点
        	if($data['point']<1)$data['point']=1;
            if ($su->point < $data['point']) {
                return $this->enc->encodeJson(["msg" => "用户点数不足", "data" => ["point" => $su->point], "code" => -10039]);
            }
            $su->point = $su->point - $data['point'];
        }else{
            //$su->point = $su->point + $data['point'];
            return $this->enc->encodeJson(["msg" => "赠点功能维护中", "code" => -10038]);
        }

        if ($su->save()) {
            return $this->enc->encodeJson(["msg" => "扣点成功", "data" => ["point" => $su->point], "code" => 200]);
        } else {
            return $this->enc->encodeJson(["msg" => "扣点失败", "code" => -10038]);
        }
    }
    //作者扣时14
    function makeTime($id, $data, $soft)
    {
        //取出登录记录
        if (!array_key_exists('hour', $data) || !array_key_exists('type', $data)) return $this->enc->encodeJson(["msg" => "参数不正确", "data" => json($data), "code" => -10001]);
        $sr = SuLoginRecord::getByEid($data['login_record']);
        if (!$sr) return $this->enc->encodeJson(["msg" => "未找到登录记录", "data" => $data['login_record'], "code" => -10018]);
        if ($sr->status == 1) return $this->enc->encodeJson(["msg" => "登录状态已经失效", "data" => [], "code" => -10019]);
        $uid = $sr->uid;
        $su = SoftUsers::findCompat($uid);
        if (!$su) return $this->enc->encodeJson(["msg" => "用户不存在", "data" => [], "code" => -10019]);
        if ($su->status != 0) return $this->enc->encodeJson(["msg" => "用户状态被锁定", "data" => [], "code" => -10020]);
        if($data['type']==0)
        {  //扣时
            $su->out_time=strtotime($su->out_time)-$data['hour']*3600;
        }else{
            //$su->out_time=strtotime($su->out_time)+$data['hour']*3600;
            return $this->enc->encodeJson(["msg" => "赠时功能维护中", "code" => -10038]);
        }
        if ($su->save()) {
            return $this->enc->encodeJson(["msg" =>$data['type']==0?"扣":"赠"."时成功", "data" => ["out_time" => $su->out_time], "code" => 200]);
        } else {
            return $this->enc->encodeJson(["msg" => $data['type']==0?"扣":"赠"."时失败", "code" => -10038]);
        }
    }
    //获取用户信息
    function getUserInfo($id, $data, $soft)
    {
        //取出登录记录
        if (!array_key_exists('login_record', $data)) return $this->enc->encodeJson(["msg" => "参数不正确", "data" => json($data), "code" => -10001]);
        $sr = SuLoginRecord::getByEid($data['login_record']);
        if (!$sr) return $this->enc->encodeJson(["msg" => "未找到登录记录", "data" => $data['login_record'], "code" => -10018]);
        if ($sr->status == 1) return $this->enc->encodeJson(["msg" => "登录状态已经失效", "data" => [], "code" => -10019]);
        $uid = $sr->uid;
        $su = SoftUsers::findCompat($uid);
        if (!$su) return $this->enc->encodeJson(["msg" => "用户不存在", "data" => [], "code" => -10019]);
        return $this->enc->encodeJson(["msg" => "查询成功", "data" => $su, "code" => 200]);
    }
    //用户修改绑定
    function changeMaccode($id, $data, $soft)
    {
        //软件是否换绑
        if($soft['isModifyMac']==0)return $this->enc->encodeJson(["msg" => "软件关闭了换绑", "data" => [], "code" => -10051]);
        if (!array_key_exists('username', $data) || !array_key_exists('password', $data) || !array_key_exists('maccode', $data)) return $this->enc->encodeJson(["msg" => "参数不正确", "data" => json($data), "code" => -10001]);
        $su=SoftUsers::where(['username'=>$data['username'],'password'=> md5(md5($data['password'])),'sid'=>$id])->find();
        if (!$su) return $this->enc->encodeJson(["msg" => "用户不存在", "data" => [], "code" => -10019]);
        if(strtoupper($data['maccode']) ==strtoupper($su->maccode) )return $this->enc->encodeJson(["msg" => "用户已经绑定到当前机器啦,无需换绑", "data" => [], "code" => -10041]);
        //检查换绑次数
        if($su->modif_num<=0)return $this->enc->encodeJson(["msg" => "对不起换绑次数不足", "data" => [], "code" => -10042]);
        $su->modif_num=$su->modif_num-1;
        $su->maccode=strtoupper($data['maccode']) ;
        if($su->save()){
            return $this->enc->encodeJson(["msg" => "换绑成功", "data" => [], "code" => 200]);
        }else{
            return $this->enc->encodeJson(["msg" => "换绑失败", "data" => [], "code" => -10043]);
        }
    }
    //查询版本信息
    function queryVer($id, $data, $soft)
    {
        $ver = SoftVer::where(["sid" => $id, "ver" => $data['version']])->find();
        if (empty($ver)) return $this->enc->encodeJson(["msg" => "版本不存在", "data" => [], "code" => -10004]);
        return $this->enc->encodeJson(["msg" => "成功", "data" => $ver, "code" => 200]);
    }
    //查询卡密信息 不包含卡密
    function queryCard($id, $data, $soft)
    {
        $card = Cards::where(["card_no"=>$data['card_no'],"sid"=>$id])->field('card_no',true)->find();
        if (!$card) return $this->enc->encodeJson(["msg" => "充值卡有误", "data" => [], "code" => -10025]);
        return $this->enc->encodeJson(["msg" => "成功", "data" => $card, "code" => 200]);
    }
    //处理心跳包
    function swHeart($body)
    {
        //查询参数是否正确
        if(!array_key_exists("sid",$body)  || !array_key_exists("login_record",$body) || !array_key_exists("data",$body))return json_encode(["msg" => "参数不正确", "code" => -10001,"type"=>2],JSON_UNESCAPED_SLASHES);
        $sid=$body['sid'];
        $data=base64_decode($body['data']) ;
        //查询出软件
        $soft=SoftList::findCompat($sid);
        if(empty($soft))return json_encode(["msg" => "软件不存在".$sid, "code" => -10002,"type"=>2],JSON_UNESCAPED_SLASHES);
        $key = $soft['privateKey'];
        $encryptType = $soft->encryptType;
        $enc = new encode($key, $encryptType);
        $res = json_decode($enc->su_decrypt($data), true);
        if ($res == null || empty($res)) return json_encode(["msg" => "参数解密失败", "data" => ['key' => $key, 'encryptType' => $encryptType], "code" => -10010,"type"=>2],JSON_UNESCAPED_SLASHES);
        $data = count($res['data']) == 1 ? $res['data'][0] : $res['data'];
        return base64_encode($this->heart($sid,$data,$soft,$enc) );
    }
    //反馈
    function feedback($id, $data, $soft)
    {
        if (!array_key_exists('login_record', $data) || !array_key_exists('msg', $data)) return $this->enc->encodeJson(["msg" => "参数不正确", "data" => json($data), "code" => -10001]);
        $sr = SuLoginRecord::getByEid($data['login_record']);
        if (!$sr) return $this->enc->encodeJson(["msg" => "未找到登录记录", "data" => $data['login_record'], "code" => -10018]);
        $fk=Feedback::where(["suid"=>$sr->uid])->order('id desc')->find();
        if(!empty($fk)){
            //检查上次反馈距离现在是否超过10秒
            if(time()-strtotime($fk->add_time)<10)return $this->enc->encodeJson(["msg" => "距离上次反馈时间不得低于10秒", "data" => [], "code" => -10050]);
        }
        //添加反馈消息
        $msg = [
            'suid' =>$sr->uid,
            'sid' => $sr->sid,
            'msg' =>$data['msg'],
            'add_time' => time()
        ];
        $fb = new Feedback();
        if($fb->create($msg))
        {
            return $this->enc->encodeJson(["msg" => "成功", "data" => [], "code" => 200]);
        }else{
            return $this->enc->encodeJson(["msg" => "反馈失败", "data" => [], "code" => -10047]);
        }

    }
    function test()
    {
        // $url="http://mytest.com/admin/soft/test";
        // echo curl_post($url,"id=1&a=6");
        // echo phpinfo();
        //v8JS("18-max","max(1,2)");
        echo $this->request->scheme();
    }
}
