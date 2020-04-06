<?php

namespace app\live\controller\anchor;

use think\Config;
use think\Exception;
use app\common\controller\Backend;

use app\live\model\live\Kv;
use app\live\model\live\Anchor;
use app\live\model\live\AnchorLevel;

/**
 * 房间管理
 *
 * @Authors yeah_lsj@yeah.net
 */
class Room extends Backend
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
            $aFilter  = json_decode($this->request->param('filter'), 1);
            $aOp      = json_decode($this->request->param('op'), 1);

            $oSelectQuery = Anchor::where('anchor_type = 0');
            $oTotalQuery  = Anchor::where('anchor_type = 0');

            if ($sKeyword) {
                if (is_numeric($sKeyword)) {
                    $oSelectQuery->where('u.user_id', 'LIKE', '%'.$sKeyword.'%');
                    $oTotalQuery->where('u.user_id', 'LIKE', '%'.$sKeyword.'%');
                } else {
                    $oSelectQuery->where('u.user_nickname', 'LIKE', '%'.$sKeyword.'%');
                    $oTotalQuery->where('u.user_nickname', 'LIKE', '%'.$sKeyword.'%');
                }
            }

            if ($aFilter) {
                foreach ($aFilter as $key => $value) {
                    if (stripos($aOp[$key], 'LIKE') !== FALSE) {
                        $value = str_replace(['LIKE ', '...'], ['', $value], $aOp[$key]);
                        $aOp[$key] = 'LIKE';
                    }
                    switch ($key) {
                        case 'user_id':
                        case 'user_nickname':
                            $oSelectQuery->where('u.'.$key, $aOp[$key], $value);
                            $oTotalQuery->where('u.'.$key, $aOp[$key], $value);
                            break;
                        case 'anchor_hot_time':
                            if ($value == 'Y') {
                                $oSelectQuery->where('a.'.$key, '>', 0);
                                $oTotalQuery->where('a.'.$key, '>', 0);
                            } else {
                                $oSelectQuery->where('a.'.$key, '<=', 0);
                                $oTotalQuery->where('a.'.$key, '<=', 0);
                            }
                            break;
                        default:
                            $oSelectQuery->where('a.'.$key, $aOp[$key], $value);
                            $oTotalQuery->where('a.'.$key, $aOp[$key], $value);
                            break;
                    }
                }
            }

            if ($nLimit) {
                $oSelectQuery->limit($nOffset, $nLimit);
            }

            $total = $oTotalQuery->alias('a')
                ->join('user u', 'u.user_id=a.user_id')
                ->count();
            $list  = $oSelectQuery->alias('a')
                ->join('user u', 'u.user_id=a.user_id')
                ->order('a.user_id desc')->select();

            foreach ($list as &$v) {
                $v['anchor_forbid_time'] = $v['anchor_forbid_time'] >= time() ? $v['anchor_forbid_time'] : 0;
            }
            $result = array("total" => $total, "rows" => $list);
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

    }

    /**
     * delete 删除
     *
     * @param  string $ids
     */
    public function delete($ids='')
    {

    }

    /**
     * multi 批量操作
     * @param  string $ids
     */
    public function multi($ids='')
    {

    }

    /**
     * banlive 禁播
     *
     * @param  string $ids
     */
    public function banlive($ids='')
    {
        $row = Anchor::where('user_id', $ids)->find();

        if (!$row)
            $this->error(__('No Results were found'));

        if ($this->request->isPost())
        {
            $params = $this->request->post('params');
            switch ($params) {
                case 'Y':
                    $row->anchor_is_live = 'N';
                    $row->anchor_is_forbid = $params;
                    break;
                case 'N':
                    $row->anchor_is_live = 'N';
                    $row->anchor_forbid_time = 0;
                    $row->anchor_is_forbid = $params;
                    break;
                default:
                    # code...
                    break;
            }
            if ($row->save() === false) {
                $this->error($row->getError());
            } else {
                if ($params == 'Y') {
                    file_get_contents(sprintf('%s/live/room/banlive?%s', Config::get('api_url'), http_build_query([
                        'anchor_user_id' => $ids,
                    ])));
                }

                $this->success();
            }
        }
    }

    /**
     * stoplive 停播
     *
     * @param  string $ids
     */
    public function stoplive($ids='')
    {
        $row = Anchor::where('user_id', $ids)->find();

        if (!$row)
            $this->error(__('No Results were found'));

        if ($this->request->isPost())
        {
            $params = $this->request->post('params');
            switch ($params) {
                case 'N':
                    $row->anchor_forbid_time = 0;
                    break;
                case 'Y':
                default:
                    $row->anchor_forbid_time = time()+Kv::getValue(Kv::KEY_LIVE_STOP_TIME, 3600);
                    $row->anchor_is_live = 'N';
                    break;
            }
            if ($row->save() === false) {
                $this->error($row->getError());
            } else {
                if ($params == 'Y') {
                    file_get_contents(sprintf('%s/live/room/stoplive?%s', Config::get('api_url'), http_build_query([
                        'anchor_user_id' => $ids,
                    ])));
                }
                $this->success();
            }
        }
    }

    /**
     * hotlive 热门
     *
     * @param  string $ids
     */
    public function hotlive($ids='')
    {
        $row = Anchor::where('user_id', $ids)->find();

        if (!$row)
            $this->error(__('No Results were found'));

        if ($this->request->isPost())
        {
            $params = $this->request->post('params');
            switch ($params) {
                case 'N':
                    $row->anchor_hot_time = 0;
                    break;
                case 'Y':
                default:
                    $row->anchor_hot_time = time();
                    break;
            }
            if ($row->save() === false) {
                $this->error($row->getError());
            } else {
                $this->success();
            }
        }
    }

    /**
     * monitor 监控
     */
    public function monitor()
    {
        if ($this->request->isAjax())
        {
            $sKeyword = $this->request->param('search');
            $nOffset  = $this->request->param('offset');
            $nLimit   = $this->request->param('limit');
            $aFilter  = json_decode($this->request->param('filter'), 1);
            $aOp      = json_decode($this->request->param('op'), 1);

            $oSelectQuery = Anchor::where('a.anchor_is_live="Y"');
            $oTotalQuery  = Anchor::where('a.anchor_is_live="Y"');

            if ($sKeyword) {
                if (is_numeric($sKeyword)) {
                    $oSelectQuery->where('u.user_nickname', 'LIKE', '%'.$sKeyword.'%');
                    $oTotalQuery->where('u.user_nickname', 'LIKE', '%'.$sKeyword.'%');
                } else {
                    $oSelectQuery->where('u.user_id', 'LIKE', '%'.$sKeyword.'%');
                    $oTotalQuery->where('u.user_id', 'LIKE', '%'.$sKeyword.'%');
                }
            }

            if ($aFilter) {
                foreach ($aFilter as $key => $value) {
                    if (stripos($aOp[$key], 'LIKE') !== FALSE) {
                        $value = str_replace(['LIKE ', '...'], ['', $value], $aOp[$key]);
                        $aOp[$key] = 'LIKE';
                    }
                    switch ($key) {
                        case 'user_id':
                        case 'user_nickname':
                            $oSelectQuery->where('u.'.$key, $aOp[$key], $value);
                            $oTotalQuery->where('u.'.$key, $aOp[$key], $value);
                            break;
                        case 'anchor_hot_time':
                            if ($value == 'Y') {
                                $oSelectQuery->where('a.'.$key, '>', 0);
                                $oTotalQuery->where('a.'.$key, '>', 0);
                            } else {
                                $oSelectQuery->where('a.'.$key, '<=', 0);
                                $oTotalQuery->where('a.'.$key, '<=', 0);
                            }
                            break;
                        default:
                            $oSelectQuery->where('a.'.$key, $aOp[$key], $value);
                            $oTotalQuery->where('a.'.$key, $aOp[$key], $value);
                            break;
                    }
                }
            }

            if ($nLimit) {
                $oSelectQuery->limit($nOffset, $nLimit);
            }

            $total = $oTotalQuery->alias('a')
                ->join('user u', 'u.user_id=a.user_id')
                ->count();
            $list  = $oSelectQuery->alias('a')
                ->join('user u', 'u.user_id=a.user_id')
                ->order('a.user_id desc')->select();

            foreach ($list as &$v) {
                $v['anchor_forbid_time'] = $v['anchor_forbid_time'] >= time() ? $v['anchor_forbid_time'] : 0;
            }
            $result = array("total" => $total, "rows" => $list);
            return json($result);
        }

        return $this->view->fetch();
    }

    /**
     * live 直播中的房间
     */
    public function live()
    {
        if ($this->request->isAjax())
        {
            $sKeyword = $this->request->param('search');
            $nOffset  = $this->request->param('offset');
            $nLimit   = $this->request->param('limit');
            $aFilter  = json_decode($this->request->param('filter'), 1);
            $aOp      = json_decode($this->request->param('op'), 1);

            $oSelectQuery = Anchor::where('a.anchor_is_live="Y"');
            $oTotalQuery  = Anchor::where('a.anchor_is_live="Y"');

            if ($sKeyword) {
                if (is_numeric($sKeyword)) {
                    $oSelectQuery->where('u.user_nickname', 'LIKE', '%'.$sKeyword.'%');
                    $oTotalQuery->where('u.user_nickname', 'LIKE', '%'.$sKeyword.'%');
                } else {
                    $oSelectQuery->where('u.user_id', 'LIKE', '%'.$sKeyword.'%');
                    $oTotalQuery->where('u.user_id', 'LIKE', '%'.$sKeyword.'%');
                }
            }

            if ($aFilter) {
                foreach ($aFilter as $key => $value) {
                    if (stripos($aOp[$key], 'LIKE') !== FALSE) {
                        $value = str_replace(['LIKE ', '...'], ['', $value], $aOp[$key]);
                        $aOp[$key] = 'LIKE';
                    }
                    switch ($key) {
                        case 'user_id':
                        case 'user_nickname':
                            $oSelectQuery->where('u.'.$key, $aOp[$key], $value);
                            $oTotalQuery->where('u.'.$key, $aOp[$key], $value);
                            break;
                        case 'anchor_hot_time':
                            if ($value == 'Y') {
                                $oSelectQuery->where('a.'.$key, '>', 0);
                                $oTotalQuery->where('a.'.$key, '>', 0);
                            } else {
                                $oSelectQuery->where('a.'.$key, '<=', 0);
                                $oTotalQuery->where('a.'.$key, '<=', 0);
                            }
                            break;
                        default:
                            $oSelectQuery->where('a.'.$key, $aOp[$key], $value);
                            $oTotalQuery->where('a.'.$key, $aOp[$key], $value);
                            break;
                    }
                }
            }

            if ($nLimit) {
                $oSelectQuery->limit($nOffset, $nLimit);
            }

            $total = $oTotalQuery->alias('a')
                ->join('user u', 'u.user_id=a.user_id')
                ->count();
            $list  = $oSelectQuery->alias('a')
                ->join('user u', 'u.user_id=a.user_id')
                ->order('a.user_id desc')->select();

            foreach ($list as &$v) {
                $v['anchor_forbid_time'] = $v['anchor_forbid_time'] >= time() ? $v['anchor_forbid_time'] : 0;
            }
            $result = array("total" => $total, "rows" => $list);
            return json($result);
        }

        return $this->view->fetch();
    }

    /**
     * play 播放视屏
     *
     * @param string $ids
     */
    public function play($ids='')
    {
        $row = Anchor::where('user_id', $ids)->find();
        if (!$row)
            $this->error(__('No Results were found'));

        $this->view->assign("row", $row);
        return $this->view->fetch();
    }
    //设置红人主播
    public function hot($ids=''){
        $row = Anchor::where('user_id', $ids)->find();

        if (!$row)
            $this->error(__('No Results were found'));

        if ($this->request->isPost())
        {
            $params = $this->request->post('params');
            switch ($params) {
                case 'N':
                    $row->anchor_hot_man = 0;
                    // 从红人群删除
                    $flg = file_get_contents(sprintf('%s/im/leaveGroup?%s', Config::get('api_url'), http_build_query([
                        'uid'   => $ids,
                        'gid'   => Kv::getValue(Kv::MATCH_CENTER_ROOM_ID),
                    ])));
                    break;
                case 'Y':
                default:
                    $row->anchor_hot_man = time();
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