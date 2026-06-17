<?php
/**
 * Created by IntelliJ IDEA.
 * User: Administrator
 * Date: 2019/4/25
 * Time: 23:58
 */

namespace app\admin\model;


use app\admin\model\BaseModel as Model;

class SoftUsers extends Model{
    public function getcreateTimeAttr($value,$data)
    {
        return date('Y-m-d H:i:s',$value);
    }
    public function getheartTimeAttr($value,$data)
    {
        if(empty($value)) return '';
        return date('Y-m-d H:i:s',$value);
    }
    public function getoutTimeAttr($value,$data)
    {
        return date('Y-m-d H:i:s',$value);
    }
    public function setoutTimeAttr($value)
    {
    	$is_unixtime = ctype_digit((string)$value) && $value <= 2147483647;
    	if($is_unixtime)
    		$sjx=$value;
    	else
    		$sjx=strtotime($value);
        return $sjx==0?$value:$sjx;
    }
    public function setmaccodeAttr($value)
    {
        return strtoupper($value);
    }
    public function setstatusAttr($value)
    {
        return $value=="正常"?0:1;
    }
}
