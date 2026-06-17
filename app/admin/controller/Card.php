<?php



/**



 * Created by IntelliJ IDEA.



 * User: Administrator



 * Date: 2019/5/2



 * Time: 22:40



 */







namespace app\admin\controller;







use app\admin\model\BuycardRecord;



use app\admin\model\CardRecord;



use app\admin\model\CardType;



use app\admin\model\PayRecord;



use app\admin\model\SoftList;



use think\facade\Log;



use think\facade\Session;



use app\BaseController as Controller;



use app\admin\model\Cards;







class Card extends Controller



{



    function cardTypeList()



    {



        $user = Session::get('user');



        if (empty($user)) {



            {



                $this->assign('title', "超时");



                $this->assign('keywords', "超时");



                return $this->fetch('index/timeout');



            }



        }







        $this->assign('user', $user);



        $this->assign('title', $user->username . " - 登录记录");



        $this->assign('keywords', $user->username . "- 登录记录");



        return $this->fetch('card/cardTypeList');



    }







    function getCardTypeList()



    {



        $user = Session::get('user');



        if (empty($user))



            return json(["msg" => '登录超时', "code" => -1]);



        $pageSize = input('limit');



        $sname = input('sname');



        $id = input('id');



        $where = [



            ['authorid', '=', $user->id],



        ];



        if (!empty($sname)) {



            $where[] = ['sname', 'like', '%' . $sname . '%'];



        }



        if (!empty($id)) {



            $where[] = ['id', '=', $id];



            $list = CardType::where($where)->order('id desc')->limit(input('offset'), $pageSize)->select();



        } else {



            //过滤掉不需要的字段



            $list = CardType::where($where)->limit(input('offset'), $pageSize)->select();



        }



        $total = CardType::where($where)->count();



        return json(["total" => $total, "rows" => $list]);



    }







    function deleteCardType()



    {



        $user = Session::get('user');



        if (empty($user))



            return json(["msg" => '登录超时', "code" => -1]);



        $id = input('id');



        if ($id <= 0) return json(["msg" => '卡类编号不正确', "code" => -2]);



        $ct = CardType::findCompat($id);



        if (empty($ct)) return json(["msg" => '没有找到该类型', "code" => -3]);



        if ($ct->authorid != $user->id) return json(["msg" => '你不能删除不属于你的卡密类型', "code" => -4]);



        if ($ct->delete())



            return json(["msg" => '删除成功', "code" => 0]);



        else



            return json(["msg" => '删除失败', "code" => -5]);



    }







    //添加卡类型页面



    function addType()



    {



        $user = Session::get('user');



        if (empty($user)) {



            $this->assign('title', "超时");



            $this->assign('keywords', "超时");



            return $this->fetch('index/timeout');



        }



        



        $id = input('id');



        if ($id <= 0) {



            //新增



            $ct = [



                'id' => 0,



                'sid' => 0,



                'type' => 0,



                'cardValue' => "",



                'remark' => '',



                'authorid' => $user->id



            ];



            $this->assign('sbName', '确认添加');



            $this->assign('cnName', '取消添加');



        } else {



            //修改



            $ctObj=new CardType();



            $ct = $ctObj->where(["id"=>$id,"authorid" => $user->id])->find();



            $this->assign('sbName', '确认修改');



            $this->assign('cnName', '取消修改');



        }



        $this->assign('user', $user);



        $this->assign('card', $ct);



        $this->assign('title', $user->username . " - 编辑卡类");



        $this->assign('keywords', $user->username . "- 编辑卡类");



        return $this->fetch('card/editCard');



    }







    //编辑卡密类型



    function editCardType()



