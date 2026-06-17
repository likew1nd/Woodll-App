<?php
/**
 * Created by IntelliJ IDEA.
 * User: Administrator
 * Date: 2019/4/20
 * Time: 18:50
 */

namespace app\admin\controller;

use app\admin\model\Cards;
use app\admin\model\CardType;
use app\admin\model\ForwardUrl;
use app\admin\model\RemoteFunction;
use app\admin\model\SoftList;
use app\admin\model\SoftUsers;
use app\admin\model\SoftVer;
use app\admin\model\Variable;
use Crypt\Aes;
use Crypt\encode;
use app\BaseController as Controller;
use think\Request;
use think\facade\Db;
use think\log;
use app\admin\model\Users;
use think\facade\Session;


class Soft extends Controller
{
    /**
     * Internal helper to check session and return user object.
     */
    protected function checkAuth()
    {
        $user = Session::get('user');
        if (empty($user)) {
            return null;
        }
        return $user;
    }

    public function softList()
    {
        if (!$user = $this->checkAuth()) return $this->fetch('index/timeout');

        $this->assign('user', $user);
        $this->assign('title', $user->username . " - 软件列表");
        $this->assign('keywords', $user->username . "- 软件列表");
        return $this->fetch('soft/list');
    }

    //获取软件列表
    function getSoftList()
    {
        if (!$user = $this->checkAuth()) return $this->fetch('index/timeout');


        // 在统计前，清理该管理员名下所有超时未心跳的用户为下线状态
        $timeoutTime = time() - 300;
        Db::name('soft_users')
            ->where('authorid', $user->id)
            ->where('isonline', 1)
            ->where('heart_time', '<', $timeoutTime)
            ->update(['isonline' => 0]);

        $where = [
            ['uid', '=', $user->id],
        ];
        $pageSize = input('limit');
        $name = input('name');
        if (!empty($name)) {
            $where[] = ['name', 'like', '%' . $name . '%'];
        }
        $list = SoftList::where($where)->order('id desc')->limit(input('offset'), $pageSize)->select();
        foreach ($list as $row) {
            $row['online_count'] = SoftUsers::where(['sid' => $row->id, 'isonline' => 1])->count();
            $row['expired_count'] = SoftUsers::where('sid', $row->id)->where('out_time', '<=', time())->count();
            $row['unused_cards_count'] = Cards::where(['sid' => $row->id, 'status' => 0])->count();
        }
        $total = SoftList::where($where)->count();
        return json(["total" => $total, "rows" => $list]);
    }

    //获取软件删除关联统计
    function getSoftDeleteStats()
    {
        $user = Session::get('user');
        if (empty($user))
            return json(["msg" => '登录超时', "code" => -1]);
            
        $ids = input('ids');
        if (empty($ids)) {
            $ids = input('id');
        }
        if (empty($ids)) {
            return json(["code" => -2, "msg" => "missing software ids"]);
        }
        
        $idsArr = is_array($ids) ? $ids : explode(',', $ids);
        $idsArr = array_filter(array_map('intval', $idsArr));
        
        if (empty($idsArr)) {
            return json(["code" => -2, "msg" => "invalid software ids"]);
        }
        
        // 校验归属权
        $ownedCount = SoftList::whereIn('id', $idsArr)->where('uid', $user->id)->count();
        if ($ownedCount != count($idsArr)) {
            return json(["code" => -3, "msg" => "无权访问部分或全部选中的软件数据"]);
        }
        
        // 获取每个软件的具体统计信息
        $list = [];
        foreach ($idsArr as $id) {
            $soft = SoftList::where('id', $id)->find();
            if ($soft) {
                $list[] = [
                    'id' => $id,
                    'name' => $soft->name,
                    'version_count' => SoftVer::where('sid', $id)->count(),
                    'card_type_count' => CardType::where('sid', $id)->count(),
                    'unused_cards_count' => Cards::where(['sid' => $id, 'status' => 0])->count(),
                    'user_count' => SoftUsers::where('sid', $id)->count()
                ];
            }
        }
        
        return json([
            "code" => 0,
            "list" => $list
        ]);
    }

