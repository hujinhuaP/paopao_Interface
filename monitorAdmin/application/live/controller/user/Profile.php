<?php

namespace app\live\controller\user;

use app\common\controller\Backend;

/**
 * 用户数据选项
 *
 */
class Profile extends Backend
{

    public function _initialize()
    {
        parent::_initialize();
        $this->model = model('live.user_profile_setting');
    }


    /**
     * index 列表
     */
    public function index()
    {
        if ( $this->request->isAjax() ) {
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            $total  = $this->model->where($where)->order($sort, $order)->count();
            $list   = $this->model->where($where)->order($sort, $order)->limit($offset, $limit)->select();
            $result = [
                "total" => $total,
                "rows"  => $list
            ];
            return json($result);
        }
        return $this->view->fetch();
    }

	/**
	 * detail 详情
	 * 
	 * @param  string $ids
	 */
	public function detail($ids='')
	{

	}

	/**
	 * add 添加
	 */
	public function add()
	{
	}

	/**
	 * edit 编辑
	 * 
	 * @param  string $ids
	 */
	public function edit($ids='')
	{
		$row = $this->model::get($ids);

        if (!$row)
            $this->error(__('No Results were found'));

        if ($this->request->isPost())
        {
            
            $params = $this->request->param('row/a');

            $row->profile_select = $params['profile_select'];
            $row->validate(
                [
                    'profile_select' => 'require',
                ],
                [
                    'profile_select.require' =>  __('Parameter %s can not be empty', ['isrobot_talk_content']),
                ]
            );

            if ($row->save($row->getData()) === false) {
                $this->error($row->getError());
            } else {
                $this->success();
            }
        }
        $this->view->assign('row', $row);
        return $this->view->fetch();
	}

	/**
	 * delete 删除
	 * 
	 * @param  string $ids
	 */
	public function delete($ids='')
	{
		if ($ids) {
			IsrobotTalk::where('isrobot_talk_id', 'in', $ids)->delete();
            $this->success();
		}
		$this->error();
	}

	/**
	 * multi 批量操作
	 * @param  string $ids
	 */
	public function multi($ids='')
	{
		# code...
	}
}