    {



        //新增还是修改



        $user = Session::get('user');



        if (empty($user))



            return json(["msg" => '登录超时', "code" => -1]);



        $id = input('id');







        if ($id <= 0) {



            //新增



            $ct = new CardType();



        } else {



            //修改



            $ctObj=new CardType();



            $ct = $ctObj->where(["id"=>$id,"authorid"=>$user->id])->find();



            if(empty($ct))return json(["msg" => '变量不存在', "code" => -3]);



        }



        $data = $this->request->param();



        $allowedFields = ['sid', 'sname', 'type', 'cardValue', 'remark', 'authorid', 'add_time'];



        $data = array_intersect_key($data, array_flip($allowedFields));



        $data['sid'] = isset($data['sid']) ? (int)$data['sid'] : 0;



        $data['type'] = isset($data['type']) ? (int)$data['type'] : 0;



        $data['cardValue'] = isset($data['cardValue']) ? (int)$data['cardValue'] : 0;



        $data['sname'] = isset($data['sname']) ? trim($data['sname']) : '';



        $data['remark'] = isset($data['remark']) ? trim($data['remark']) : '';



        $data['authorid'] = $user->id;







        if ($data['sid'] <= 0) {



            return json(["msg" => '请选择所属软件', "code" => -4]);



        }



        if ($data['cardValue'] <= 0) {



            return json(["msg" => '请输入卡值', "code" => -5]);



        }



        $checkWhere = [



            ['sid', '=', $data['sid']],



            ['type', '=', $data['type']],



            ['cardValue', '=', $data['cardValue']],



        ];



        if ($id > 0) {



            $checkWhere[] = ['id', '<>', $id];



        }



        if (CardType::where($checkWhere)->find()) {



            return json(["msg" => "该软件下已存在相同类型和面值的卡密类型", "code" => -6]);



        }



        if ($id <= 0) {



            $data['add_time'] = time();



        }







        try {



            if ($ct->allowField($allowedFields)->save($data) !== false) {



                return json(["msg" => '成功', "code" => 0]);



            }



        } catch (\Throwable $e) {



            return json(["msg" => '保存失败: ' . $e->getMessage(), "code" => -500]);



        }



        return json(["msg" => '失败', "code" => -2]);



    }







    //卡密页面



    function cardList()



    {



        $user = Session::get('user');



        if (empty($user)) {



            $this->assign('title', "超时");



            $this->assign('keywords', "超时");



            return $this->fetch('index/timeout');



        }



        $this->assign('user', $user);



        $this->assign('title', $user->username . " - 卡密列表");



        $this->assign('keywords', $user->username . "- 卡密列表");



        return $this->fetch('card/CardList');



    }







    //获取卡密数据



    function getCardList()



    {



        $user = Session::get('user');



        if (empty($user))



            return json(["msg" => '登录超时', "code" => -1]);



        $pageSize = input('limit');



        $sname = input('sname');



        $type = input('type');



        $status = input('status');



        $userAccount = input('userAccount');







        $where = [



            ['authorid', '=', $user->id],



        ];



        if (!empty($sname)) {



            $where[] = ['sname', 'like', '%' . $sname . '%'];



        }



        if ($type !== null && $type !== '' && $type != -1) {



            $where[] = ['type', '=', (int)$type];



        }



        if ($status !== null && $status !== '' && $status != -1) {



            $where[] = ['status', '=', (int)$status];



        }



        if ($userAccount !== null && $userAccount !== '') {



            $where[] = ['user_account', 'like', '%' . $userAccount . '%'];



        }







        //过滤掉不需要的字段



        $list = Cards::where($where)->order('id desc')->limit(input('offset'), $pageSize)->select();



        $total = Cards::where($where)->count();



        return json(["total" => $total, "rows" => $list]);



    }



    //更改卡密状态



    function updateCardStatus()



    {



        $user = Session::get('user');



        if (empty($user))



            return json(["msg" => '登录超时', "code" => -1]);



        $id = input('id');



        $status = input('status');



        if ($status < 0 || $status > 2) return json(["msg" => '输入的状态码错误', "code" => -4]);



        if ($id <= 0) return json(["msg" => '卡密ID错误', "code" => -2]);



        $card = Cards::findCompat($id);



        if($card->authorid!=$user->id) return json(["msg" => '非法调用', "code" => -10]);



        if (!$card) return json(["msg" => '卡密不存在', "code" => -3]);



        //被使用的不可更改



        if ($card->status == 1) return json(["msg" => '卡密已经被使用啦,不可更改状态哦', "code" => -6]);



        $card->status = $status;



        if ($card->save()) {



            return json(["msg" => '更新成功', "status" => $card->status, "code" => 0]);



        } else {



            return json(["msg" => '更新失败', "code" => -5]);



        }



    }







    //删除卡密



    function deleteCard()