    //删除软件
    function delSoft()
    {
        $user = Session::get('user');
        if (empty($user))
            return json(["msg" => '登录超时', "code" => -1]);
            
        $ids = input('ids');
        if (empty($ids)) {
            $ids = input('id');
        }
        if (empty($ids)) {
            return json(["msg" => "invalid software id", "code" => -2]);
        }
        
        $idsArr = is_array($ids) ? $ids : explode(',', $ids);
        $idsArr = array_filter(array_map('intval', $idsArr));
        if (empty($idsArr)) {
            return json(["msg" => "invalid software id", "code" => -2]);
        }
        
        // 校验归属权
        $softs = SoftList::whereIn('id', $idsArr)->where('uid', $user->id)->select();
        if (count($softs) != count($idsArr)) {
            return json(["msg" => '非法访问数据或软件不存在', "code" => -3]);
        }
        
        try {
            Db::transaction(function () use ($idsArr) {
                // 级联物理删除关联的表记录
                Db::name('soft_users')->whereIn('sid', $idsArr)->delete();
                Db::name('cards')->whereIn('sid', $idsArr)->delete();
                Db::name('card_type')->whereIn('sid', $idsArr)->delete();
                Db::name('variable')->whereIn('sid', $idsArr)->delete();
                Db::name('remote_function')->whereIn('sid', $idsArr)->delete();
                Db::name('forward_url')->whereIn('sid', $idsArr)->delete();
                Db::name('soft_ver')->whereIn('sid', $idsArr)->delete();
                
                // 删除软件主体
                Db::name('soft_list')->whereIn('id', $idsArr)->delete();
            });
            return json(["msg" => '成功删除了选中的软件及关联的所有数据', "code" => 0]);
        } catch (\Throwable $e) {
            return json(["msg" => '删除失败: ' . $e->getMessage(), "code" => -4]);
        }
    }

    //编辑软件页面
    function editSoft()
    {
        if (!$user = $this->checkAuth()) return $this->fetch('index/timeout');

        $id = input('id');
        if ($id < 0) return "invalid software id";
        $soft = SoftList::findCompat($id);
        if (empty($soft)) return "software not found";
        if($soft->uid!=$user->id)return "非法访问";
        $this->assign('user', $user);
        $this->assign('soft', $soft);
        $this->assign('title', $user->username . " - 软件编辑");
        $this->assign('keywords', $user->username . "- 软件编辑");
        return $this->fetch('soft/editSoft');
    }

    //添加软件页面
    function addsoft()
    {
        if (!$user = $this->checkAuth()) return $this->fetch('index/timeout');
        
        $soft = [
            'id' => '',
            'name' => '',
            'status' => '收费',
            'key' => '系统自动生成',
            'openReg' => 0,
            'notice' => '',
            'data' => '',
            'regFree' => 0,
            'regFreePoint' => 0,
            'timeFree' => 0,
            'timeFreePointEnd' => 12,
            'timeFreePointStart' => 1,
            'freeChangeBundled' => 0,
            'verifyMode' => 0,
            'topLoginType' => 0,
            'pointStep' => 1,
            'multiType' => 0,
            'multiTypeValue' => 1,
            'isModifyMac' => 0,
            'encryptType' => 0,
            'privateKey' => generate_password(24),
            'privateSalt' => generate_password(16),
            'regMacLimit'=>0,
            'regIpLimit'=>0
        ];
        $this->assign('user', $user);
        $this->assign('soft', $soft);
        $this->assign('title', $user->username . " - 添加软件");
        $this->assign('keywords', $user->username . "- 添加软件");
        return $this->fetch('soft/editSoft');
    }

