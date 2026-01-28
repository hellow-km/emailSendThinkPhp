<?php
namespace app\controller;

use app\BaseController;
use think\facade\Request;
use think\facade\Session;
use think\facade\Config;
use app\common\service\MailService;
use app\common\service\SmsService;
use think\facade\Log;
use think\facade\Db;

class Index extends BaseController
{
    public function index()
    {
         // 使用绝对路径
    $viewFile = app()->getAppPath() . 'view'  . '/index.html';
    
    if (file_exists($viewFile)) {
        // 直接读取文件内容
        return response(file_get_contents($viewFile));
    } else {
        return "视图文件不存在: " . $viewFile;
    }
    }


/**
     * 发送验证码接口（支持邮箱和手机）
     */
    public function sendCode()
    {
        $type = Request::param('type', 'phone'); // phone 或 email
        $target = Request::param('target'); // 手机号或邮箱
         $name  = Request::post('name');
        $orign  = Request::post('orign');
        if (!$target) {
            return json([
                'code' => 400,
                'msg' => '请提供手机号或邮箱'
            ]);
        }
        
        // 生成验证码
        $code = rand(100000, 999999);
        
        // 根据类型发送验证码
        if ($type === 'email') {
            // 验证邮箱格式
            if (!filter_var($target, FILTER_VALIDATE_EMAIL)) {
                return json([
                    'code' => 400,
                    'msg' => '邮箱格式不正确'
                ]);
            }
             if ($this->userExists($target, null,$orign,$name)) {
                return json(['code' => 409, 'msg' => '该邮箱已注册']);
            }
            // 发送邮件验证码
            $result = MailService::sendCode($target, $code);
            
            if ($result) {
                // 保存到session，5分钟有效
                Session::set("email_code_{$target}", $code, 300);
                   Log::info('set:SID=' . session_id());
                Log::info("set:email_code_{$target}-".$code);
                return json([
                    'code' => 200,
                    'msg' => '验证码已发送到您的邮箱',
                    'data' => [
                        'type' => 'email',
                        'expire' => 300,
                        'code' =>null // 调试模式显示验证码
                    ]
                ]);
            } else {
                return json([
                    'code' => 500,
                    'msg' => '邮件发送失败，请稍后重试'
                ]);
            }
            
        } else {
            // 验证手机号格式
            if (!preg_match('/^1[3-9]\d{9}$/', $target)) {
                return json([
                    'code' => 400,
                    'msg' => '手机号格式不正确'
                ]);
            }
            
            // 发送短信验证码
            $result = SmsService::sendCode($target, $code);
            
            if ($result['success']) {
                // 保存到session，5分钟有效
                Session::set("sms_code_{$target}", $code, 300);
                
                $response = [
                    'code' => 200,
                    'msg' => '验证码已发送到您的手机',
                    'data' => [
                        'type' => 'phone',
                        'expire' => 300
                    ]
                ];
                
                // 开发环境返回验证码便于测试
                if (Config::get('app.debug') && isset($result['code'])) {
                    $response['data']['code'] = $result['code'];
                }
                
                return json($response);
            } else {
                return json([
                    'code' => 500,
                    'msg' => $result['message'] ?? '短信发送失败，请稍后重试'
                ]);
            }
        }
    }
    
    /**
     * 手机登录
     */
    public function loginPhone()
    {
        $phone = Request::post('phone');
        $code  = Request::post('code');

        if (!$phone || !$code) {
            return json(['code' => 400, 'msg' => '请填写完整手机号和验证码']);
        }

        // 校验验证码
        $savedCode = Session::get("sms_code_{$phone}");
        if (!$savedCode || $savedCode != $code) {
            return json(['code' => 400, 'msg' => '验证码错误或已过期']);
        }

        // 清除验证码
        Session::delete("sms_code_{$phone}");

        // TODO: 这里添加用户登录逻辑
        // 1. 检查用户是否存在
        // 2. 不存在则创建用户
        // 3. 设置登录状态
        
        return json([
            'code' => 200, 
            'msg' => '注册成功', 
            'data' => [
                'phone' => $phone,
                'token' => md5($phone . time()), // 示例token
                'expire' => time() + 3600 // 1小时有效
            ]
        ]);
    }

    /**
     * 邮箱登录
     */
    public function loginEmail()
    {
        $email = Request::post('email');
        $code  = Request::post('code');
        $name  = Request::post('name');
        $orign  = Request::post('orign');
        if ($this->userExists($email, null,$orign,$name)) {
            return json(['code' => 409, 'msg' => '该邮箱已存在']);
        }
        if (!$email || !$code) {
            return json(['code'=>400,'msg'=>'请填写完整邮箱和验证码']);
        }
    
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return json(['code'=>400,'msg'=>'邮箱格式不正确']);
        }
    
        $key = "email_code_{$email}";
        $savedCode = Session::get($key);
        Log::info('get:SID=' . session_id());
        Log::info("get:email_code_{$email}"."验证码".$savedCode."-".$code);
        if (!$savedCode || (string)$savedCode !== (string)$code) {
            return json(['code'=>400,'msg'=>'验证码错误或已过期']);
        }
    

      $this->saveData($name, $email, null, $orign);
              Session::delete($key);
        return json([
            'code' => 200,
            'msg'  => '登录成功',
            'data' => [
                'email'  => $email,
                'token'  => md5($email . time()),
                'expire' => time() + 3600
            ]
        ]);
    }

    //连接mysql数据库，并存入数据，name,email,phone,orign,createTime
    private function saveData($name, $email, $phone = null, $orign = '')
    {
        try {
            Db::name('user_log')->insert([
                'name'        => $name,
                'email'       => $email,
                'phone'       => $phone,
                'orign'       => $orign,
                'create_time'=> date('Y-m-d H:i:s'),
            ]);
            Log::info('insert data', compact('name','email','phone','orign'));
        } catch (\Throwable $e) {
            Log::error('保存用户数据失败：' . $e->getMessage());
        }
    }
    
  private function userExists(
    ?string $email = null,
    ?string $phone = null,
    ?string $orign = null,
    ?string $name  = null
): bool {
    if ((!$email && !$phone) || !$orign || !$name) {
        return false;
    }

    return Db::name('user_log')
        // 固定 AND 条件
        ->where('orign', $orign)
        ->where('name', $name)

        // (email OR phone)
        ->where(function ($q) use ($email, $phone) {
            if ($email) {
                $q->whereOr('email', $email);
            }
            if ($phone) {
                $q->whereOr('phone', $phone);
            }
        })

        ->limit(1)
        ->value('id') ? true : false;
}
    /**
     * 生成登录页面HTML
     */
    private function getLoginPageHtml()
    {
        // 这里返回之前提供的HTML代码
        return file_get_contents(root_path() . 'login.html');
    }
}