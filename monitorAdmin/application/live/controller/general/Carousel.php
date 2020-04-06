<?php

namespace app\live\controller\general;

use think\Exception;
use app\common\controller\Backend;

use app\live\model\live\Carousel as CarouselModel;
use app\live\model\live\CarouselCategory;

/**
 * 礼物管理
 *
 * @Authors yeah_lsj@yeah.net
 */
class Carousel extends Backend
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

            $oSelectQuery = CarouselModel::where('1=1');
            $oTotalQuery  = CarouselModel::where('1=1');

            if ($sKeyword) {
                $oSelectQuery->where('carousel_id', 'LIKE', '%'.$sKeyword.'%');
                $oTotalQuery->where('carousel_id', 'LIKE', '%'.$sKeyword.'%');
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
            $list  = $oSelectQuery->order('carousel_sort '.$sOrder)->select();
            $result = array("total" => $total, "rows" => $list);
            return json($result);
        }

        $oCarouselCategory = CarouselCategory::all();
        $this->view->assign("row_category", $oCarouselCategory);
        return $this->view->fetch();
    }

    /**
     * detail 详情
     *
     * @param  string $ids
     */
    public function detail($ids='')
    {
        $row = CarouselModel::get($ids);

        if (!$row)
            $this->error(__('No Results were found'));

        $oCarouselCategory = CarouselCategory::all();
        $this->view->assign("row_category", $oCarouselCategory);
        $this->view->assign("row", $row);
        return $this->view->fetch();
    }

    /**
     * add 添加
     */
    public function add()
    {
        $oCarouselCategory = CarouselCategory::all();

        if ($this->request->isPost())
        {
            $params = $this->request->param('row/a');

            $row = new CarouselModel();

            $row->carousel_url         = $params['carousel_url'];
            $row->carousel_category_id = $params['carousel_category_id'];
            $row->carousel_href        = $params['carousel_href'];
            $row->carousel_sort        = $params['carousel_sort'];

            $row->validate(
                [
                    'carousel_url'         => 'require',
                    'carousel_category_id' => 'require',
                    'carousel_sort'        => 'require',
                ],
                [
                    'carousel_url.require'         =>  __('Parameter %s can not be empty', ['carousel_url']),
                    'carousel_category_id.require' =>  __('Parameter %s can not be empty', ['carousel_category_id']),
                    'carousel_sort.require'        =>  __('Parameter %s can not be empty', ['carousel_sort']),
                ]
            );

            if ($row->save($row->getData()) === false) {
                $this->error($row->getError());
            } else {
                $this->success();
            }
        }

        $this->view->assign("row_category", $oCarouselCategory);
        return $this->view->fetch();
    }

    /**
     * edit 编辑
     *
     * @param  string $ids
     */
    public function edit($ids='')
    {
        $row = CarouselModel::get($ids);
        if (!$row)
            $this->error(__('No Results were found'));

        $oCarouselCategory = CarouselCategory::all();

        if ($this->request->isPost())
        {
            $params = $this->request->param('row/a');

            $row->carousel_url         = $params['carousel_url'];
            $row->carousel_category_id = $params['carousel_category_id'];
            $row->carousel_href        = $params['carousel_href'];
            $row->carousel_sort        = $params['carousel_sort'];

            $row->validate(
                [
                    'carousel_url'         => 'require',
                    'carousel_category_id' => 'require',
                    'carousel_sort'        => 'require',
                ],
                [
                    'carousel_url.require'         =>  __('Parameter %s can not be empty', ['carousel_url']),
                    'carousel_category_id.require' =>  __('Parameter %s can not be empty', ['carousel_category_id']),
                    'carousel_sort.require'        =>  __('Parameter %s can not be empty', ['carousel_sort']),
                ]
            );

            if ($row->save($row->getData()) === false) {
                $this->error($row->getError());
            } else {
                $this->success();
            }
        }

        $this->view->assign("row", $row);
        $this->view->assign("row_category", $oCarouselCategory);
        return $this->view->fetch();
    }

    /**
     * delete 删除
     *
     * @param  string $ids
     */
    public function delete($ids='')
    {
        CarouselModel::where('carousel_id', 'in', $ids)->delete();
        $this->success();
    }

    /**
     * multi 批量操作
     * @param  string $ids
     */
    public function multi($ids='')
    {

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
                CarouselModel::where('carousel_id', $id)->update(['carousel_sort' => $k + 1, 'carousel_update_time'=>time()]);
            }
            $this->success();
        }
        $this->error();
    }

    /**
     * status 修改状态
     *
     * @param  string $ids
     */
    public function status($ids='')
    {
        $row = CarouselModel::get($ids);

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
}