    {



        $user = Session::get('user');



        if (empty($user))



            return json(["msg" => '登录超时', "code" => -1]);



        $id = input('id');



        if ($id <= 0) return json(["msg" => '卡密编号不正确', "code" => -2]);



        $ct = Cards::findCompat($id);



        if($ct->authorid!=$user->id) return json(["msg" => '非法调用', "code" => -10]);



        if (empty($ct)) return json(["msg" => '没有找到该卡密', "code" => -3]);



        if ($ct->authorid != $user->id) return json(["msg" => '你不能删除不属于你的卡密', "code" => -4]);



        if ($ct->delete())



            return json(["msg" => '删除成功', "code" => 0]);



        else



            return json(["msg" => '删除失败', "code" => -5]);



    }







    //添加卡密页面



    function addCard()



    {



        $user = Session::get('user');



        if (empty($user)) {



            $this->assign('title', "超时");



            $this->assign('keywords', "超时");



            return $this->fetch('index/timeout');



        }



        $this->assign('user', $user);



        $this->assign('onceMax', config('cards.onceMax'));



        $this->assign('sbName', '确认添加');



        $this->assign('cnName', '取消添加');



        $this->assign('title', $user->username . " - 添加卡密");



        $this->assign('keywords', $user->username . "- 添加卡密");



        return $this->fetch('card/addCard');



    }







    //获取简单的卡密类型



    function getCardTypeSimple()



    {



        $user = Session::get('user');



        if (empty($user))



            return json(["msg" => '登录超时', "code" => -1]);



        $sid = input('id');



        if ($sid <= 0) return json(["msg" => '软件ID错误', "code" => -2]);



        $where = [



            'sid' => $sid



        ];



        $list = CardType::where($where)->field(['id', 'type', 'cardValue'])->select();



        return json(["total" => count($list), "rows" => $list, "code" => 0]);



    }







    //根据软件ID获取唯一的卡值列表



    function getCardValuesBySid()



    {



        $user = Session::get('user');



        if (empty($user))



            return json(["msg" => '请服务器登录超时', "code" => -1]);



        $sid = (int)input('sid');

        $type = (int)input('type');



        $where = ['authorid' => $user->id];



        if ($sid != -1) {



            $where['sid'] = $sid;



        }

        if ($type != -1) {



            $where['type'] = $type;



        }



        $values = CardType::where($where)->group('cardValue')->order('cardValue asc')->column('cardValue');



        return json(["code" => 0, "values" => $values]);



    }



    //用户添加卡密



    function userAddCard()



    {



        $user = Session::get('user');



        if (empty($user))



            return json(["msg" => '登录超时', "code" => -1]);



        $data = $this->request->param();



        $cardNum = floor($data['cardNum']);



        if ($cardNum <= 0) return json(["msg" => '生成数量不能小于1', "code" => -1]);



        $onceMax=config('cards.onceMax');



        $max=config('cards.max');



        //判断是否大于单次最大添加值



        if($cardNum>$onceMax) return json(["msg" => '每个软件您最多添加'.$onceMax.'张卡密哦', "code" => -5]);



        //判断sid与type是否存在



        if ($data['sid'] <= 0 || $data['typeId'] < 0) return json(["msg" => '传入参数错误', "code" => -2]);



        //查询用户目前有多少卡



        $currNum=Cards::where(['sid'=>$data['sid'],  'authorid' => $user->id])->count();



        if($currNum+$cardNum>$max) return json(["msg" => '每个软件您最多添加'.$max.'张卡密哦,当前该软件有'.$currNum.'张卡密', "code" => -6]);



        //找出卡类型数据



        $ct = CardType::findCompat($data['typeId']);



        if (!$ct) return json(["msg" => '卡密类型没有找到', "code" => -3]);



        $list = array();



        $keys = "";



        for ($i = 0; $i < $cardNum; $i++) {



            $card_no = $data['cardHead'] . strtoupper(MD5(guid()));



            $keys = $keys . $card_no . "<br />";



            $list[$i] = [



                'sid' => $data['sid'],



                'sname' => $data['sname'],



                'type' => $ct->type,



                'authorid' => $user->id,



                'card_no' => $card_no,



                'status' => 0,



                'card_value' => $ct->cardValue,



                'remark' => $data['remark'],



                'add_time' => time()



            ];







        }



        $card = new Cards();



        if ($card->saveAll($list)) {



            return json(["msg" => '添加成功', "keys" => $keys, "code" => 0]);



        } else {



            return json(["msg" => '添加失败', "code" => -4]);



        }



    }







