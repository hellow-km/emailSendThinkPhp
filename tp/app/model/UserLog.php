<?php
namespace app\model;

use think\Model;

class UserLog extends Model
{
    // 设置表名
    protected $table = 'user_log';
    
    // 设置主键
    protected $pk = 'id';
    
    // 自动写入时间戳
    protected $autoWriteTimestamp = false;
    
    // 定义字段类型
    protected $type = [
        'id' => 'integer',
        'name' => 'string',
        'email' => 'string',
        'phone' => 'string',
        'orign' => 'string',
        'create_time' => 'datetime',
    ];
    
    // 搜索条件
    public function search($params)
    {
        $query = $this->newQuery();
        
        if (!empty($params['name'])) {
            $query->where('name', 'like', '%' . $params['name'] . '%');
        }
        
        if (!empty($params['email'])) {
            $query->where('email', 'like', '%' . $params['email'] . '%');
        }
        
        if (!empty($params['phone'])) {
            $query->where('phone', 'like', '%' . $params['phone'] . '%');
        }
        
        if (!empty($params['orign'])) {
            $query->where('orign', 'like', '%' . $params['orign'] . '%');
        }
        
        if (!empty($params['start_date'])) {
            $query->where('create_time', '>=', $params['start_date']);
        }
        
        if (!empty($params['end_date'])) {
            $query->where('create_time', '<=', $params['end_date'] . ' 23:59:59');
        }
        
        return $query;
    }
}