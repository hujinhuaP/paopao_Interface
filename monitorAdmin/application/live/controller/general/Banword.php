<?php

namespace app\live\controller\general;

use think\cache\driver\Redis;
use think\Exception;
use app\common\controller\Backend;

use app\live\model\live\Banword as BanwordModel;

/**
 * 禁止字管理
 *
 * @Authors yeah_lsj@yeah.net
 */
class Banword extends Backend
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
            $sOrder   = $this->request->param('order');
            $aFilter  = json_decode($this->request->param('filter'), 1);
            $aOp      = json_decode($this->request->param('op'), 1);

            $oSelectQuery = BanwordModel::where('1=1');
            $oTotalQuery  = BanwordModel::where('1=1');

            if ($sKeyword) {
                if (is_numeric($sKeyword)) {
                    $oSelectQuery->where('banword_id', 'LIKE', '%'.$sKeyword.'%');
                    $oTotalQuery->where('banword_id', 'LIKE', '%'.$sKeyword.'%');
                } else {
                    $oSelectQuery->where('banword_content', 'LIKE', '%'.$sKeyword.'%');
                    $oTotalQuery->where('banword_content', 'LIKE', '%'.$sKeyword.'%');
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
            $list  = $oSelectQuery->order('banword_id '.$sOrder)->select();
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
        if ($this->request->isPost())
        {
            $params = $this->request->param('row/a');

            $params['banword_content'] = explode(',', str_replace(['，'], [','], $params['banword_content']));

            foreach ($params['banword_content'] as $value) {
                
                if (BanwordModel::where('banword_content', $value)->find()) {
                    continue;
                }

                $row = new BanwordModel();
                $row->banword_content = $value;
                $row->validate(
                    [
                        'banword_content' => 'require',
                    ],
                    [
                        'banword_content.require' =>  __('Parameter %s can not be empty', ['banword_content']),
                    ]
                );

                if ($row->save($row->getData()) === false) {
                    $this->error($row->getError());
                }
            }
            $oRedis = new Redis(\think\Config::get('redis'));
            $oRedis->rm('_PHCRcaches_banword_arr');
            $this->success();
        }

        return $this->view->fetch();
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
        BanwordModel::where('banword_id', 'in', $ids)->delete();
        $oRedis = new Redis(\think\Config::get('redis'));
        $oRedis->rm('_PHCRcaches_banword_arr');
        $this->success();
    }

    /**
     * multi 批量操作
     * @param  string $ids
     */
    public function multi($ids='')
    {

    }
}