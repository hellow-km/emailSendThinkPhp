<?php
namespace app\common\service;

use think\facade\Config;
use think\facade\Log;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class MailService
{
    /**
     * 发送邮件验证码
     * @param string $email 邮箱地址
     * @param string $code 验证码
     * @return bool
     */
   public static function sendCode($email, $code)
    {
        try {
            $subject = '您的验证码 - ' . Config::get('app.site_name', '世茂网络');
            $content = self::getEmailTemplate($code);
        
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host       = 'smtp.qq.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = '2874453400@qq.com';
            $mail->Password   = 'ukbhwtivxyhodggb'; // 必须是授权码
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port       = 465;
        
            $mail->Timeout = 8;
        
            $mail->setFrom('2874453400@qq.com', '邮件服务');
            $mail->addAddress($email);
        
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $content;
        
            return $mail->send();
        
        } catch (\Throwable $e) {
            Log::error('邮件发送失败: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * 获取邮件模板
     * @param string $code 验证码
     * @return string
     */
    private static function getEmailTemplate($code)
    {
        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: 'Microsoft YaHei', Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { text-align: center; padding: 20px 0; }
        .code-box { 
            background: #f8f9fa; 
            border: 2px dashed #dee2e6; 
            padding: 15px; 
            text-align: center; 
            margin: 20px 0;
            font-size: 24px;
            font-weight: bold;
            color: #e74c3c;
            letter-spacing: 5px;
        }
        .footer { 
            margin-top: 30px; 
            padding-top: 20px; 
            border-top: 1px solid #eee; 
            color: #999; 
            font-size: 12px; 
            text-align: center;
        }
        .tip { 
            background: #fff8e1; 
            padding: 10px; 
            border-radius: 4px; 
            margin: 15px 0;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>邮箱验证码</h2>
        </div>
        
        <p>您好！</p>
        <p>您正在尝试登录或注册账户，请使用以下验证码完成操作：</p>
        
        <div class="code-box">{$code}</div>
    </div>
</body>
</html>
HTML;
    }
}