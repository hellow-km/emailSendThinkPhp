<?php
namespace app\controller;

use app\BaseController;
use app\model\UserLog;
use think\Request;

class Admin extends BaseController
{
    public function index()
    {
         // 使用绝对路径
    $viewFile = app()->getAppPath() . 'view'  . '/admin/index.html';
    
    if (file_exists($viewFile)) {
        // 直接读取文件内容
        return response(file_get_contents($viewFile));
    } else {
        return "视图文件不存在: " . $viewFile;
    }
    }


/**
     * 获取用户日志列表（API）
     * 访问路径：POST /admin/user_log/list
     */
    public function list(Request $request)
    {
        try {
            $params = $request->param();
            $page = $request->param('page', 1);
            $limit = $request->param('limit', 10);
            
            $userLogModel = new UserLog();
            $query = $userLogModel->search($params);
            
            // 获取总数
            $total = $query->count();
            
            // 分页查询
            $list = $query->page($page, $limit)
                         ->order('create_time', 'desc')
                         ->select();
            
            return json([
                'code' => 0,
                'msg' => 'success',
                'count' => $total,
                'data' => $list
            ]);
        } catch (\Exception $e) {
            return json([
                'code' => 500,
                'msg' => '获取数据失败：' . $e->getMessage(),
                'count' => 0,
                'data' => []
            ]);
        }
    }
    
    /**
     * 添加用户日志
     * 访问路径：POST /admin/user_log/add
     */
    public function add(Request $request)
    {
        if ($request->isPost()) {
            try {
                $data = $request->post();
                $data['create_time'] = date('Y-m-d H:i:s');
                
                $userLog = new UserLog();
                $result = $userLog->save($data);
                
                if ($result) {
                    return json(['code' => 0, 'msg' => '添加成功']);
                } else {
                    return json(['code' => 1, 'msg' => '添加失败']);
                }
            } catch (\Exception $e) {
                return json(['code' => 1, 'msg' => '添加失败：' . $e->getMessage()]);
            }
        }
    }
    
    /**
     * 编辑用户日志
     * 访问路径：POST /admin/user_log/edit
     */
    public function edit(Request $request)
    {
        if ($request->isPost()) {
            try {
                $data = $request->post();
                $id = $request->post('id');
                
                $userLog = UserLog::find($id);
                if (!$userLog) {
                    return json(['code' => 1, 'msg' => '记录不存在']);
                }
                
                $result = $userLog->save($data);
                
                if ($result !== false) {
                    return json(['code' => 0, 'msg' => '更新成功']);
                } else {
                    return json(['code' => 1, 'msg' => '更新失败']);
                }
            } catch (\Exception $e) {
                return json(['code' => 1, 'msg' => '更新失败：' . $e->getMessage()]);
            }
        }
    }
    
    /**
     * 删除用户日志
     * 访问路径：POST /admin/user_log/delete
     */
    public function delete(Request $request)
    {
        try {
            $id = $request->post('id');
            
            if (empty($id)) {
                return json(['code' => 1, 'msg' => '参数错误']);
            }
            
            // 支持批量删除
            if (is_array($id)) {
                $result = UserLog::whereIn('id', $id)->delete();
            } else {
                $result = UserLog::destroy($id);
            }
            
            if ($result) {
                return json(['code' => 0, 'msg' => '删除成功']);
            } else {
                return json(['code' => 1, 'msg' => '删除失败']);
            }
        } catch (\Exception $e) {
            return json(['code' => 1, 'msg' => '删除失败：' . $e->getMessage()]);
        }
    }
    
    /**
     * 获取用户日志详情
     * 访问路径：GET /admin/user_log/detail
     */
    public function detail(Request $request)
    {
        try {
            $id = $request->param('id');
            $data = UserLog::find($id);
            
            if ($data) {
                return json(['code' => 0, 'data' => $data]);
            } else {
                return json(['code' => 1, 'msg' => '记录不存在']);
            }
        } catch (\Exception $e) {
            return json(['code' => 1, 'msg' => '获取详情失败：' . $e->getMessage()]);
        }
    }
}