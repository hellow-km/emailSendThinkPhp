<?php
return [
    // 短信驱动 aliyun|tencent|qcloud|mock
    'driver' => env('sms.driver', 'mock'),
    
    // 阿里云配置
    'aliyun' => [
        'access_key' => env('sms.aliyun.access_key', ''),
        'access_secret' => env('sms.aliyun.access_secret', ''),
        'sign_name' => env('sms.aliyun.sign_name', ''),
        'template_code' => env('sms.aliyun.template_code', ''),
    ],
    
    // 腾讯云配置
    'tencent' => [
        'secret_id' => env('sms.tencent.secret_id', ''),
        'secret_key' => env('sms.tencent.secret_key', ''),
        'app_id' => env('sms.tencent.app_id', ''),
        'sign_name' => env('sms.tencent.sign_name', ''),
        'template_id' => env('sms.tencent.template_id', ''),
    ],
];