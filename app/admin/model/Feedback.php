<?php
/**
 * Created by IntelliJ IDEA.
 * User: Administrator
 * Date: 2019/6/18
 * Time: 13:24
 */

namespace app\admin\model;


use app\admin\model\BaseModel as Model;

class Feedback extends Model{
    public function getAddTimeAttr($value,$data)
    {
        return date('Y-m-d H:i:s',$value);
    }
}