    // 添加或更新软件
    function updateSoft()
    {
        $user = Session::get('user');
        if (empty($user)) {
            return json(["msg" => '登录超时', "code" => -1]);
        }

        $id = input('id');
        $all = request()->post();
        $allowedFields = [
            'name', 'version', 'openReg', 'notice', 'data', 'regFreePoint',
            'regFree', 'timeFreePointEnd', 'timeFreePointStart', 'timeFree',
            'freeChangeBundled', 'verifyMode', 'pointStep', 'topLoginType',
            'multiType', 'isModifyMac', 'multiTypeValue', 'encryptType',
            'privateSalt', 'privateKey', 'status', 'regMacLimit', 'regIpLimit'
        ];
        $all = array_intersect_key($all, array_flip($allowedFields));
        try {
            $tableFields = Db::name('soft_list')->getTableFields();
            if (!empty($tableFields)) {
                $all = array_intersect_key($all, array_flip($tableFields));
            }
        } catch (\Throwable $e) {
            return json(["msg" => "read soft_list fields failed: " . $e->getMessage(), "code" => -500]);
        }
        $all = array_map(function ($value) {
            return is_string($value) ? trim($value) : $value;
        }, $all);

        foreach ([
            'openReg', 'regFree', 'regFreePoint', 'timeFree', 'timeFreePointEnd',
            'timeFreePointStart', 'freeChangeBundled', 'verifyMode', 'pointStep',
            'topLoginType', 'multiType', 'multiTypeValue', 'isModifyMac',
            'encryptType', 'status', 'regMacLimit', 'regIpLimit'
        ] as $intField) {
            if (isset($all[$intField])) {
                $all[$intField] = (int)$all[$intField];
            }
        }

        if (empty($all['name'])) {
            return json(["msg" => "请输入软件名称", "code" => -6]);
        }
        $nameCheckWhere = [['name', '=', $all['name']]];
        if (!empty($id)) {
            $nameCheckWhere[] = ['id', '<>', $id];
        }
        if (Db::name('soft_list')->where($nameCheckWhere)->find()) {
            return json(["msg" => "该软件名称已存在", "code" => -7]);
        }
        if (!isset($all['privateKey']) || $all['privateKey'] === '') {
            return json(["msg" => "请输入加密KEY", "code" => -6]);
        }
        if (!isset($all['privateSalt']) || $all['privateSalt'] === '') {
            return json(["msg" => "请输入加密盐", "code" => -6]);
        }

        $stringLimits = [
            'name' => 255,
            'version' => 50,
            'notice' => 255,
            'data' => 255,
            'privateSalt' => 255,
            'privateKey' => 255,
        ];
        foreach ($stringLimits as $field => $limit) {
            if (isset($all[$field]) && strlen($all[$field]) > $limit) {
                return json(["msg" => $field . " is too long, max " . $limit . " bytes", "code" => -6]);
            }
        }

        if (!empty($id)) {
            $soft = Db::name('soft_list')->where(['id' => $id, 'uid' => $user->id])->find();
            if (empty($soft)) {
                return json(["msg" => "软件不存在或无权访问", "code" => -5]);
            }
            try {
                Db::name('soft_list')->where(['id' => $id, 'uid' => $user->id])->update($all);
                return json(["msg" => "更新成功", "code" => 0]);
            } catch (\Throwable $e) {
                return json(["msg" => "update failed: " . $e->getMessage(), "code" => -500]);
            }
        } else {
            //查询用户名下有多少软件了,限制数量
            $max = 100;
            $count = Db::name('soft_list')->where(['uid' => $user->id])->count();
            if ($count >= $max) {
                return json(["msg" => "最多添加 " . $max . " 个软件", "code" => -4]);
            }
            //添加软件
            $all['key'] = strtoupper(md5(guid()));
            $all['uid'] = $user->id;
            $all['expireTime'] = time();
            try {
                Db::name('soft_list')->insert($all);
                return json(["msg" => "添加成功", "code" => 0]);
            } catch (\Throwable $e) {
                return json(["msg" => "create failed: " . $e->getMessage(), "code" => -500]);
            }
        }
    }

    // get simple software list
    function getSoftListsimple()
    {
        $user = Session::get('user');
        if (empty($user))
            return json(["msg" => '登录超时', "code" => -1]);
        $where = [
            'uid' => $user->id,
        ];
        $list = SoftList::where($where)->field(['id', 'name'])->select();
        return json(["total" => count($list), "rows" => $list, "code" => 0]);
    }

    //软件版本列表页面
    function ver()
    {
        $user = Session::get('user');
        if (empty($user))
            return json(["msg" => '登录超时', "code" => -1]);


        $id = input('id');
        if ($id < 0) return json(["msg" => "invalid software id", "code" => -2]);
        $soft = SoftList::findCompat($id);
        if (empty($soft)) return json(["msg" => "software not found", "code" => -5]);
        if ($soft->uid != $user->id) return json(["msg" => '非法访问数据', "code" => -3]);
        $this->assign('user', $user);
        $this->assign('soft', $soft);
        $this->assign('title', $user->username . " - 版本管理");
        $this->assign('keywords', $user->username . "- 版本管理");
        return $this->fetch('soft/verGM');
    }

    function getVerList()
    {
        $user = Session::get('user');
        if (empty($user))
            return json(["msg" => '登录超时', "code" => -1]);


        $id = input('id');
        if ($id < 0) return json(["msg" => "invalid software id", "code" => -2]);
        $soft = SoftList::findCompat($id);
        if (empty($soft)) return json(["msg" => "software not found", "code" => -5]);
        if ($soft->uid != $user->id) return json(["msg" => '非法访问数据', "code" => -3]);
        $pageSize = input('limit');
        $name = input('name');
        $where = [
            ['sid', '=', $id]
        ];
        if (!empty($name)) {
            $where[] = ['name', 'like', '%' . $name . '%'];
        }
        $list = SoftVer::where($where)->order('id desc')->limit(input('offset'), $pageSize)->select();
        $total = SoftVer::where($where)->count();
        return json(["total" => $total, "rows" => $list]);
    }

