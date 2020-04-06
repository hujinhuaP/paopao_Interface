<?php

namespace app\live\controller\anchor;

use app\common\controller\Backend;
use app\live\model\live\AnchorImage;
use app\live\model\live\AnchorSourceCertification;
use app\live\model\live\AnchorSourceCertificationDetail;
use app\live\model\live\Anchor as AnchorModel;

/**
 * 主播图片
 *
 */
class Image extends Backend
{

    use \app\live\library\traits\SystemMessageService;
    public function _initialize()
    {
        parent::_initialize();
        $this->model = model('live.anchor_image');
    }

    /**
     * index 列表
     */
    public function index($ids = '')
    {
        if ( $this->request->isAjax() ) {
            $whereStr = '1=1';
            if($ids){
                $whereStr = 'user_id = '.intval($ids);
            }
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            $total  = $this->model->where($where)->where($whereStr)->order($sort, $order)->count();
            $list   = $this->model->where($where)->where($whereStr)->order($sort, $order)->limit($offset, $limit)->select();
            $result = [
                "total" => $total,
                "rows"  => $list
            ];
            return json($result);
        }
        $this->view->assign('user_id',$ids);
        return $this->view->fetch();
    }

    /**
     * @param string $ids
     */
    public function detail($ids = '')
    {

    }

    /**
     * @param string $ids
     */
    public function add($ids = '')
    {
        if ( $this->request->isPost() ) {
            $params = $this->request->param('row/a');

            $row               = new AnchorImage();
            $row->user_id      =  $params['user_id'];
            $row->img_src      = $params['img_src'];
            $row->position     = 'normal';
            $row->visible_type = 'normal';
            if ( $row->save() === FALSE ) {
                $this->error($row->getError());
            }
            $this->success();
        }
        $this->view->assign('user_id', $ids);
        return $this->view->fetch();
    }


    /**
     * @param string $ids
     */
    public function edit($ids = '')
    {

    }

    /**
     * @param string $ids
     * 设为封面  设为VIP
     */
    public function status($ids = '')
    {
        $row = $this->model::where('id', $ids)->find();
        if ( !$row )
            $this->error(__('No Results were found'));
        if ( $this->request->isPost() ) {
            $params          = $this->request->param('params');
            $params          = explode('=', $params);
            $allow_params = [
                'visible_type','position'
            ];
            if(!in_array($params[0],$allow_params)){
                $this->error('无权修改');
            }
            if($params[0] == 'position'){
                //需要将之前的封面数据修改为普通
                $this->model::where(['user_id' => $row->user_id,'position' => 'cover'])->update(['position' => 'examine']);
                \app\live\model\live\Anchor::where(['user_id' => $row->user_id])->update(['anchor_video_cover' => $row->img_src]);
            }
            $row[$params[0]] = $params[1];
            if ( $row->save() === FALSE ) {
                $this->error($row->getError());
            }
            $this->success();
        }
    }

    public function sort()
    {
        echo 1;die;
    }

    /**
     * 删除
     */
    public function delete($ids = '')
    {
        $row = $this->model::where('id', $ids)->find();
        if ( !$row )
            $this->error(__('No Results were found'));
        if($row->position == 'cover'){
            $this->error(__('封面，不能删除'));
        }
        $src = $row->img_src;
        $oAnchor = AnchorModel::where('user_id',$row->user_id)->find();
        if(!$oAnchor){
            $this->success();
        }
        if($row->position == 'examine'){
            // 删除审核图片
            if($oAnchor->anchor_check_img){
                $anchorImageArr = explode(',',$oAnchor->anchor_check_img);
                $newImage = [];
                foreach($anchorImageArr as $item){
                    if($item != $src){
                        $newImage[] = $item;
                    }
                }
                $newStr = implode(',',$newImage);
                if($newStr != $oAnchor->anchor_check_img){
                    $oAnchor->anchor_check_img = $newStr;
                    $oAnchor->save();
                }
            }
        }else{
            if($oAnchor->anchor_images){
                $anchorImageArr = explode(',',$oAnchor->anchor_images);
                $newImage = [];
                foreach($anchorImageArr as $item){
                    if($item != $src){
                        $newImage[] = $item;
                    }
                }
                $newStr = implode(',',$newImage);
                if($newStr != $oAnchor->anchor_images){
                    $oAnchor->anchor_images = $newStr;
                    $oAnchor->save();
                }
            }
        }
        $this->model::where('id', $ids)->delete();
        $this->success();
    }

}