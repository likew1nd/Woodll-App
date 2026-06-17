<?php 
$info = "install.lock"; 
if(is_dir($info)){
if(rmdir($info)){
echo "很抱歉，您选择了拒绝，所以本系统不能为您提供服务，温馨提示：本系统源码中某些文件已删除完毕！";
}else{
echo "很抱歉，您选择了拒绝，所以本系统不能为您提供服务 - 错误代码： wj";
}
}
if(is_file($info)){
if(unlink($info)){
echo "很抱歉，您选择了拒绝，所以本系统不能为您提供服务，温馨提示：本系统源码中某些文件已删除完毕！";
}else{
echo "很抱歉，您选择了拒绝，所以本系统不能为提供服务，正在重新请求，请稍后...";
if(chmod($info,0777)){
unlink($info);
echo "很抱歉，您选择了拒绝，所以本系统不能为您提供服务，温馨提示：本系统源码中某些文件已删除完毕！";
}else{
echo "很抱歉，您选择了拒绝，所以本系统不能为您提供服务 - 错误代码： 777";
}
}
}
?>

<?php
header("refresh:5;./agreement.html");
print(' ————— 注意：五秒后将自动重新启动“WOODLL 网络验证”安装程序，请稍等...');
?>