    //删除版本
    function delVer()
    {
        $user = Session::get('user');
        if (empty($user))
            return json(["msg" => '登录超时', "code" => -1]);
        $id = input('id');
        if ($id < 0) return json(["msg" => "invalid version id", "code" => -2]);
        $ver = SoftVer::findCompat($id);
        if (empty($ver)) return json(["msg" => "version not found", "code" => -5]);
        // 查询所属软件
        $soft = SoftList::findCompat($ver->sid);
        if ($soft->uid != $user->id) return json(["msg" => '非法访问数据', "code" => -3]);
        //更新最新版的版本信息为该软件最后添加的版本
        if ($ver->delete()) {
            $newVer=SoftVer::where([
                "sid"=>$soft->id
            ])->order('addTime', 'desc')->find();
            $soft->version=empty($newVer)?"":$newVer->ver;
            $soft->save();
            return json(["msg" => '成功删除了该版本', "code" => 0]);
        } else {
            return json(["msg" => '版本删除失败', "code" => -4]);
        }
    }

    //添加版本页面
    function addVer()
    {
        $user = Session::get('user');
        if (empty($user)) {
            $this->assign('title', "超时");
            $this->assign('keywords', "超时");
            return $this->fetch('index/timeout');
        }


        $id = input('id');
        if ($id < 0) return "invalid software id";
        $soft = SoftList::findCompat($id);
        if (empty($soft)) return "software not found";
        if ($soft->uid != $user->id) return '非法访问数据';
        $ver = [
            'id' => '',
            'name' => '',
            'ver' => '1.0',
            'status' => 0,
            'checkUpdate' => 0,
            'MD5' => '',
            'updateUrl' => '',
            'notice' => '',
            'reamrk' => ''
        ];
        $this->assign('user', $user);
        $this->assign('ver', $ver);
        $this->assign('soft', $soft);
        $this->assign('sbName', "确认添加");
        $this->assign('cbName', "取消添加");
        $this->assign('title', $user->username . " - 版本添加");
        $this->assign('keywords', $user->username . "- 版本添加");
        return $this->fetch('soft/editVer');
    }

    //编辑版本页面
    function editVer()
    {
        $user = Session::get('user');
        if (empty($user)) {
            $this->assign('title', "超时");
            $this->assign('keywords', "超时");
            return $this->fetch('index/timeout');
        }


        $id = input('id');
        if ($id < 0) return "invalid version id";
        $ver = SoftVer::findCompat($id);
        if (empty($ver)) return "version not found";
        $soft = SoftList::findCompat($ver->sid);
        if($soft->uid!=$user->id) return json(["msg" => "非法访问", "code" => -4]);
        $this->assign('user', $user);
        $this->assign('ver', $ver);
        $this->assign('soft', $soft);
        $this->assign('sbName', "确认修改");
        $this->assign('cbName', "取消修改");
        $this->assign('title', $user->username . " - 版本编辑");
        $this->assign('keywords', $user->username . "- 版本编辑");
        return $this->fetch('soft/editVer');
    }

    // update version
    function updateVer()
    {
        $user = Session::get('user');
        if (empty($user))
            return json(["msg" => '登录超时', "code" => -1]);


        $id = input('id');    //版本ID
        $sid = input('sid'); //软件ID
        if ($sid <= 0) return json(["msg" => '软件编号错误', "code" => -2]);
        //先查询版本所属软件是不是该用户的
        $soft = SoftList::findCompat($sid);
        if (empty($soft)) return json(["msg" => "software not found", "code" => -3]);
        if ($soft->uid != $user->id) return json(["msg" => '非法访问数据', "code" => -4]);
        //先判断是更新还是添加
        $all = request()->post();
        if (!empty($id)) //更新版本
        {
            $soft = SoftVer::findCompat($id);
            if ($soft->update($all)) {
                return json(["msg" => "更新成功", "code" => 0]);
            } else {
                return json(["msg" => "更新失败", "code" => -2]);
            }
        } else {
            // 先查询版本是否存在
            if (SoftVer::where(['sid' => $sid, 'ver' => $all['ver']])->find()) return json(["msg" => "版本已经存在,请选择修改版本", "code" => -5]);
            //添加版本
            $all['addTime'] = time();
            $all['sid'] = $sid;
            if (SoftVer::create($all)) {
                // 更新到软件最新版本
                $soft->version = $all['ver'];
                $soft->save();
                return json(["msg" => "添加成功", "code" => 0]);
            } else {
                return json(["msg" => "添加失败", "code" => -3]);
            }
        }
    }

    //远程变量页面
    function variableList()
    {
        $user = Session::get('user');
        if (empty($user)) {
            $this->assign('title', "超时");
            $this->assign('keywords', "超时");
            return $this->fetch('index/timeout');
        }
        $this->assign("user", $user);
        $this->assign('title', $user->username . " - 远程变量");
        $this->assign('keywords', $user->username . "- 远程变量");
        return $this->fetch('soft/variableList');
    }

