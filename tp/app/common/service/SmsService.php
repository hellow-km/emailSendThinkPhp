<?php
namespace app\common\service;

use think\facade\Config;
use think\facade\Log;

class SmsService
{
    /**
     * 发送短信验证码
     * @param string $phone 手机号
     * @param string $code 验证码
     * @return array
     */
    public static function sendCode($phone, $code)
    {
        try {
            // 获取短信配置
            $smsConfig = Config::get('sms', []);
            
            // 根据配置选择短信服务商
            switch ($smsConfig['driver'] ?? 'aliyun') {
                case 'aliyun':
                    return self::sendByAliyun($phone, $code, $smsConfig);
                case 'tencent':
                    return self::sendByTencent($phone, $code, $smsConfig);
                case 'qcloud':
                    return self::sendByQcloud($phone, $code, $smsConfig);
                default:
                    // 模拟发送（开发环境使用）
                    return self::mockSend($phone, $code);
            }
            
        } catch (\Exception $e) {
            Log::error('短信发送失败: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => '短信发送失败: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * 阿里云短信发送
     * @param string $phone
     * @param string $code
     * @param array $config
     * @return array
     */
    private static function sendByAliyun($phone, $code, $config)
    {
        // 这里需要安装阿里云SDK: composer require alibabacloud/dysmsapi-20170525
        try {
            // 示例代码，实际使用时请参考阿里云官方文档
            /*
            AlibabaCloud::accessKeyClient($config['access_key'], $config['access_secret'])
                ->regionId('cn-hangzhou')
                ->asDefaultClient();
            
            $result = AlibabaCloud::rpc()
                ->product('Dysmsapi')
                ->version('2017-05-25')
                ->action('SendSms')
                ->method('POST')
                ->host('dysmsapi.aliyuncs.com')
                ->options([
                    'query' => [
                        'PhoneNumbers' => $phone,
                        'SignName' => $config['sign_name'],
                        'TemplateCode' => $config['template_code'],
                        'TemplateParam' => json_encode(['code' => $code]),
                    ],
                ])
                ->request();
            */
            
            // 模拟成功
            Log::info("阿里云短信发送: {$phone}, 验证码: {$code}");
            return [
                'success' => true,
                'message' => '短信发送成功',
                'code' => $code // 开发环境返回验证码便于测试
            ];
            
        } catch (\Exception $e) {
            throw new \Exception("阿里云短信发送失败: " . $e->getMessage());
        }
    }
    
    /**
     * 腾讯云短信发送
     * @param string $phone
     * @param string $code
     * @param array $config
     * @return array
     */
    private static function sendByTencent($phone, $code, $config)
    {
        // 需要安装腾讯云SDK: composer require tencentcloud/tencentcloud-sdk-php
        try {
            // 示例代码
            Log::info("腾讯云短信发送: {$phone}, 验证码: {$code}");
            return [
                'success' => true,
                'message' => '短信发送成功',
                'code' => null
            ];
            
        } catch (\Exception $e) {
            throw new \Exception("腾讯云短信发送失败: " . $e->getMessage());
        }
    }
    
    /**
     * 模拟发送（开发环境）
     * @param string $phone
     * @param string $code
     * @return array
     */
    private static function mockSend($phone, $code)
    {
        Log::info("模拟短信发送: {$phone}, 验证码: {$code}");
        
        return [
            'success' => true,
            'message' => '短信发送成功（模拟）',
            'code' => null // 返回验证码便于测试
        ];
    }
}