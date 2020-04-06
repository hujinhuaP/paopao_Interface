<?php

namespace app\live\controller\anchor;

use app\common\controller\Backend;
use app\live\model\live\AnchorImage;
use app\live\model\live\AnchorSourceCertification;
use app\live\model\live\AnchorSourceCertificationDetail;
use app\live\model\live\Anchor as AnchorModel;

/**
 * 主播资源审核
 *
 */
class Source extends Backend
{

    use \app\live\library\traits\SystemMessageService;
    public function _initialize()
    {
        parent::_initialize();
        $this->model = model('live.anchor_source_certification');
    }

    /**
     * index 列表
     */
    public function index()
    {
        if ( $this->request->isAjax() ) {
            list($where, $sort, $order, $offset, $limit) = $this->buildparams('user_id', TRUE);
            $total  = $this->model->where($where)->with('User')->order($sort, $order)->count();
            $list   = $this->model->where($where)->with('User')->order($sort, $order)->limit($offset, $limit)->select();
            $result = [
                "total" => $total,
                "rows"  => $list
            ];
            return json($result);
        }

        return $this->view->fetch();
    }

    /**
     * @param string $ids
     */
    public function detail($ids = '')
    {
        $row = AnchorSourceCertification::get($ids);
        if ( !$row )
            $this->error(__('No Results were found'));
        if ( $row->auth_type == 'video' ) {
            $detail = AnchorSourceCertificationDetail::get([ 'certification_id' => $row->id ]);
        } else {
            $detail = AnchorSourceCertificationDetail::all([ 'certification_id' => $row->id ]);
        }
        $this->view->assign('detail', $detail);
        $this->view->assign('row', $row);
        return $this->view->fetch();
    }

    /**
     * @param string $ids
     * 审核
     */
    public function edit($ids = '')
    {
        $row = AnchorSourceCertification::get($ids);
        if ( !$row )
            $this->error(__('No Results were found'));
        if ( $row->auth_type == 'video' ) {
            $typeName = '个人相册';
            $detail = AnchorSourceCertificationDetail::get([ 'certification_id' => $row->id ]);
        } else {
            $typeName = '个人宣传视频';
            $detail = AnchorSourceCertificationDetail::all([ 'certification_id' => $row->id ]);
        }

        if ( $this->request->isPost() ) {
            $params = $this->request->param('row/a');
            if ( $row->status != 'C' ) {
                $this->error(__('You have no permission'));
            }
            $row->startTrans();
            $oAnchor                            = AnchorModel::where('user_id', $row->user_id)->find();
            $refuseReason = '';
            if ( $row->auth_type == 'video' ) {
                //视频 将一条详情修改状态
                if ( $params['status'] == 'N' && empty($params['result']) ) {
                    $this->error('拒绝需要添加描述理由');
                }
                $refuseReason = $params['result'];
                $detail->status = $params['status'];
                $detail->result = $params['result'];
                if ( $detail->save($detail->getData()) === FALSE ) {
                    $row->rollback();
                    $this->error($detail->getError());
                }
                $row_status                         = $params['status'];
                if ( $row_status == 'Y' ) {
                    $oAnchor->anchor_video = $detail->source_url;
                }
                $oAnchor->anchor_video_check_status = $row_status;
            } else {
                $row_status = 'Y';
                $cover      = '';
                foreach ( $params as $imagekey => $saveItem ) {
                    if ( $saveItem['status'] == 'N' ) {
                        $row_status = 'N';
                        if ( empty($saveItem['result']) ) {
                            $this->error('拒绝的图片需要添加描述理由');
                        }

                        $refuseReason .= sprintf('第%d张：%s',$imagekey + 1,$saveItem['result']);
                    }
                }

                $imagesArr = [];
                foreach ( $params as $saveItem ) {
                    $oAnchorSourceCertificationDetail = AnchorSourceCertificationDetail::get($saveItem['id']);
                    if(!$cover){
                        $cover = $oAnchorSourceCertificationDetail->source_url;
                    }
                    $imagesArr[] = $oAnchorSourceCertificationDetail->source_url;
                    $oAnchorSourceCertificationDetail->status = $saveItem['status'];
                    $oAnchorSourceCertificationDetail->result = $saveItem['result'];
                    if ( $oAnchorSourceCertificationDetail->save() === FALSE ) {
                        $row->rollback();
                        $this->error('更新错误');
                    }
                }
                $oAnchor                            = AnchorModel::where('user_id', $row->user_id)->find();

                if ( $row_status == 'Y' ) {
                    $oAnchor->anchor_video_cover = $cover;
                    $oAnchor->anchor_check_img = implode(',',$imagesArr);
                }
                $oAnchor->anchor_image_check_status = $row_status;
            }
            if ( $oAnchor->save() === FALSE ) {
                $row->rollback();
                $this->error($oAnchor->getError());
            }

            $row->status = $row_status;

            if ( $row->save() === FALSE ) {
                $row->rollback();
                $this->error($row->getError());
            }

            $msg = '';
            if($row_status == 'Y'){
                $msg = sprintf('亲爱的小姐姐，恭喜您的%s修改审核通过；',$typeName);
                if ( $row->auth_type == 'img' ) {
                    //删除之前的图片 并且添加新记录
                    $sql = "delete from anchor_image where user_id = {$row->user_id} AND position != 'normal'";
                    if($row->execute($sql) === FALSE){
                        $row->rollback();
                        $this->error('删除失败');
                    }

                    $now = time();
                    $imageSave = [];
                    foreach($imagesArr as $item){
                        $tmp = [
                            'user_id' => $row->user_id,
                            'img_src' => $item,
                            'position' => 'examine',
                            'create_time' => $now,
                            'update_time' => $now,
                        ];
                        if($item == $cover){
                            $tmp['position'] = 'cover';
                        }
                        $imageSave[] = $tmp;
                    }
                    $oAnchorImage = new AnchorImage();
                    if($oAnchorImage->saveAll($imageSave) === false){
                        $row->rollback();
                        $this->error($oAnchorImage->getError());
                    }
                }
            }elseif($row_status == 'N'){
                $msg = sprintf('亲爱的小姐姐，您的%s修改审核失败，请重新对应的规则重新完善您的%s；未通过原因：%s',$typeName,$typeName,$refuseReason);
            }
            $row->commit();
            if($msg){
                $this->sendGeneral($row->user_id,$msg,'',TRUE);
            }
            $this->success();
        }
        $this->view->assign('detail', $detail);
        $this->view->assign('row', $row);
        return $this->view->fetch();
    }

}