    //充值记录页面



    function cardLogList()



    {



        $user = Session::get('user');



        if (empty($user)) {



            $this->assign('title', "超时");



            $this->assign('keywords', "超时");



            return $this->fetch('index/timeout');



        }



        $this->assign('user', $user);



        $this->assign('title', $user->username . " - 充值日志");



        $this->assign('keywords', $user->username . "- 充值日志");



        return $this->fetch('card/cardLogList');



    }







    function getCardLogList()



    {



        $user = Session::get('user');



        if (empty($user))



            return json(["msg" => '登录超时', "code" => -1]);



        $pageSize = input('limit');



        $sname = input('sname');



        $user_account=input('user_account');



        $where = [



            ['authorid', '=', $user->id],



        ];



        if (!empty($sname)) {



            $where[] = ['sname', 'like', '%' . $sname . '%'];



        }



        if (!empty($user_account)) {



            $where[] = ['user_account', 'like', '%' . $user_account . '%'];



        }



        //过滤掉不需要的字段



        $list = CardRecord::where($where)->limit(input('offset'), $pageSize)->select();



        $total = CardRecord::where($where)->count();



        return json(["total" => $total, "rows" => $list]);



    }







    //导出卡密页面



    function exportCard()



    {



        $user = Session::get('user');



        if (empty($user)) {



            $this->assign('title', "超时");



            $this->assign('keywords', "超时");



            return $this->fetch('index/timeout');



        }



        $this->assign('user', $user);



        $this->assign('title', $user->username . " - 充值日志");



        $this->assign('keywords', $user->username . "- 充值日志");



        return $this->fetch('card/exportCard');



    }







    //导出卡密



    function userExportCard()



    {



        $user = Session::get('user');



        if (empty($user))



            return json(["msg" => '登录超时', "code" => -1]);



        //查询出数据



        $sid = (int)input('sid');



        $type = (int)input('type');



        $status=(int)input('status');



        $exportType = (int)input('exportType');



        $remark = input("remark");



        $where = [



            "authorid" => $user->id



        ];



        if($status!=-1) $where["status"] = $status;



        if ($sid != -1) $where["sid"] = $sid;



        if ($type != -1) $where["type"] = $type;



        if (!empty($remark)) $where['remark'] = $remark;



        $list = Cards::where($where)->select();



        foreach ($list as $key => $value) {



            switch ($list[$key]['type']) {



                case 0: //点卡



                    $list[$key]['type'] = "点卡";



                    break;



                case 1: //分



                    $list[$key]['type'] = "分卡";



                    break;



                case 2: //时



                    $list[$key]['type'] = "时卡";



                    break;



                case 3: //天



                    $list[$key]['type'] = "天卡";



                    break;



                case 4: //周



                    $list[$key]['type'] = "周卡";



                    break;



                case 5: //月 31天



                    $list[$key]['type'] = "月卡";



                    break;



                case 6: //年 365天



                    $list[$key]['type'] = "年卡";



                    break;



                default:



            }



        }



        //导出xls 还是txt



        if ($exportType == 0) {



            //xls



            $field = array(



                'A' => array('sname', '软件名称'),



                'B' => array('card_no', '充值卡号'),



                'C' => array('type', '类型'),



                'D' => array('card_value', '面值'),



                'E' => array('add_time', '添加时间'),



                'F' => array('remark', '备注'),



            );



            phpExcelList($field, $list, '卡密列表_' . date('Y-m-d'));



        } else {



            //txt - 清空所有已缓冲输出（含 Debu��发过货啦";



        }



		Log::record('开始记录发卡2', Log::DEBUG);



        //取出软件ID,卡密类型ID bid,数量,邮箱



        //给卡密打上销售表ID



        $ct = CardType::findCompat($br->cardId);



        //修复没有限制卡值的



        $where = [



            'sid' => $br->sid,



            'type' => $ct->type,



            'status' => 0,



            'bid' => 0,



            'proxyid'=>0,



            'card_value'=>$ct->cardValue //修复同类型卡密 不判断卡值



        ];



		Log::record('开始记录发卡3', Log::DEBUG);



        Log::record('sendcard'.json_encode($where), Log::DEBUG);



        $list = Cards::where($where)->limit($br->num)->select();



        $arr = [];



        $key = "以下是您购买的卡密信息<br />订单号:" . $orderNo . "<br />";



        for ($i = 0; $i < count($list); $i++) {



            $tmp = [



                "id" => $list[$i]['id'],



                "bid" => $br->id



            ];



            $arr[$i] = $tmp;



            // $list[$i]['status']=1;



            //echo $list[$i]['id']."-".$list[$i]['card_no']."<br />";



            $key = $key . $list[$i]['card_no'] . "   <br />";



        }



        if (strlen($br->email) > 4) {



            //发送卡密到邮箱



            sendMail($br->email, "购买的卡密", $key);



        }



        $cards = new Cards;



        $cards->isUpdate()->saveAll($arr);



        $br->status = 1;



        $br->save();



		return json_encode($where);



    }







