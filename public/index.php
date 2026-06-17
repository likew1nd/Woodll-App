<?php
if (!file_exists(__DIR__ . '/install/install.lock')) {
    header('Location: /install/index.php');
    exit;
}

use think\App;

require __DIR__ . '/../vendor/autoload.php';

$http = (new App())->http;

$response = $http->name('admin')->run();

$response->send();

$http->end($response);
