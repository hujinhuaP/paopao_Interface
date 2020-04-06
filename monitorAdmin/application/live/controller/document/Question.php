<?php

namespace app\live\controller\document;

use app\common\controller\Backend;
use think\Exception;

use app\live\model\live\Question as QuestionModel;
use app\live\model\live\QuestionCategory;

/**
 * 帮助中心
 *
 * @Authors yeah_lsj@yeah.net
 */
class Question extends Backend
{
	/**
	 * index 列表
	 */
	public function index()
	{
        $aQuestionCategory = QuestionCategory::all();

		if ($this->request->isAjax())
        {
            $sKeyword = $this->request->param('search');
            $nOffset  = $this->request->param('offset');
            $nLimit   = $this->request->param('limit');
            $sOrder   = $this->request->param('order');
            $aFilter  = json_decode($this->request->param('filter'), 1);
            $aOp      = json_decode($this->request->param('op'), 1);

            $oSelectQuery = QuestionModel::where('1=1');
            $oTotalQuery  = QuestionModel::where('1=1');

            if ($sKeyword) {
                $oSelectQuery->where('q.question_title', 'LIKE', '%'.$sKeyword.'%');
                $oTotalQuery->where('q.question_title', 'LIKE', '%'.$sKeyword.'%');
            }

            if ($aFilter) {
                foreach ($aFilter as $key => $value) {
                    if (stripos($aOp[$key], 'LIKE') !== FALSE) {
                        $value = str_replace(['LIKE ', '...'], ['', $value], $aOp[$key]);
                        $aOp[$key] = 'LIKE';
                    }
                    switch ($key) {
                        default:
                            $oSelectQuery->where('q.'.$key, $aOp[$key], $value);
                            $oTotalQuery->where('q.'.$key, $aOp[$key], $value);
                            break;
                    }
                }
            }

            if ($nLimit) {
                $oSelectQuery->limit($nOffset, $nLimit);
            }

            $total = $oTotalQuery->alias('q')
                                 ->join('question_category qc', 'q.question_category_id=qc.question_category_id')
                                 ->count();
            $list  = $oSelectQuery->alias('q')
                                  ->join('question_category qc', 'q.question_category_id=qc.question_category_id')
                                  ->order('q.question_sort '.$sOrder)->select();

            foreach ($list as $v) {
            	$v['question_content'] = strip_tags($v['question_content']);
            	$v['question_content'] = mb_strlen($v['question_content']) >= 20 ? (mb_substr($v['question_content'], 0 , 20).' ... ') : $v['question_content'];
            }

            $result = array("total" => $total, "rows" => $list);
            return json($result);
        }
        $this->view->assign('row_category', $aQuestionCategory);
        return $this->view->fetch();
	}

	/**
	 * detail 详情
	 * 
	 * @param  string $ids
	 */
	public function detail($ids='')
	{
		$row = QuestionModel::alias('q')
                            ->join('question_category qc', 'q.question_category_id=qc.question_category_id')
                            ->where('question_id', $ids)
                            ->find();
        if (!$row)
            $this->error(__('No Results were found'));

        $this->view->assign("row", $row);
        return $this->view->fetch();
	}

	/**
	 * add 添加
	 */
	public function add()
	{
        $aQuestionCategory = QuestionCategory::all();

        if ($this->request->isPost()) {
            $params = $this->request->param('row/a');
            $row = new QuestionModel();
            $row->question_category_id = $params['question_category_id'];
            $row->question_content     = $params['question_content'];
            $row->question_title       = $params['question_title'];
            $row->validate(
                [
                    'question_category_id' => 'require',
                    'question_title'       => 'require',
                    'question_content'     => 'require',
                ],
                [
                    'question_category_id.require' =>  __('Parameter %s can not be empty', ['question_category_id']),
                    'question_title.require'       =>  __('Parameter %s can not be empty', ['question_content']),
                    'question_content.require'     =>  __('Parameter %s can not be empty', ['question_content']),
                ]
            );

            if ($row->save($row->getData()) === false) {
                $this->error($row->getError());
            } else {
                $this->success();
            }
        }
        $this->view->assign("row_category", $aQuestionCategory);
        return $this->view->fetch();
	}

	/**
	 * edit 编辑
	 * 
	 * @param  string $ids
	 */
	public function edit($ids='')
	{
        $row = QuestionModel::get($ids);
		$aQuestionCategory = QuestionCategory::all();
        if (!$row)
            $this->error(__('No Results were found'));

        if ($this->request->isPost())
        {
            $params = $this->request->param('row/a');

            $row->question_category_id = $params['question_category_id'];
            $row->question_content     = $params['question_content'];
            $row->question_title       = $params['question_title'];
            $row->validate(
                [
                    'question_category_id' => 'require',
                    'question_title'       => 'require',
                    'question_content'     => 'require',
                ],
                [
                    'question_category_id.require' =>  __('Parameter %s can not be empty', ['question_category_id']),
                    'question_title.require'       =>  __('Parameter %s can not be empty', ['question_content']),
                    'question_content.require'     =>  __('Parameter %s can not be empty', ['question_content']),
                ]
            );

            if ($row->save($row->getData()) === false) {
                $this->error($row->getError());
            } else {
                $this->success();
            }
        }

        $this->view->assign("row", $row);
        $this->view->assign("row_category", $aQuestionCategory);
        return $this->view->fetch();
	}

	/**
	 * delete 删除
	 * 
	 * @param  string $ids
	 */
	public function delete($ids='')
	{
        QuestionModel::where('question_id', 'in', $ids)->delete();
		return $this->success();
	}

    /**
     * multi 批量操作
     * @param  string $ids
     */
    public function multi($ids='')
    {
        
    }

    /**
     * status 修改状态
     * 
     * @param  string $ids 
     */
    public function status($ids='')
    {
        $row = QuestionModel::get($ids);

        if (!$row)
            $this->error(__('No Results were found'));

        if ($this->request->isPost())
        {
            $params = $this->request->param('params');
            $params = explode('=', $params);
            $row[$params[0]] = $params[1];
            if ($row->save() === false) {
                $this->error($row->getError());
            } else {
                $this->success();
            }
        }
    }

    /**
     * sort 排序
     */
    public function sort()
    {
        if ($this->request->isPost()) {
            $ids      = $this->request->post('ids');
            $changeid = $this->request->post("changeid");
            $orderway = $this->request->post("orderway", 'strtolower');
            $orderway = $orderway == 'asc' ? 'ASC' : 'DESC';
            $ids = explode(',', $ids);

            if ($orderway == 'DESC') {
                $ids = array_reverse($ids);
            }

            foreach ($ids as $k=>$id)
            {
                QuestionModel::where('question_id', $id)->update(['question_sort' => $k + 1, 'question_update_time'=>time()]);
            }
            $this->success();
        }
        $this->error();
    }
}