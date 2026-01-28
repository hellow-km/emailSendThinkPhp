<?php
use think\facade\Route;

// 首页
Route::get('/', 'index/index');

// 登录相关路由
Route::post('index/loginPhone', 'index/loginPhone');
Route::post('index/loginEmail', 'index/loginEmail');
Route::post('index/sendCode', 'index/sendCode');

// 可以添加更多路由
Route::post('index/sendEmailCode', 'index/sendEmailCode'); // 专门发送邮箱验证码
Route::post('index/sendSmsCode', 'index/sendSmsCode');    // 专门发送短信验证码


// 用户日志管理路由
Route::get('admin', 'Admin/index');
Route::post('admin/list', 'Admin/list');
Route::post('admin/add', 'Admin/add');
Route::post('admin/edit', 'Admin/edit');
Route::post('admin/delete', 'Admin/delete');
Route::get('admin/detail', 'Admin/detail');

// 或者使用资源路由简化
Route::resource('admin/user_log', 'Admin');