    //售卡日志页面



    







    //售卡日志json



    







    //查找订单



    function searchOrder()



    {



        $email = input('email');



        $orderno = input('orderno');



        $list = [];



        if (empty($orderno) && strlen($email) < 5) {



            //没有传参的情况



        }



        if (!empty($orderno)) {



            $list = PayRecord::getByorderno($orderno);



            if (empty($list)) return "订单号不存在";



            $this->redirect('/tk.html?orderNo=' . $orderno);



            //订单号方式就查询出卡密 直接返回



        }



        if (strlen($email) > 5) {



            //邮件方式就查询出所有订单BID



            $br = BuycardRecord::where(['email' => $email, 'status' => 1])->select();



            if (empty($br)) return "该联系方式,没有任何完成订单";



            foreach ($br as $data) {



                echo "<a href='/admin/card/tkByEid?eid=" . $data['eid'] . "'>订单ID:" . $data['id'] . " 软件名称:" . $data['sname'] . " 购卡数量:" . $data['num'] . " 订单金额:" . $data['money'] . "</a><br />";



            }



            return;



        }



        $this->assign('title', "订单查询");



        $this->assign('keywords', "订单查询");



        return $this->fetch('card/searchOrder');



    }



    //卡密高级删除页面



    function deleteMoreCard()



    {



        $user = Session::get('user');



        if (empty($user)) {



            $this->assign('title', "超时");



            $this->assign('keywords', "超时");



            return $this->fetch('index/timeout');



        }



        $this->assign('user', $user);



        $this->assign('title', $user->username . " - 充值日志");



        $this->assign('keywords', $user->username . "- 充值日志");



        return $this->fetch('card/deleteMoreCard');



    }



    function userDeleteMoreCard()



    {



        $user = Session::get('user');



        if (empty($user))



            return json(["msg" => '登录超时', "code" => -1]);



        $sid=(int)input("sid");



        $type=(int)input("type");



        $status=(int)input("status");



        $cardValue=(int)input("cardValue");



        $remark=input("remark");



        $end=input("end");



        $where = [



            ["authorid", "=", $user->id]



        ];



        if($status!=-1) $where[] = ['status', '=', $status];



        if ($sid != -1) $where[] = ['sid', '=', $sid];



        if ($type != -1) $where[] = ['type', '=', $type];



        if ($cardValue != -1) $where[] = ['card_value', '=', $cardValue];



        if (!empty($remark)) $where[] = ['remark', 'like', '%' . $remark . '%'];



        if(!empty($end)){



            $arr=explode(" - ",$end);



            if(count($arr)==2){



                $where[] = ['add_time', 'between', [strtotime($arr[0]), strtotime($arr[1])]];



            }



        }



        $total=Cards::where($where)->delete();



        return json(["msg" => '成功', "total" => $total,"code"=>0]);



    }



    function test()



    {



		Log::record('测试调试错误信息', Log::DEBUG);



        Log::record('调试的SQL：', Log::SQL);



        //卡密发货



       // $card = new Card();



        //$card->sendCard(97,"2019050522351110297995");



    }



}



