<?php

namespace app\live\controller\voiceroom;

use app\common\controller\Backend;
use app\live\model\live\Kv;

/**
 * 砸蛋奖品配置
 */
class Egggoods extends Backend
{
    public function _initialize()
    {
        parent::_initialize();
        $this->model = model('live.egg_goods');
        $this->view->assign("eggGoodsCategoryList", $this->model->getEggGoodsCategoryList());
        $this->view->assign("eggGoodsNoticeFlgList", $this->model->getEggGoodsNoticeFlgList());
    }


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
        $defaultRow = Kv::many([
            Kv::CONSOLATION_CATEGORY,
            Kv::CONSOLATION_VALUE,
            Kv::CONSOLATION_NAME,
            Kv::CONSOLATION_IMAGE,
            Kv::EGGS_HAMMER_COIN,
        ]);
        $this->view->assign('defaultRow', $defaultRow);
        return $this->view->fetch();
    }


    /**
     * delete 删除
     *
     * @param string $ids
     */
    public function delete( $ids = '' )
    {
    }

    /**
     * multi 批量操作
     *
     * @param string $ids
     */
    public function multi( $ids = '' )
    {
        # code...
    }

    /**
     * sort 排序
     */
    public function sort()
    {
    }

    /**
     * 默认数据设置
     */
    public function defaultset()
    {
        if ( $this->request->isPost() ) {
            $params = $this->request->param('row/a');

            $defaultRow = Kv::many([
                Kv::CONSOLATION_CATEGORY,
                Kv::CONSOLATION_VALUE,
                Kv::CONSOLATION_NAME,
                Kv::CONSOLATION_IMAGE,
                Kv::EGGS_HAMMER_COIN,
            ]);


            $model = new Kv();
            $model->startTrans();
            if ( $defaultRow[ Kv::CONSOLATION_CATEGORY ] != $params[ Kv::CONSOLATION_CATEGORY ] ) {
                $kv     = Kv::get([ 'kv_key' => Kv::CONSOLATION_CATEGORY ]);
                $result = $kv->save([ 'kv_value' => $params[ Kv::CONSOLATION_CATEGORY ] ]);
                if ( $result === FALSE ) {
                    $model->rollback();
                    $this->error('修改失败');
                }
            }

            if ( $defaultRow[ Kv::CONSOLATION_VALUE ] != $params[ Kv::CONSOLATION_VALUE ] ) {
                $kv     = Kv::get([ 'kv_key' => Kv::CONSOLATION_VALUE ]);
                $result = $kv->save([ 'kv_value' => $params[ Kv::CONSOLATION_VALUE ] ]);
                if ( $result === FALSE ) {
                    $model->rollback();
                    $this->error('修改失败');
                }
            }

            if ( $defaultRow[ Kv::CONSOLATION_NAME ] != $params[ Kv::CONSOLATION_NAME ] ) {
                $kv     = Kv::get([ 'kv_key' => Kv::CONSOLATION_CATEGORY ]);
                $result = $kv->save([ 'kv_value' => $params[ Kv::CONSOLATION_CATEGORY ] ]);
                if ( $result === FALSE ) {
                    $model->rollback();
                    $this->error('修改失败');
                }
            }

            if ( $defaultRow[ Kv::CONSOLATION_IMAGE ] != $params[ Kv::CONSOLATION_IMAGE ] ) {
                $kv     = Kv::get([ 'kv_key' => Kv::CONSOLATION_IMAGE ]);
                $result = $kv->save([ 'kv_value' => $params[ Kv::CONSOLATION_IMAGE ] ]);
                if ( $result === FALSE ) {
                    $model->rollback();
                    $this->error('修改失败');
                }
            }

            if ( $defaultRow[ Kv::EGGS_HAMMER_COIN ] != $params[ Kv::EGGS_HAMMER_COIN ] ) {
                $kv     = Kv::get([ 'kv_key' => Kv::EGGS_HAMMER_COIN ]);
                $result = $kv->save([ 'kv_value' => $params[ Kv::EGGS_HAMMER_COIN ] ]);
                if ( $result === FALSE ) {
                    $model->rollback();
                    $this->error('修改失败');
                }
            }

            $model->commit();

            $this->success();
        }
    }


}