<?php

namespace app\live\controller\user;

use app\live\model\live\UserCertificationSource;
use app\common\controller\Backend;

use app\live\model\live\User;
use app\live\model\live\UserCertification;

/**
 * Photographer 摄影师认证
 *
 */
class Photographer extends Backend
{
    use \app\live\library\traits\SystemMessageService;


    public function _initialize()
    {
        parent::_initialize();
        $this->model = model('live.user_certification');
    }

    /**
     * index 列表
     */
    public function index()
    {
        if ( $this->request->isAjax() ) {
            list($where, $sort, $order, $offset, $limit) = $this->buildparams('user_id', TRUE);
            $total  = $this->model->where($where)->where('user_certification_type = "photographer"')->with('User')->order($sort, $order)->count();
            $list   = $this->model->where($where)->where('user_certification_type = "photographer"')->with('User')->order($sort, $order)->limit($offset, $limit)->select();
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
    public function detail($ids = '')
    {
        $row = UserCertification::where('user_id', $ids)
            ->find();
        if ( !$row )
            $this->error(__('No Results were found'));

        $videoUrl    = '';
        $videoSource = new UserCertificationSource();
        if ( $row->user_certification_video_status != 'NOT' ) {
            $videoSource = UserCertificationSource::get([
                'certification_id' => $row->user_certification_id,
                'type'             => 'first',
                'source_type'      => 'video',
            ]);
            $videoUrl    = $videoSource->source_url;
        }
        $imageSource = new UserCertificationSource();
        if ( $row->user_certification_image_status != 'NOT' ) {
            $imageSource = UserCertificationSource::all([
                'certification_id' => $row->user_certification_id,
                'type'             => 'first',
                'source_type'      => 'img',
            ]);
        }
        $this->view->assign("videoUrl", $videoUrl);
        $this->view->assign("videoSource", $videoSource);
        $this->view->assign("imageSource", $imageSource);
        $this->view->assign("row", $row);
        return $this->view->fetch();
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
    public function edit($ids = '')
    {
        $row = UserCertification::where('user_id', $ids)
            ->find();
        if ( !$row )
            $this->error(__('No Results were found'));

        $row->startTrans();

        if ( $this->request->isPost() ) {
            $params = $this->request->param('row/a');

            // 已审核
            if ( $row->user_certification_status != 'C' ) {
                $this->error(__('You have no permission'));
                // 拒绝
            } else if ( $params['user_certification_status'] == 'N' ) {
                $aValidate                    = [
                    'user_certification_status' => 'require',
                    'user_certification_result' => 'require',
                ];
                $aValidateMessage             = [
                    'user_certification_status.require' => __('Parameter %s can not be empty', [ 'user_certification_status' ]),
                    'user_certification_result.require' => __('Parameter %s can not be empty', __('Check result')),
                ];
                $oUser                        = User::get($ids);
                $oUser->user_is_certification = 'R';
                if ( $oUser->save() === FALSE ) {
                    $row->rollback();
                    $this->error($oUser->getError());
                }
            } else if ( $params['user_certification_status'] == 'D' ) {
                $aValidate                    = [
                    'user_certification_status' => 'require',
                ];
                $aValidateMessage             = [
                    'user_certification_status.require' => __('Parameter %s can not be empty', [ 'user_certification_status' ]),
                ];
                $oUser                        = User::get($ids);
                $oUser->user_is_certification = 'D';
                if ( $oUser->save() === FALSE ) {
                    $row->rollback();
                    $this->error($oUser->getError());
                }
            } else {
                // 通过
                $aValidate        = [
                    'user_certification_status' => 'require',
                ];
                $aValidateMessage = [
                    'user_certification_status.require' => __('Parameter %s can not be empty', [ 'user_certification_status' ]),
                ];

            }
            $row->user_certification_status = $params['user_certification_status'];
            $row->user_certification_result = $params['user_certification_result'];
            $row->validate($aValidate, $aValidateMessage);

            if ( $row->save($row->getData()) === FALSE ) {
                $row->rollback();
                $this->error($row->getError());
            }

            $this->_becomePhotographer($row);

            $row->commit();

            $this->sendCertificationMsg($ids, $row,'photographer');
            $this->success();
        }

        $videoUrl    = '';
        $videoSource = new UserCertificationSource();
        if ( $row->user_certification_video_status != 'NOT' ) {
            $videoSource = UserCertificationSource::get([
                'certification_id' => $row->user_certification_id,
                'type'             => 'first',
                'source_type'      => 'video',
            ]);
            $videoUrl    = $videoSource->source_url;
        }
        $imageSource = new UserCertificationSource();
        if ( $row->user_certification_image_status != 'NOT' ) {
            $imageSource = UserCertificationSource::all([
                'certification_id' => $row->user_certification_id,
                'type'             => 'first',
                'source_type'      => 'img',
            ]);
        }
        $this->view->assign("videoUrl", $videoUrl);
        $this->view->assign("videoSource", $videoSource);
        $this->view->assign("imageSource", $imageSource);
        $this->view->assign("row", $row);
        return $this->view->fetch();
    }

    /**
     * 提交视频审核结果
     */
    public function video()
    {
        $params                = $this->request->param('row/a');
        $user_certification_id = $params['user_certification_id'];
        $row                   = UserCertification::get($user_certification_id);

        if ( !$row )
            $this->error(__('No Results were found'));
        if ( $row->user_certification_video_status != 'C' ) {
            $this->error(__('You have no permission'));
        }
        $row->startTrans();
        $result = $params['video_certification_result'];
        $status = $params['user_certification_video_status'];
        // 修改认证状态
        $row->user_certification_video_result = $result;
        $row->user_certification_video_status = $status;
        if ( $row->save($row->getData()) === FALSE ) {
            $row->rollback();
            $this->error($row->getError());
        }
        $videoSource = UserCertificationSource::get([
            'certification_id' => $row->user_certification_id,
            'type'             => 'first',
            'source_type'      => 'video',
        ]);
        if ( empty($videoSource) ) {
            $row->rollback();
            $this->error(__('视频记录不存在'));
        }

        // 视频记录状态修改
        $videoSource->result = $result;
        $videoSource->status = $status;
        if ( $videoSource->save() === FALSE ) {
            $row->rollback();
            $this->error($videoSource->getError());
        }

        $this->_becomePhotographer($row, $videoSource->source_url);
        $this->sendCertificationMsg($row['user_id'], $row,'photographer');

        $row->commit();

        $this->success();


    }

    /**
     * 图片审核
     */
    public function image()
    {
        $params                = $this->request->param('row/a');
        $user_certification_id = $this->request->param('user_certification_id');
        $row                   = UserCertification::get($user_certification_id);
        if ( !$row )
            $this->error(__('No Results were found'));
        if ( $row->user_certification_image_status != 'C' ) {
            $this->error(__('You have no permission'));
        }
        $isPass   = TRUE;
        $isForbid = FALSE;
        $cover    = '';
        $result   = '';
        foreach ( $params as $key => $saveItem ) {
            if ( $saveItem['user_certification_image_status'] == 'N' ) {
                $isPass = FALSE;
                if ( empty($saveItem['user_image_certification_result']) ) {
                    $this->error('拒绝的图片需要添加描述理由');
                }
                $imgP   = $key + 1;
                $result .= '第' . $imgP . ' 张:' . $saveItem['user_image_certification_result'] . ';';
            } else if ( $saveItem['user_certification_image_status'] == 'D' ) {
                $isForbid = TRUE;
            }
        }
        $row->startTrans();
        $row->user_certification_image_status = 'N';
        $row->user_certification_image_result = $result;
        if ( $isPass ) {
            $row->user_certification_image_status = 'Y';
        }
        if ( $isForbid ) {
            $row->user_certification_image_status = 'D';
        }
        if ( $row->save() === FALSE ) {
            $row->rollback();
            $this->error($row->getError());
        }
        $imagesArr = [];
        foreach ( $params as $saveItem ) {
            $oUserCertificationSource = UserCertificationSource::get($saveItem['id']);
            if ( !$cover ) {
                $cover = $oUserCertificationSource->source_url;
            }
            $imagesArr[]                      = $oUserCertificationSource->source_url;
            $oUserCertificationSource->status = $saveItem['user_certification_image_status'];
            $oUserCertificationSource->result = $saveItem['user_image_certification_result'];
            if ( $oUserCertificationSource->save() === FALSE ) {
                $row->rollback();
                $this->error('更新错误');
            }
        }

        $this->_becomePhotographer($row, '', $cover, implode(',', $imagesArr));
        $this->sendCertificationMsg($row['user_id'], $row,'photographer');

        $row->commit();
        $this->success();
    }

    private function _becomePhotographer($row, $video = '', $cover = '', $images = '')
    {

        //如果三个审核状态都通过则成为主播
        if ( $row->user_certification_video_status == 'Y' && $row->user_certification_status == 'Y' && $row->user_certification_image_status == 'Y' ) {
            $user_id = $row->user_id;
            // 修改用户等级经验
            $oUser                        = User::get($user_id);
            $oUser->user_is_photographer  = 'Y';
            $oUser->user_is_certification = 'Y';

            if ( $oUser->save() === FALSE ) {
                $row->rollback();
                $this->error($oUser->getError());
            }

            if ( empty($video) ) {
                //查到video
                $videoSource = UserCertificationSource::get([
                    'certification_id' => $row->user_certification_id,
                    'type'             => 'first',
                    'source_type'      => 'video',
                ]);
                $video       = $videoSource->source_url;
            }
            if ( empty($cover) ) {
                $imageSource = UserCertificationSource::get([
                    'certification_id' => $row->user_certification_id,
                    'type'             => 'first',
                    'source_type'      => 'img',
                    'sort_num'         => 0,
                ]);
                $cover       = $imageSource->source_url;
            }
            if ( empty($images) ) {
                $imageSource = UserCertificationSource::where([
                    'certification_id' => $row->user_certification_id,
                    'type'             => 'first',
                    'source_type'      => 'img',
                ])->column('source_url');
                $images      = implode(',', $imageSource);
            }

            // 添加主播
            $oPhotographer                         = new \app\live\model\live\Photographer();
            $oPhotographer->user_id                = $user_id;
            $oPhotographer->photographer_video     = $video;
            $oPhotographer->photographer_check_img = $images;
            if ( $oPhotographer->save() === FALSE ) {
                $row->rollback();
                $this->error($oPhotographer->getError());
            }

        }
    }

    /**
     * delete 删除
     *
     * @param  string $ids
     */
    public function delete($ids = '')
    {

    }

    /**
     * multi 批量操作
     * @param  string $ids
     */
    public function multi($ids = '')
    {

    }
}