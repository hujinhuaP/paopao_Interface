<?php

namespace app\live\controller\video;

use think\Exception;
use app\common\controller\Backend;

use app\live\model\live\VideoMusic;

/**
 * 音乐列表
 *
 * @Authors yeah_lsj@yeah.net
 */
class Music extends Backend
{
	/**
	 * index 列表
	 */
	public function index()
	{
		if ($this->request->isAjax())
        {
            $sKeyword = $this->request->param('search');
            $nOffset  = $this->request->param('offset');
            $nLimit   = $this->request->param('limit');
            $sSort    = $this->request->param('sort');
            $sOrder   = $this->request->param('order');
            $aFilter  = json_decode($this->request->param('filter'), 1);
            $aOp      = json_decode($this->request->param('op'), 1);
            $oSelectQuery = VideoMusic::where('1=1');
            $oTotalQuery  = VideoMusic::where('1=1');

            if ($sKeyword) {
                if (is_numeric($sKeyword)) {
                    $oSelectQuery->where('id', 'LIKE', '%'.$sKeyword.'%');
                    $oTotalQuery->where('id', 'LIKE', '%'.$sKeyword.'%');
                } else {
                    $oSelectQuery->where('name', 'LIKE', '%'.$sKeyword.'%');
                    $oTotalQuery->where('name', 'LIKE', '%'.$sKeyword.'%');
                }
            }
            if ($aFilter) {
                foreach ($aFilter as $key => $value) {
                    if (stripos($aOp[$key], 'LIKE') !== FALSE) {
                        $value = str_replace(['LIKE ', '...'], ['', $value], $aOp[$key]);
                        $aOp[$key] = 'LIKE';
                    }
                    switch ($key) {
                        default:
                            $oSelectQuery->where($key, $aOp[$key], $value);
                            $oTotalQuery->where($key, $aOp[$key], $value);
                            break;
                    }
                }
            }

            if ($nLimit) {
                $oSelectQuery->limit($nOffset, $nLimit);
            }

            $total = $oTotalQuery->count();
            $list  = $oSelectQuery->order($sSort.' '.$sOrder)->select();
            $result = array("total" => $total, "rows" => $list);
            return json($result);
        }

        return $this->view->fetch();
	}

	/**
	 * add 添加
	 */
	public function add()
	{
        $upload_url = config('upload.uploadurl_mp3');
        if ($this->request->isPost())
        {
            $params = $this->request->param('row/a');
            $row = new VideoMusic();
            $row->name = $params['name'];
            $row->duration = $params['duration'];
            $row->author = $params['author'];
            $row->cover = $params['cover'];
            $row->url = $params['down_url'];
            $row->validate(
            	[
                    'name' => 'require',
                    'duration' => 'require',
                    'author' => 'require',
                    'cover' => 'require',
                    'url' => 'require',
            	],
            	[
                    'name.require'      =>  __('Parameter %s can not be empty', ['name']),
                    'duration.require'  =>  __('Parameter %s can not be empty', ['duration']),
                    'author.require' =>  __('Parameter %s can not be empty', ['author']),
                    'url.require' =>  __('Parameter %s can not be empty', ['url']),
                    'cover.require' =>  __('Parameter %s can not be empty', ['cover']),
            	]
            );

            if ($row->save($row->getData()) === false) {
                $this->error($row->getError());
            } else {
                $this->success();
            }
        }
        $this->view->assign("upload_url", $upload_url);
        return $this->view->fetch();
	}

	/**
	 * edit 编辑
	 * 
	 * @param  string $ids
	 */
	public function edit($ids='')
	{

        $row = VideoMusic::get($ids);

		if ($this->request->isPost())
        {
            $params = $this->request->param('row/a');


            $row->name = $params['name'];
            $row->duration = $params['duration'];
            $row->author = $params['author'];
            $row->cover = $params['cover'];
            $row->url = $params['down_url'];

            $row->validate(
                [
                    'name' => 'require',
                    'duration' => 'require',
                    'author' => 'require',
                    'cover' => 'require',
                    'url' => 'require',
                ],
                [
                    'name.require'      =>  __('Parameter %s can not be empty', ['name']),
                    'duration.require'  =>  __('Parameter %s can not be empty', ['duration']),
                    'author.require' =>  __('Parameter %s can not be empty', ['author']),
                    'url.require' =>  __('Parameter %s can not be empty', ['url']),
                    'cover.require' =>  __('Parameter %s can not be empty', ['cover']),
                ]
            );

            if ($row->save($row->getData()) === false) {
                $this->error($row->getError());
            } else {
                $this->success();
            }
        }

        $this->view->assign("row", $row);
        $upload_url = config('upload.uploadurl_mp3');
        $this->view->assign("upload_url", $upload_url);
        return $this->view->fetch();
	}

	/**
	 * delete 删除
	 * 
	 * @param  string $ids
	 */
	public function delete($ids='')
	{
		VideoMusic::where('id', 'in', $ids)->delete();
        $this->success();
	}

	/**
	 * multi 批量操作
	 * 
	 * @param  string $ids
	 */
	public function multi($ids='')
	{
		# code...
	}


}