    function getVariableList()
    {
        $user = Session::get('user');
        if (empty($user))
            return json(["msg" => '登录超时', "code" => -1]);
        $pageSize = input('limit');
        $sname = input('sname');
        $where = [
            ['v.authorid', '=', $user->id],
        ];
        if (!empty($sname)) {
            $where[] = ['s.name', 'like', '%' . $sname . '%'];
        }
        $var = new Variable();
        $list = $var->alias("v")
            ->join("soft_list s", "s.id=v.sid", "LEFT")
            ->field("v.*,s.name as sname")
            ->where($where)
            ->limit(input('offset'), $pageSize)
            ->order(['v.id' => 'desc'])
            ->select();
        $total = $var->alias("v") ->join("soft_list s", "s.id=v.sid", "LEFT")->where($where)->count();
        return json(["total" => $total, "rows" => $list]);
    }

    //删除变量
    function deleteVariable()
    {
        $user = Session::get('user');
        if (empty($user))
            return json(["msg" => '登录超时', "code" => -1]);
        $id = input('id');
        if ($id < 0) return json(["msg" => "invalid variable id", "code" => -2]);
        $data =Variable::findCompat($id);
        if (empty($data)) return json(["msg" => '变量没有找到', "code" => -3]);
        if ($data->authorid != $user->id) return json(["msg" => '非法访问数据', "code" => -4]);
        if ($data->delete()) {
            return json(["msg" => '成功删除了该变量', "code" => 0]);
        } else {
            return json(["msg" => '变量删除失败', "code" => -5]);
        }
    }
    //添加编辑变量
    function addVariable()
    {
        $user = Session::get('user');
        if (empty($user)) {
            $this->assign('title', "超时");
            $this->assign('keywords', "超时");
            return $this->fetch('index/timeout');
        }
        $id = input('id');
        $var=[];
        if ($id > 0){
            //修改
            $this->assign('sbName', "确认修改");
            $this->assign('cnName', "取消修改");
            $this->assign('title', $user->username . " - 变量修改");
            $this->assign('keywords', $user->username . "- 变量修改");
            $Variable = new Variable();
            $var = $Variable->alias("v")
                ->join("soft_list s", "s.id=v.sid", "LEFT")
                ->field("v.*,s.name as sname")
                ->where(["v.id"=>$id,"v.authorid"=>$user->id])
                ->find();
        }else
        {
            $var=[
              'id'=>0,
                'sname'=>"",
                'sid'=>0,
                'name'=>'',
                 'value'=>''
            ];
            //新增
            $this->assign('sbName', "确认添加");
            $this->assign('cnName', "取消添加");
            $this->assign('title', $user->username . " - 变量添加");
            $this->assign('keywords', $user->username . "- 变量添加");
        }
        $this->assign('var', $var);
        $this->assign('user', $user);
        return $this->fetch('soft/Variable');
    }
    //添加变量
    function addVar()
    {
        //新增还是修改
        $user = Session::get('user');
        if (empty($user))
            return json(["msg" => '登录超时', "code" => -1]);
        $id = input('id');
        $data = $this->request->param();
        if (empty($data['sid'])) {
            return json(["msg" => "请选择软件", "code" => -4]);
        }
        if (empty($data['name'])) {
            return json(["msg" => "请输入变量名称", "code" => -5]);
        }
        $varCheckWhere = [
            ['sid', '=', $data['sid']],
            ['name', '=', $data['name']]
        ];
        if ($id > 0) {
            $varCheckWhere[] = ['id', '<>', $id];
        }
        if (Variable::where($varCheckWhere)->find()) {
            return json(["msg" => "该变量名在此软件下已存在", "code" => -6]);
        }

        if ($id <= 0) {
            //新增
            $var = new Variable();
        } else {
            //修改
            $varObj=new Variable();
            $var = $varObj->where(["id"=>$id,"authorid"=>$user->id])->find();
            if(empty($var))return json(["msg" => "variable not found", "code" => -3]);
        }
        $data['authorid'] = $user->id;
        $data['create_time']=time();
        if ($var->save($data)) {
            return json(["msg" => '成功', "code" => 0]);
        } else {
            return json(["msg" => '失败', "code" => -2]);
        }
    }
    //远程JS函数
    function FunctionList()
    {
        $user = Session::get('user');
        if (empty($user)) {
            $this->assign('title', "超时");
            $this->assign('keywords', "超时");
            return $this->fetch('index/timeout');
        }
        $this->assign("user", $user);
        $this->assign('title', $user->username . " - 远程函数");
        $this->assign('keywords', $user->username . "- 远程函数");
        return $this->fetch('soft/FunctionList');
    }
    //获取代码列表
    function getFunctionList()
    {
        $user = Session::get('user');
        if (empty($user))
            return json(["msg" => '登录超时', "code" => -1]);
        $pageSize = input('limit');
        $sname = input('sname');
        $where = [
            ['r.uid', '=', $user->id],
        ];
        if (!empty($sname)) {
            $where[] = ['s.name', 'like', '%' . $sname . '%'];
        }
        $rf = new RemoteFunction();
        $list = $rf->alias("r")
            ->join("soft_list s", "s.id=r.sid", "LEFT")
            ->field("r.*,s.name as sname")
            ->where($where)
            ->limit(input('offset'), $pageSize)
            ->order(['r.id' => 'desc'])
            ->select();
        $total = $rf->alias("r")->join("soft_list s", "s.id=r.sid", "LEFT")->where($where)->count();
        return json(["total" => $total, "rows" => $list]);
    }
    //添加修改函数页面
    function addFunction()
    {
        $user = Session::get('user');
        if (empty($user)) {
            $this->assign('title', "超时");
            $this->assign('keywords', "超时");
            return $this->fetch('index/timeout');
        }
        $id = input('id');
        $func=[];
        if ($id > 0){
            //修改
            $this->assign('sbName', "确认修改");
            $this->assign('cnName', "取消修改");
            $this->assign('title', $user->username . " - 函数修改");
            $this->assign('keywords', $user->username . "- 函数修改");
            $function = new RemoteFunction();
            $func = $function->alias("v")
                ->join("soft_list s", "s.id=v.sid", "LEFT")
                ->field("v.*,s.name as sname")
                ->where(["v.id"=>$id])
                ->find();
            if(empty($func))return "function not found";
            $file_path=app()->getRootPath() . 'public' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . "v8js" . DIRECTORY_SEPARATOR . $user->id . "-" . $func->name . ".txt";
            if(file_exists($file_path)) {
                $func['value']=file_get_contents($file_path);
            }else{
                $func['value']="代码文件丢失".$file_path;
            }


        }else
        {
            $func=[
                'id'=>0,
                'sname'=>"",
                'sid'=>0,
                'name'=>'',
                'value'=>''
            ];
            //新增
            $this->assign('sbName', "确认添加");
            $this->assign('cnName', "取消添加");
            $this->assign('title', $user->username . " - 函数添加");
            $this->assign('keywords', $user->username . "- 函数添加");
        }
        $this->assign('func', $func);
        $this->assign('user', $user);
        return $this->fetch('soft/addFunction');
    }
    function addRemoteFunction()
    {
        //新增还是修改
        $user = Session::get('user');
        if (empty($user))
            return json(["msg" => '登录超时', "code" => -1]);
        $id = input('id');
        $data = $this->request->param();
        if (empty($data['sid'])) {
            return json(["msg" => "请选择软件", "code" => -4]);
        }
        if (empty($data['name'])) {
            return json(["msg" => "请输入函数标签", "code" => -5]);
        }
        $funcCheckWhere = [
            ['sid', '=', $data['sid']],
            ['name', '=', $data['name']]
        ];
        if ($id > 0) {
            $funcCheckWhere[] = ['id', '<>', $id];
        }
        if (RemoteFunction::where($funcCheckWhere)->find()) {
            return json(["msg" => "该函数标签在此软件下已存在", "code" => -6]);
        }

        if ($id <= 0) {
            //新增
            $var = new RemoteFunction();
        } else {
            //修改
            $rf=new RemoteFunction();
            $var = $rf->where(["id"=>$id,"uid"=>$user->id])->find();// RemoteFunction::findCompat($id);
            if(empty($var)) return json(["msg" => "remote function not found", "code" => -3]);
        }

        $data = $this->request->param();
        // Sanitize name to prevent path traversal
        $safeName = preg_replace('/[^a-zA-Z0-9_\-]/', '', $data['name']);
        $data['uid'] = $user->id;
        $data['create_time']=time();
		//判断文件上传是否出错
        $file = request()->file('file');
		 if($file){
			 $file_path = app()->getRootPath() . 'public' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'v8js';
			 try {
				 validate([
					 'file' => 'fileSize:156780|fileExt:txt,js'
				 ])->check(['file' => $file]);

				 $info = $file->move($file_path, $user->id."-".$safeName.".txt");
				 if($info){
					  return json(["msg" => '文件上传成功', "code" => 0]);
				 }else{
					  return json(["msg" => '文件上传失败', "code" => -2]);
				 }
			 } catch (\Throwable $e) {
				 return json(["msg" => '文件上传失败:' . $e->getMessage(), "code" => -2]);
			 }

		 }else{
			 
		 
			if ($var->save($data)) {
				//把代码保存到文件
				$dir_path = app()->getRootPath() . 'public' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'v8js';
				if (!is_dir($dir_path)) {
					mkdir($dir_path, 0755, true);
				}
				$file_path = $dir_path . DIRECTORY_SEPARATOR . $user->id . "-" . $safeName . ".txt";
				$myfile = fopen($file_path, "w") or die("Unable to open file!");
				fwrite($myfile, urldecode($data['value']));
				fclose($myfile);
				return json(["msg" => '成功', "code" => 0]);
			} else {
				return json(["msg" => '失败', "code" => -2]);
			}
		}
    }
    //删除JS函数
    function deleteFunction()
    {
        $user = Session::get('user');
        if (empty($user))
            return json(["msg" => '登录超时', "code" => -1]);
        $id = input('id');
        if ($id < 0) return json(["msg" => "invalid function id", "code" => -2]);
        $data =RemoteFunction::findCompat($id);
        if (empty($data)) return json(["msg" => '函数没有找到', "code" => -3]);
        if ($data->uid != $user->id) return json(["msg" => '非法访问数据', "code" => -4]);
        if ($data->delete()) {
            return json(["msg" => '成功删除了该函数', "code" => 0]);
        } else {
            return json(["msg" => '函数删除失败', "code" => -5]);
        }
    }
    //算法转发
    function ForwardUrlList()
    {
        $user = Session::get('user');
        if (empty($user)) {
            $this->assign('title', "超时");
            $this->assign('keywords', "超时");
            return $this->fetch('index/timeout');
        }
        $this->assign("user", $user);
        $this->assign('title', $user->username . " - 算法转发");
        $this->assign('keywords', $user->username . "- 算法转发");
        return $this->fetch('soft/ForwardUrlList');
    }
    function getForwardUrlList()
    {
        $user = Session::get('user');
        if (empty($user))
            return json(["msg" => '登录超时', "code" => -1]);
        $pageSize = input('limit');
        $sname = input('sname');
        $where = [
            ['r.uid', '=', $user->id],
        ];
        if (!empty($sname)) {
            $where[] = ['s.name', 'like', '%' . $sname . '%'];
        }
        $fu = new ForwardUrl();
        $list = $fu->alias("r")
            ->join("soft_list s", "s.id=r.sid", "LEFT")
            ->field("r.*,s.name as sname")
            ->where($where)
            ->limit(input('offset'), $pageSize)
            ->order(['r.id' => 'desc'])
            ->select();
        $total = $fu->alias("r")->join("soft_list s", "s.id=r.sid", "LEFT")->where($where)->count();
        return json(["total" => $total, "rows" => $list]);
    }
    function addForward()
    {
        $user = Session::get('user');
        if (empty($user)) {
            $this->assign('title', "超时");
            $this->assign('keywords', "超时");
            return $this->fetch('index/timeout');
        }
        $id = input('id');
        $forward=[];
        if ($id > 0){
            //修改
            $this->assign('sbName', "确认修改");
            $this->assign('cnName', "取消修改");
            $this->assign('title', $user->username . " - 算法转发修改");
            $this->assign('keywords', $user->username . "- 算法转发修改");
            $fu = new ForwardUrl();
            $forward = $fu->alias("v")
                ->join("soft_list s", "s.id=v.sid", "LEFT")
                ->field("v.*,s.name as sname")
                ->where(["v.id"=>$id,"v.uid"=>$user->id])
                ->find();
            if(empty($forward))return "forward not found";
        }else
        {
            $forward=[
                'id'=>0,
                'sname'=>"",
                'sid'=>0,
                'name'=>'',
                'value'=>'',
                'type'=>0,
                'url'=>'',
                'cookie'=>'',
                'head'=>''
            ];
            //新增
            $this->assign('sbName', "确认添加");
            $this->assign('cnName', "取消添加");
            $this->assign('title', $user->username . " - 算法转发添加");
            $this->assign('keywords', $user->username . "- 算法转发添加");
        }
        $this->assign('forward', $forward);
        $this->assign('user', $user);
        return $this->fetch('soft/addForward');
    }
    // add or update forward url
    function addForwardUrl()
    {
        //新增还是修改
        $user = Session::get('user');
        if (empty($user))
            return json(["msg" => '登录超时', "code" => -1]);
        $id = input('id');
        $data = $this->request->param();
        if (empty($data['sid'])) {
            return json(["msg" => "请选择软件", "code" => -4]);
        }
        if (empty($data['name'])) {
            return json(["msg" => "请输入转发标签", "code" => -5]);
        }
        $forwardCheckWhere = [
            ['sid', '=', $data['sid']],
            ['name', '=', $data['name']]
        ];
        if ($id > 0) {
            $forwardCheckWhere[] = ['id', '<>', $id];
        }
        if (ForwardUrl::where($forwardCheckWhere)->find()) {
            return json(["msg" => "该转发标签在此软件下已存在", "code" => -6]);
        }

        if ($id <= 0) {
            //新增
            $fu = new ForwardUrl();
        } else {
            //修改
            $fu = ForwardUrl::findCompat($id);
        }
        $data['uid'] = $user->id;
        $data['add_time']=time();
        if ($fu->save($data)) {
            return json(["msg" => '成功', "code" => 0]);
        } else {
            return json(["msg" => '失败', "code" => -2]);
        }
    }
    //删除转发
    function deleteForward()
    {
        $user = Session::get('user');
        if (empty($user))
            return json(["msg" => '登录超时', "code" => -1]);
        $id = input('id');
        if ($id < 0) return json(["msg" => "invalid forward id", "code" => -2]);
        $data =ForwardUrl::findCompat($id);
        if (empty($data)) return json(["msg" => '转发算法没有找到', "code" => -3]);
        if ($data->uid != $user->id) return json(["msg" => '非法访问数据', "code" => -4]);
        if ($data->delete()) {
            return json(["msg" => '成功删除了该转发算法', "code" => 0]);
        } else {
            return json(["msg" => '转发算法删除失败', "code" => -5]);
        }
    }
    // change forward status
    function changeForwardStatus()
    {
        $user = Session::get('user');
        if (empty($user))
            return json(["msg" => '登录超时', "code" => -1]);
        $id = input('id');
        $status=input('status');
        if ($status < 0 || $status>1) return json(["msg" => '提交的状态不正确', "code" => -6]);
        if ($id < 0) return json(["msg" => "invalid forward id", "code" => -2]);
        $fu = new ForwardUrl();
        $where = [
            'v.id' => $id,
        ];
        $data = $fu->alias("v")
            ->join("soft_list s", "s.id=v.sid", "LEFT")
            ->where($where)
            ->field("v.*,s.uid")
            ->find();
        if (empty($data)) return json(["msg" => '转发算法没有找到', "code" => -3]);
        if ($data->uid != $user->id) return json(["msg" => '非法访问数据', "code" => -4]);
        $data->status=$status;
        if ($data->save()) {
            return json(["msg" => "status updated", "code" => 0]);
        } else {
            return json(["msg" => "status update failed", "code" => -5]);
        }
    }

