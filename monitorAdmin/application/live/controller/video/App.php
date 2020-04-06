<?php

namespace app\live\controller\video;
use think\Exception;
use app\common\controller\Backend;
use app\live\model\live\UserVideo;

/**
 * 小视频管理
 *
 */
class App extends Backend
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

            $oSelectQuery = UserVideo::where("uv.watch_type='free'");
            $oTotalQuery  = UserVideo::where("uv.watch_type='free'");

            if ($sKeyword) {
                if (is_numeric($sKeyword)) {
                    $oSelectQuery->where('uv.user_id', $sKeyword);
                    $oTotalQuery->where('uv.user_id', $sKeyword);
                }
            }

            if ($aFilter) {
                foreach ($aFilter as $key => $value) {
                    if (stripos($aOp[$key], 'LIKE') !== FALSE) {
                        $value = str_replace(['LIKE ', '...'], ['', $value], $aOp[$key]);
                        $aOp[$key] = 'LIKE';
                    }
                    switch ($key) {
                        case 'id':
                            $oSelectQuery->where('uv.id', $aOp[$key], $value);
                            $oTotalQuery->where('uv.id', $aOp[$key], $value);
                            break;
                        case 'user_id':
                            $oSelectQuery->where('uv.user_id', $aOp[$key], $value);
                            $oTotalQuery->where('uv.user_id', $aOp[$key], $value);
                            break;
                        case 'create_time':
                            $oSelectQuery->where('uv.create_time', $aOp[$key], $value);
                            $oTotalQuery->where('uv.create_time', $aOp[$key], $value);
                            break;
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


            $total = $oTotalQuery->alias('uv')->join("user u","u.user_id = uv.user_id")->count();
            $list  = $oSelectQuery->alias('uv')
                ->join("user u","u.user_id = uv.user_id")
                ->join('video_category vc','uv.type=vc.id','LEFT')
                ->order('uv. '.$sSort.' '.$sOrder)
                ->field("uv.*,vc.name as category_name,u.user_nickname,u.user_avatar")
                ->select();
            $result = array("total" => $total, "rows" => $list);
            return json($result);
        }

        return $this->view->fetch();
	}

	/**
	 * delete 删除
	 * 
	 * @param  string $ids
	 */
	public function delete($ids='')
	{
		UserVideo::where('id', 'in', $ids)->delete();
        $this->success();
	}

    public function hot($ids=''){
        $row = UserVideo::where('id', $ids)->find();

        if (!$row)
            $this->error(__('No Results were found'));

        if ($this->request->isPost())
        {
            $params = $this->request->post('params');
            switch ($params) {
                case 'N':
                    $row->hot_time = 0;
                    break;
                case 'Y':
                default:
                    $row->hot_time = time();
                    break;
            }
            if ($row->save() === false) {
                $this->error($row->getError());
            } else {
                $this->success();
            }
        }
    }

    public function play($ids = ''){
        $row = UserVideo::where('id', $ids)->find();
        $this->view->assign('url',$row->play_url);
        $this->view->assign('cover',$row->cover);
        return $this->view->fetch();
    }

    public function show($ids=''){
        $row = UserVideo::where('id', $ids)->find();

        if (!$row)
            $this->error(__('No Results were found'));

        if ($this->request->isPost())
        {
            $params = $this->request->post('params');
            switch ($params) {
                case 'N':
                    $row->is_show = 0;
                    break;
                case 'Y':
                default:
                    $row->is_show = 1;
                    break;
            }
            if ($row->save() === false) {
                $this->error($row->getError());
            } else {
                $this->success();
            }
        }
    }
}