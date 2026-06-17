<?php
use think\facade\Route;

Route::rule('admin/:controller/:action', ':controller/:action');
Route::rule('admin/:controller', ':controller/index');

Route::rule('/', 'index/index');
Route::rule('index', 'index/index');
Route::rule('index_v1', 'index/index_v1');
Route::rule('index_v2', 'index/index_v2');
Route::rule('userInfo', 'index/userInfo');
Route::rule('doLogin', 'index/doLogin');
Route::rule('doRegister', 'index/doRegister');
Route::rule('doSendMsg', 'index/doSendMsg');
Route::rule('doSendMailMsg', 'index/doSendMailMsg');
Route::rule('profile', 'user/profile');
Route::rule('doChangePwd', 'user/doChangePwd');
Route::rule('forgotpassword', 'user/forgotpassword');
Route::rule('soft_list', 'soft/softList');
Route::rule('webgateway', 'api/webgateway');
Route::rule('susf', 'soft/suFindPassword');
Route::rule('soft/:id', 'soft/detail');
Route::rule('login', 'index/login');

Route::rule('logout', 'index/logout');
Route::rule('index/logout', 'index/logout');
