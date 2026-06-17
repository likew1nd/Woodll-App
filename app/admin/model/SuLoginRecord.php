<?php
/**
 * Created by IntelliJ IDEA.
 * User: Administrator
 * Date: 2019/4/30
 * Time: 18:20
 */

namespace app\admin\model;


use app\admin\model\BaseModel as Model;

class SuLoginRecord extends Model{
    public function getLoginTimeAttr($LoginTime,$data)
    {
        return date('Y-m-d H:i:s',$data['login_time']);
    }
    public function getheartTimeAttr($heart_time,$data)
    {
        if(empty($data['heart_time'])) return '';
        return date('Y-m-d H:i:s',$data['heart_time']);
    }
    public function getmaccodeAttr($value,$data)
    {
        return strtoupper($value);
    }
}
