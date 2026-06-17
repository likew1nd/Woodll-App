<?php
/**
 * Created by IntelliJ IDEA.
 * User: Administrator
 * Date: 2019/3/27
 * Time: 6:22
 */

namespace app\admin\controller;

use Crypt\encode;
use Crypt\RSA;
use app\BaseController as Controller;
use think\facade\Cookie;
use think\Request;
use think\facade\Db;
use think\log;
use think\facade\Session;
use app\admin\model\Cards;

class Tools extends Controller
{
    public function qrcode()
    {
        Vendor('phpqrcode.phpqrcode');
        $url = urldecode(input('url'));
        if (!empty($url)) {
            $object = new \QRcode();//实例化二维码类
            $errorCorrectionLevel = "L";
            $matrixPointSize = "4";
            $object->png($url, false, $errorCorrectionLevel, $matrixPointSize);
        } else {
            echo "未传参数";
        }
    }
    public function upload_img()
    {
        $file = request()->file('file');
        $host = input('server.REQUEST_SCHEME') . '://' . input('server.SERVER_NAME');
        if ($file) {
            $hashName = $file->hashName();
            $hashName = str_replace('\\', '/', $hashName);
            $pos = strrpos($hashName, '/');
            if ($pos !== false) {
                $subDir = substr($hashName, 0, $pos);
                $fileName = substr($hashName, $pos + 1);
                $destDir = app()->getRootPath() . 'public' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'files' . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $subDir);
            } else {
                $destDir = app()->getRootPath() . 'public' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'files';
                $fileName = $hashName;
            }
            try {
                validate([
                    'file' => 'fileSize:26817331|fileExt:jpg,png,gif,bmp'
                ])->check(['file' => $file]);

                $info = $file->move($destDir, $fileName);
                if ($info) {
                    $webPath = '/uploads/files/' . $hashName;
                    echo json_encode(["code" => 0, "msg" => "上传成功", "url" => $host . $webPath]);
                } else {
                    echo json_encode(["code" => 0, "msg" => "上传失败", "data" => ["src" => ""]]);
                }
            } catch (\Throwable $e) {
                echo json_encode(["code" => 0, "msg" => "上传失败:" . $e->getMessage(), "data" => ["src" => ""]]);
            }
        }
    }
    public function upload()
    {
        $file = request()->file('file');
        $host = input('server.REQUEST_SCHEME') . '://' . input('server.SERVER_NAME');
        if ($file) {
            $hashName = $file->hashName();
            $hashName = str_replace('\\', '/', $hashName);
            $pos = strrpos($hashName, '/');
            if ($pos !== false) {
                $subDir = substr($hashName, 0, $pos);
                $fileName = substr($hashName, $pos + 1);
                $destDir = app()->getRootPath() . 'public' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'files' . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $subDir);
            } else {
                $destDir = app()->getRootPath() . 'public' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'files';
                $fileName = $hashName;
            }
            try {
                validate([
                    'file' => 'fileSize:26817331|fileExt:jpg,png,gif,gz,zip,rar,7z,apk,bmp'
                ])->check(['file' => $file]);

                $info = $file->move($destDir, $fileName);
                if ($info) {
                    $webPath = '/uploads/files/' . $hashName;
                    echo json_encode(["code" => 0, "msg" => "上传成功", "data" => ["src" => $host . $webPath]]);
                } else {
                    echo json_encode(["code" => 0, "msg" => "上传失败", "data" => ["src" => ""]]);
                }
            } catch (\Throwable $e) {
                echo json_encode(["code" => 0, "msg" => "上传失败:" . $e->getMessage(), "data" => ["src" => ""]]);
            }
        }
    }
    //测试\
    function test()
    {
        echo "token:".input("server.HTTP_token");
        echo "返回数据:cookie:".json_encode($_COOKIE);
        Cookie::set('abc',"sadsa");
        Cookie::set('abc1',"sadsa1");

        return json($_POST);
        //echo phpinfo();
        /*
		$data = $this->request->param();
		$where = [
		'card_no'=>['like BINARY',$data['card_no']],
		'sid'=>['like BINARY',$data['id']]
		];
		//$where['card_no'] = ['LIKE',$data['card_no']];
		//$where['sid'] = ['LIKE',$data['id']];
		$card = Cards::where($where)->field('card_no',true)->find();
		print_r($card);
		//echo curl_get("http://opendata.baidu.com/api.php", "query=183.160.246.22&co=&resource_id=6006&t=1412300361645&ie=utf8&oe=utf8&format=json&tn=baidu");
		//echo file_get_contents('http://opendata.baidu.com/api.php?query=183.160.246.22&co=&resource_id=6006&t=1412300361645&ie=utf8&oe=utf8&format=json&tn=baidu');
        sendMail("2652648116@qq.com","卡密发货","这是你的卡密");
      //echo  captcha_src();
        //举个粒子
 /*        $Rsa=new RSA();
        $keys=$Rsa->new_rsa_key();
		print_r($keys);
        $privkey = $keys['privkey'];
        $pubkey  = $keys['pubkey'];
        $Rsa->init($privkey, $pubkey,TRUE);
        //原文
        $data =strrev('0GqODLaVvXnddmT!fXSYUKJH') ;
        $encode = $Rsa->pub_encode($data);
        echo $encode;
        echo "<br />";
        echo $pubkey;
        echo "<br />";
        echo $privkey;
        $arr=explode("\n",$privkey);
        print_r($arr) ;
        $priv="";
        echo "<hr />";
        for($i=1;$i<count($arr)-2;$i++)
        {
            $priv=$priv. $arr[$i]."\n";
        }
        echo $priv; */
    }
}