    //软件用户找回密码
    function sendFPCode()
    {
        $id=input('sid');
        $username=input('username');
        $where=array(
            "sid"=>$id,
            "username"=>$username
        );
        //寻找用户
        $userD=new SoftUsers;
        $user=$userD->where($where)->find();
        if(empty($user))
            return json(["msg" => "查找用户失败", "code" => -1]);
        if(empty($user->email))
            return json(["msg" => "email is empty", "code" => -2]);
       // dump($user);
        SendMailCode($user->email);
        return json(["msg" => "验证码已经发送到邮箱**".substr($user->email,-strlen($user->email)+2), "code" => -2]);
    }
    function suFindPassword()
    {
        $id=input('id');
        if($id<1)
            return "invalid software id";
        $soft=SoftList::findCompat($id);
        $this->assign('soft', $soft);
        $this->assign('title', $soft->name."软件找回密码");
        $this->assign('keywords', $soft->name."软件找回密码");
        return $this->fetch('soft/suFP');
    }
    function suChangePWD()
    {
        $sid=input('sid');
        $username=input('username');
        $password=input('newPwd');
        $code=input('code');
        if($sid<1)
            return "invalid software id";
        //寻找用户
        $where=array(
            "sid"=>$sid,
            "username"=>$username
        );
        $userD=new SoftUsers;
        $user=$userD->where($where)->find();
        if(empty($user))
            return json(["msg" => "查找用户失败", "code" => -1]);
        if(empty($user->email))
            return json(["msg" => "email is empty", "code" => -2]);
        // 验证邮箱验证码是否正确
        if(!verifyMailMsg($user->email,$code))
            return json(["msg" => "invalid verify code", "code" => -3]);
        //修改密码
        if(strlen($password)<6)
            return json(["msg" => "密码长度小于6", "code" => -4]);
        $user->password=md5(md5($password));
        if($user->save()){
            return json( [ "msg"=>"密码修改成功","password"=>$password,'username'=>$user->username,"code"=>0]);
        }else{
            return json(["msg" => "password update failed", "code" => -5]);
        }
    }

    function test()
    {
        /*
        $card = new Cards;
        $list = $card->alias('a')
            ->join('soft_list u', 'u.id=a.sid and a.authorid =2')
            ->field('u.name,u.key,a.type,a.id')
            ->order(['a.id' => 'desc'])
            ->select();
        foreach ($list as $k => $v) {
            echo $k . "=>" . $v . "<br />";
        }
        echo $card->getLastSql();
        */

       // echo v8JS("2-des","encryptByDESModeCBC('2','2')");
        // 是否为 GET 请求
        if (request()->isGet()) echo "GET 请求";
        // 是否为 POST 请求
        if (request()->isPost()) echo "POST 请求";
        $data = $this->request->param();
        echo "参数".json_encode($data);
       // echo phpinfo();

    }
}
