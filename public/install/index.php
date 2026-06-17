<?php
header('Content-type:text/html;charset=utf-8');

if (file_exists('./install.lock')) {
    echo '温馨提示：您已经安装过 WOODLL 网络验证系统，如需重新安装请先删除 ./public/install/install.lock 文件。';
    die;
}

$step = isset($_GET['c']) ? $_GET['c'] : 'agreement';

if ($step === 'agreement') {
    require './agreement.html';
    exit;
}

if ($step === 'test') {
    require './test.html';
    exit;
}

if ($step === 'create') {
    require './create.html';
    exit;
}

if ($step !== 'success') {
    require './agreement.html';
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    require './create.html';
    exit;
}

$data = $_POST;
foreach (['DB_HOST', 'DB_PORT', 'DB_USER', 'DB_NAME', 'DB_PREFIX', 'adminName', 'adminPwd'] as $field) {
    if (!isset($data[$field]) || $data[$field] === '') {
        die("<script>alert('请完整填写数据库和管理员信息');history.go(-1)</script>");
    }
}
$data['DB_PWD'] = isset($data['DB_PWD']) ? $data['DB_PWD'] : '';

$link = @new mysqli("{$data['DB_HOST']}:{$data['DB_PORT']}", $data['DB_USER'], $data['DB_PWD']);
$error = $link->connect_error;
if (!is_null($error)) {
    $error = addslashes($error);
    die("<script>alert('数据库连接失败：{$error}');history.go(-1)</script>");
}

$link->query("SET NAMES 'utf8'");
if (version_compare($link->server_info, '5.0', '<')) {
    die("<script>alert('请将 MySQL 升级到 5.0 以上');history.go(-1)</script>");
}

if (!$link->select_db($data['DB_NAME'])) {
    $create_sql = 'CREATE DATABASE IF NOT EXISTS `' . str_replace('`', '', $data['DB_NAME']) . '` DEFAULT CHARACTER SET utf8;';
    $link->query($create_sql) or die('创建数据库失败');
    $link->select_db($data['DB_NAME']);
}

$sql = file_get_contents('./test.sql');
if (substr($sql, 0, 3) === "\xEF\xBB\xBF") {
    $sql = substr($sql, 3);
}
$sql = str_replace('yz_admin_username', $data['adminName'], $sql);
$sql = str_replace('yz_admin_password', md5(md5($data['adminPwd'])), $sql);
$sql = str_replace('yy_', $data['DB_PREFIX'], $sql);
$sql = str_replace('yz_', $data['DB_PREFIX'], $sql);
$sql_array = preg_split("/;[\r\n]+/", $sql);
foreach ($sql_array as $item) {
    $item = trim($item);
    if ($item !== '') {
        $link->query($item);
    }
}
$link->close();

$env_str = <<<ENV
APP_DEBUG = true

[APP]
DEFAULT_TIMEZONE = Asia/Shanghai

[DATABASE]
TYPE = mysql
HOSTNAME = {$data['DB_HOST']}
DATABASE = {$data['DB_NAME']}
USERNAME = {$data['DB_USER']}
PASSWORD = {$data['DB_PWD']}
HOSTPORT = {$data['DB_PORT']}
CHARSET = utf8mb4
PREFIX = {$data['DB_PREFIX']}

DB_TYPE = mysql
DB_HOST = {$data['DB_HOST']}
DB_NAME = {$data['DB_NAME']}
DB_USER = {$data['DB_USER']}
DB_PASS = {$data['DB_PWD']}
DB_PORT = {$data['DB_PORT']}
DB_CHARSET = utf8mb4
DB_PREFIX = {$data['DB_PREFIX']}
ENV;

$env_file = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . '.env';
$database_file = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'database.php';
$lock_file = __DIR__ . DIRECTORY_SEPARATOR . 'install.lock';

$database_config = [
    'default'         => 'mysql',
    'time_query_rule' => [],
    'auto_timestamp'  => true,
    'datetime_format' => 'Y-m-d H:i:s',
    'datetime_field'  => '',
    'connections'     => [
        'mysql' => [
            'type'            => 'mysql',
            'hostname'        => $data['DB_HOST'],
            'database'        => $data['DB_NAME'],
            'username'        => $data['DB_USER'],
            'password'        => $data['DB_PWD'],
            'hostport'        => $data['DB_PORT'],
            'params'          => [],
            'charset'         => 'utf8mb4',
            'prefix'          => $data['DB_PREFIX'],
            'deploy'          => 0,
            'rw_separate'     => false,
            'master_num'      => 1,
            'slave_no'        => '',
            'fields_strict'   => true,
            'break_reconnect' => false,
            'trigger_sql'     => true,
            'fields_cache'    => false,
        ],
    ],
];

$database_str = "<?php\n\nreturn " . var_export($database_config, true) . ";\n";

if (file_put_contents($env_file, $env_str) === false) {
    die("<script>alert('写入 .env 配置文件失败，请检查项目根目录写入权限');history.go(-1)</script>");
}

if (file_put_contents($database_file, $database_str) === false) {
    die("<script>alert('Write config/database.php failed. Please check config directory permission.');history.go(-1)</script>");
}

if (@touch($lock_file) === false) {
    die("<script>alert('创建安装锁文件失败，请检查 public/install 目录写入权限');history.go(-1)</script>");
}
require './success.html';
