<?php

namespace app\live\controller\user;

use app\live\model\live\AnchorImage;
use app\live\model\live\ShortPosts;
use app\live\model\live\UserCertificationSource;
use app\live\model\live\Group;
use think\Exception;
use app\common\controller\Backend;

use app\live\model\live\User;
use app\live\model\live\Anchor;
use app\live\model\live\UserLevel;
use app\live\model\live\AnchorLevel;
use app\live\model\live\UserCertification;
use app\live\library\Redis;

/**
 * 实名认证
 *
 */
class Certification extends Backend
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
            $whereStr = 'user_certification.group_certification_status = "Y" AND user_certification_type = "anchor"';
            list($where, $sort, $order, $offset, $limit) = $this->buildparams('user_id', TRUE);
            $total  = $this->model->where($where)->where($whereStr)->with('User')->order($sort, $order)->count();
            $list   = $this->model->where($where)->where($whereStr)->with('User')->order($sort, $order)->limit($offset, $limit)->select();
            $result = [
                "total" => $total,
                "rows"  => $list
            ];
            return json($result);
        }
        $this->view->assign('groups', $group = Group::all());
        return $this->view->fetch();
    }

    /**
     * detail 详情
     *
     * @param string $ids
     */
    public function detail( $ids = '' )
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
     * @param string $ids
     */
    public function edit( $ids = '' )
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

            $this->_becomeAnchor($row);

            $row->commit();

            $this->sendCertificationMsg($ids, $row);
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

        $this->_becomeAnchor($row, $videoSource->source_url);
        $this->sendCertificationMsg($row['user_id'], $row);

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

        $this->_becomeAnchor($row, '', $cover, implode(',', $imagesArr));
        $this->sendCertificationMsg($row['user_id'], $row);

        $row->commit();
        $this->success();
    }

    private function _becomeAnchor( $row, $video = '', $cover = '', $images = '' )
    {
        $oUser = User::get($row->user_id);
        //如果三个审核状态都通过则成为主播
        if ( $row->user_certification_video_status == 'Y' && $row->user_certification_status == 'Y' && $row->user_certification_image_status == 'Y' ) {
            $user_id = $row->user_id;
            // 修改用户等级经验

            $oUser->user_is_anchor        = 'Y';
            $oUser->user_is_certification = 'Y';

            // 如果没有公会 那么默认添加到我们自己的公会
            if ( !$oUser->user_group_id ) {
                $oUser->user_group_id = 21;
            }
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
            $oAnchor                     = new Anchor();
            $oAnchor->user_id            = $user_id;
            $oAnchor->anchor_video_cover = $cover;
            $oAnchor->anchor_video       = $video;
            $oAnchor->anchor_check_img   = $images;
            $oAnchor->anchor_hot_time    = 1;
            // 积极主播默认开启
            $oAnchor->anchor_is_positive = 'Y';
            if ( $oUser->user_online_status == 'Online' ) {
                $oAnchor->anchor_chat_status = 3;
            } else {
                $oAnchor->anchor_chat_status = 1;
            }
            $oAnchor->anchor_chat_price = 20;
            if ( $oAnchor->save() === FALSE ) {
                $row->rollback();
                $this->error($oAnchor->getError());
            }

            // 将图片存到图集
            $imagesArr = explode(',', $images);
            $saveAll   = [];
            $now       = time();
            foreach ( $imagesArr as $imageItem ) {
                $tmp = [
                    'user_id'      => $user_id,
                    'img_src'      => $imageItem,
                    'position'     => 'examine',
                    'visible_type' => 'normal',
                    'create_time'  => $now,
                    'update_time'  => $now,
                ];
                if ( $imageItem == $cover ) {
                    $tmp['position'] = 'cover';
                }
                $saveAll[] = $tmp;
            }
            $oAnchorImage = new AnchorImage();
            $oAnchorImage->saveAll($saveAll);


            // 删除新主播第100条的缓存
            $oRedis = new Redis();
            $oRedis->delete('new_100_anchor_id');

            // 生成动态
            $wordContentArr = [
                "大家好，我是新来的小姐姐，喜欢我的请多多关注",
                "新人报道，请多关照，今天也需要大家的支持哦",
                "新人报道，我是你理想中的女朋友吗？"
            ];
            $oShortPosts    = new ShortPosts();
            $postSave       = [
                [
                    'short_posts_word'         => $wordContentArr[ rand(0, count($wordContentArr) - 1) ],
                    'short_posts_user_id'      => $user_id,
                    'short_posts_price'        => 0,
                    'short_posts_pay_type'     => ShortPosts::PAY_TYPE_FREE,
                    'short_posts_type'         => 'image',
                    'short_posts_status'       => 'Y',
                    'short_posts_check_remark' => '主播审核',
                    'short_posts_images'       => $images,
                    'short_posts_check_time'   => time(),
                    'short_posts_update_time'  => time(),
                ],
                [
                    'short_posts_word'         => $wordContentArr[ rand(0, count($wordContentArr) - 1) ],
                    'short_posts_user_id'      => $user_id,
                    'short_posts_price'        => 0,
                    'short_posts_pay_type'     => ShortPosts::PAY_TYPE_FREE,
                    'short_posts_type'         => 'video',
                    'short_posts_video'        => $oAnchor->anchor_video,
                    'short_posts_status'       => 'Y',
                    'short_posts_check_remark' => '主播审核',
                    'short_posts_images'       => $imagesArr[0],
                    'short_posts_check_time'   => time(),
                    'short_posts_update_time'  => time(),
                ]
            ];
            if ( $oShortPosts->saveAll($postSave) === FALSE ) {
                $this->error($row->getError());
            }


        } else if ( $row->user_certification_video_status == 'N' && $row->user_certification_status == 'N' && $row->user_certification_image_status == 'N' ) {
            if ( $oUser->user_group_id != 0 ) {
                $row->group_certification_status = 'N';
                $row->save();
            }
        }
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
     * @param string $ids
     */
    public function multi( $ids = '' )
    {

    }
}