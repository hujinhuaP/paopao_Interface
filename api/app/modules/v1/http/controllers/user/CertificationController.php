<?php

/*
 +------------------------------------------------------------------------+
 | Green Live                                                             |
 +------------------------------------------------------------------------+
 | Copyright (c) 2017-2017 Green Live Team (https://www.greenlive.com)    |
 +------------------------------------------------------------------------+
 | 实名认证控制器                                                         |
 +------------------------------------------------------------------------+
 |                                      |
 +------------------------------------------------------------------------+
 */

namespace app\http\controllers\user;


use app\models\Anchor;
use app\models\AnchorSourceCertification;
use app\models\AnchorSourceCertificationDetail;
use app\models\Kv;
use app\models\Photographer;
use app\models\PhotographerSourceCertification;
use app\models\PhotographerSourceCertificationDetail;
use app\models\UserCertificationSource;
use app\models\WechatCertification;
use Exception;

use app\models\User;
use app\helper\ResponseError;
use app\models\UserCertification;
use app\http\controllers\ControllerBase;

/**
 * CertificationController 实名认证控制器
 */
class CertificationController extends ControllerBase
{
    use \app\services\SystemMessageService;

    /**
     * 实名认证状态
     * @apiVersion 1.3.0
     * @apiSampleRequest http://dev.api.sxypaopao.com/v1/user/certification/check
     * @api {get} /user/certification/check 实名认证状态
     * @apiName 实名认证状态-check
     * @apiGroup Certification
     * @apiDescription 实名认证状态
     * @apiParam (正常请求){String} access_token  token值
     * @apiParam (debug){String} debug  debug
     * @apiParam (debug){String} cli_api_key  debug
     * @apiParam (debug){String} uid  用户id
     * @apiSuccess {number} c 返回码
     * @apiSuccess {string} m 返回文字信息
     * @apiSuccess {number} t 服务器当前时间戳
     * @apiSuccess {Object} d 内容
     * @apiSuccess {String} d.user_is_anchor  是否为主播
     * @apiSuccess {String} d.user_is_photographer  是否为摄影师
     * @apiSuccess {String} d.is_submit 废弃
     * @apiSuccess {String} d.is_forbid  是否被禁止认证
     * @apiSuccess {String} d.user_certification_result 实名认证结果
     * @apiSuccess {String="Y","N","C（审核中）","NOT(未提交)"} d.user_certification_status 实名认证状态
     * @apiSuccess {String="Y","N","C（审核中）","NOT(未提交)"} d.user_certification_video_status  视频认证状态
     * @apiSuccess {String="Y","N","C（审核中）","NOT(未提交)"} d.user_certification_image_status  图片认证状态
     * @apiSuccess {String='anchor(主播)','photographer(摄影师)','NOT(未提交)'} d.user_certification_type  认证类型
     * @apiSuccessExample 返回:
     *     HTTP/1.1 200 OK
     *     {
     *         "c": 0,
     *         "d": {
     *             "result": "",
     *             "status": "Y",
     *             "url": "https://lebolive-1255651273.image.myqcloud.com/video/2018/08/08/5e741d06d8f3d37fff6e10d22b2d29ae.mp4"
     *         },
     *         "m": "请求成功",
     *         "t": 1534911421
     *     }
     * @apiError UserNotFound The id of the User was not found.
     * @apiErrorExample Error-Response:
     *     HTTP/1.1 404 Not Found
     *     {
     *       "error": "UserNotFound"
     *     }
     */
    public function checkAction( $nUserId = 0 )
    {
        try {
            $oUserCertification                     = UserCertification::findFirst([
                'user_id=:user_id:',
                'bind' => [
                    'user_id' => $nUserId,
                ]
            ]);
            $oUser                                  = User::findFirst($nUserId);
            $row['user_is_anchor']                  = $oUser->user_is_anchor;
            $row['user_is_photographer']            = $oUser->user_is_photographer;
            $row['is_submit']                       = 'N';
            $row['is_forbid']                       = $oUser->user_is_certification == 'D' ? 'Y' : 'N';
            $row['user_certification_result']       = '';
            $row['user_certification_status']       = 'NOT';
            $row['user_certification_video_status'] = 'NOT';
            $row['user_certification_image_status'] = 'NOT';
            $row['user_certification_type']         = 'NOT';

            if ( !empty($oUserCertification) ) {
                $row['is_submit']                       = 'Y';
                $row['user_certification_result']       = $oUserCertification->user_certification_result;
                $row['user_certification_status']       = $oUserCertification->user_certification_status;
                $row['user_certification_video_status'] = $oUserCertification->user_certification_video_status;
                $row['user_certification_image_status'] = $oUserCertification->user_certification_image_status;
                $row['user_certification_type']         = $oUserCertification->user_certification_type;
            }

        } catch ( Exception $e ) {
            $this->error(
                ResponseError::FAIL,
                sprintf('%s[%s]', ResponseError::getError(ResponseError::FAIL), $e->getMessage())
            );
        }

        $this->success($row);
    }

    /**
     * 视频详情
     * @apiVersion 1.3.0
     * @apiSampleRequest http://dev.api.sxypaopao.com/v1/user/certification/videoDetail
     * @api {get} /user/certification/videoDetail 视频详情
     * @apiName 视频详情-videoDetail
     * @apiGroup Certification
     * @apiDescription 视频详情
     * @apiParam (正常请求){String} access_token  token值
     * @apiParam (debug){String} debug  debug
     * @apiParam (debug){String} cli_api_key  debug
     * @apiParam (debug){String} uid  用户id
     * @apiSuccess {number} c 返回码
     * @apiSuccess {string} m 返回文字信息
     * @apiSuccess {number} t 服务器当前时间戳
     * @apiSuccess {Object} d 内容
     * @apiSuccess {string} d.result 审核内容
     * @apiSuccess {string="Y","N","C（审核中）","NOT(未提交)"} d.status 审核状态
     * @apiSuccess {string} d.url 视频地址
     * @apiSuccessExample 返回:
     *     HTTP/1.1 200 OK
     *     {
     *         "c": 0,
     *         "d": {
     *             "result": "",
     *             "status": "Y",
     *             "url": "https://lebolive-1255651273.image.myqcloud.com/video/2018/08/08/5e741d06d8f3d37fff6e10d22b2d29ae.mp4"
     *         },
     *         "m": "请求成功",
     *         "t": 1534911421
     *     }
     * @apiError UserNotFound The id of the User was not found.
     * @apiErrorExample Error-Response:
     *     HTTP/1.1 404 Not Found
     *     {
     *       "error": "UserNotFound"
     *     }
     */
    public function videoDetailAction( $nUserId = 0 )
    {
        $oUser = User::findFirst($nUserId);
        if ( $oUser->user_is_anchor == 'Y' ) {
            // 视频审核
            $status  = 'Y';
            $result  = '';
            $oAnchor = Anchor::findFirst([
                'user_id = :user_id:',
                'bind' => [
                    'user_id' => $nUserId
                ]
            ]);
            if ( $oAnchor->anchor_video_check_status == 'Y' ) {
                $url = $oAnchor->anchor_video;
            } else {
                // 审核中 或者审核失败
                $oAnchorSourceCertification       = AnchorSourceCertification::findFirst([
                    'user_id=:user_id: AND auth_type = :auth_type:',
                    'bind'  => [
                        'user_id'   => $nUserId,
                        'auth_type' => 'video'
                    ],
                    'order' => 'create_time desc'
                ]);
                $oAnchorSourceCertificationDetail = AnchorSourceCertificationDetail::findFirst([
                    'certification_id = :certification_id:',
                    'bind' => [
                        'certification_id' => $oAnchorSourceCertification->id,
                    ],
                ]);
                $url                              = $oAnchorSourceCertificationDetail->source_url;
                $result                           = $oAnchorSourceCertificationDetail->result;
            }
        } else if ( $oUser->user_is_photographer == 'Y' ) {
            // 摄影师
            $status        = 'Y';
            $result        = '';
            $oPhotographer = Photographer::findFirst([
                'user_id = :user_id:',
                'bind' => [
                    'user_id' => $nUserId
                ]
            ]);
            if ( $oPhotographer->photographer_video_check_status == 'Y' ) {
                $url = $oPhotographer->photographer_video;
            } else {

                // 审核中 或者审核失败
                $oPhotographerSourceCertification       = PhotographerSourceCertification::findFirst([
                    'user_id=:user_id: AND auth_type = :auth_type:',
                    'bind'    => [
                        'user_id'   => $nUserId,
                        'auth_type' => 'video'
                    ],
                    'orderby' => 'create_time desc'
                ]);
                $oPhotographerSourceCertificationDetail = PhotographerSourceCertificationDetail::findFirst([
                    'certification_id = :certification_id:',
                    'bind' => [
                        'certification_id' => $oPhotographerSourceCertification->id,
                    ],
                ]);
                $url                                    = $oPhotographerSourceCertificationDetail->source_url;
                $result                                 = $oPhotographerSourceCertificationDetail->result;
            }
        } else {

            $oUserCertification = UserCertification::findFirst([
                'user_id=:user_id:',
                'bind' => [
                    'user_id' => $nUserId,
                ]
            ]);
            $status             = 'NOT';
            $result             = '';
            $url                = '';
            if ( $oUserCertification ) {
                $status = $oUserCertification->user_certification_video_status;

                $oUserCertificationSource = UserCertificationSource::findFirst([
                    'certification_id = :certification_id: AND type = :type: AND source_type = :source_type:',
                    'bind' => [
                        'certification_id' => $oUserCertification->user_certification_id,
                        'type'             => UserCertificationSource::TYPE_FIRST,
                        'source_type'      => 'video'
                    ]
                ]);
                if ( $oUserCertificationSource ) {
                    $url = $oUserCertificationSource->source_url;
                } else {
                    $status = 'NOT';
                }
            }

        }
        $result = [
            'status' => $status,
            'result' => $result,
            'url'    => $url
        ];
        $this->success($result);
    }


    /**
     * @apiVersion 1.3.0
     * @apiSampleRequest http://dev.api.sxypaopao.com/v1/user/certification/imageDetail
     * @api {get} /user/certification/imageDetail 图片详情
     * @apiName 图片详情-imageDetail
     * @apiGroup Certification
     * @apiDescription 图片详情
     * @apiParam (正常请求){String} access_token  token值
     * @apiParam (debug){String} debug  debug
     * @apiParam (debug){String} cli_api_key  debug
     * @apiParam (debug){String} uid  用户id
     * @apiSuccess {number} c 返回码
     * @apiSuccess {string} m 返回文字信息
     * @apiSuccess {number} t 服务器当前时间戳
     * @apiSuccess {Object} d 内容
     * @apiSuccess {string="Y","N","C（审核中）","NOT(未提交)"} d.status 审核状态
     * @apiSuccess {Object[]} d.detail 详情
     * @apiSuccess {string="Y","N","C（审核中）"} d.detail.status 审核状态
     * @apiSuccess {string} d.detail.result 审核内容
     * @apiSuccess {string} d.detail.url 图片地址
     * @apiSuccessExample 返回:
     *     HTTP/1.1 200 OK
     * {
     *     "c": 0,
     *     "m": "请求成功",
     *     "d": {
     *             "status": "Y",
     *             "detail": [{
     *                 "url": "https://lebolive-1255651273.image.myqcloud.com/image/2018/08/21/1534812775269.png",
     *                 "status": "Y",
     *                 "result": ""
     *             },
     *             {
     *                 "url": "https://lebolive-1255651273.image.myqcloud.com/image/2018/08/20/708e631bb18a8f3a90517d102e6a0414",
     *                 "status": "Y",
     *                 "result": ""
     *             },
     *             {
     *                 "url": "http://thirdwx.qlogo.cn/mmopen/vi_32/l1eG09GX8y2oBbbjiaGdY0c1hPUzbTaF6z3sexRbZAZpWlz3D1F7GDI3U8FFavGYZ0UIWUZIHtHfhFHnq1H9A8A/132",
     *                 "status": "Y",
     *                 "result": ""
     *             },
     *             {
     *                 "url": "https://lebolive-1255651273.image.myqcloud.com/image/2018/08/19/1534688096385.png",
     *                 "status": "Y",
     *                 "result": ""
     *             },
     *             {
     *                 "url": "https://lebolive-1255651273.image.myqcloud.com/image/2018/08/18/66149e5d23fd1b6f4a56a74e174e6f0c",
     *                 "status": "Y",
     *                 "result": ""
     *             }
     *         ]
     *     },
     *     "t": 1534920044
     * }
     * @apiError UserNotFound The id of the User was not found.
     * @apiErrorExample Error-Response:
     *     HTTP/1.1 404 Not Found
     *     {
     *       "error": "UserNotFound"
     *     }
     */
    public function imageDetailAction( $nUserId = 0 )
    {
        $oUser = User::findFirst($nUserId);
        if ( $oUser->user_is_anchor == 'Y' ) {
            // 图片审核
            $status  = 'Y';
            $oAnchor = Anchor::findFirst([
                'user_id = :user_id:',
                'bind' => [
                    'user_id' => $nUserId
                ]
            ]);
            if ( $oAnchor->anchor_image_check_status == 'Y' ) {
                $imagesArr = explode(',', $oAnchor->anchor_check_img);
                $url       = [];
                foreach ( $imagesArr as $item ) {
                    $url[] = [
                        'url'    => $item,
                        'result' => '',
                        'status' => 'Y',
                    ];
                }
            } else {
                // 审核中 或者审核失败
                $oAnchorSourceCertification       = AnchorSourceCertification::findFirst([
                    'user_id=:user_id: AND auth_type = :auth_type:',
                    'bind'  => [
                        'user_id'   => $nUserId,
                        'auth_type' => 'img'
                    ],
                    'order' => 'create_time desc'
                ]);
                $oAnchorSourceCertificationDetail = AnchorSourceCertificationDetail::find([
                    'certification_id = :certification_id:',
                    'bind'    => [
                        'certification_id' => $oAnchorSourceCertification->id,
                    ],
                    'columns' => 'source_url as url,status,result'
                ]);
                $url                              = [];
                if ( $oAnchorSourceCertificationDetail ) {
                    $url = $oAnchorSourceCertificationDetail;
                }
            }
        } else if ( $oUser->user_is_photographer == 'Y' ) {
            // 图片审核
            $status        = 'Y';
            $oPhotographer = Photographer::findFirst([
                'user_id = :user_id:',
                'bind' => [
                    'user_id' => $nUserId
                ]
            ]);
            if ( $oPhotographer->photographer_image_check_status == 'Y' ) {
                $imagesArr = explode(',', $oPhotographer->photographer_check_img);
                $url       = [];
                foreach ( $imagesArr as $item ) {
                    $url[] = [
                        'url'    => $item,
                        'result' => '',
                        'status' => 'Y',
                    ];
                }
            } else {
                // 审核中 或者审核失败
                $oPhotographerSourceCertification       = PhotographerSourceCertification::findFirst([
                    'user_id=:user_id: AND auth_type = :auth_type:',
                    'bind'  => [
                        'user_id'   => $nUserId,
                        'auth_type' => 'img'
                    ],
                    'order' => 'create_time desc'
                ]);
                $oPhotographerSourceCertificationDetail = PhotographerSourceCertificationDetail::find([
                    'certification_id = :certification_id:',
                    'bind'    => [
                        'certification_id' => $oPhotographerSourceCertification->id,
                    ],
                    'columns' => 'source_url as url,status,result'
                ]);
                $url                                    = [];
                if ( $oPhotographerSourceCertificationDetail ) {
                    $url = $oPhotographerSourceCertificationDetail;
                }
            }
        } else {
            $oUserCertification = UserCertification::findFirst([
                'user_id=:user_id:',
                'bind' => [
                    'user_id' => $nUserId,
                ]
            ]);
            $status             = 'NOT';
            $url                = [];
            if ( $oUserCertification ) {
                $status = $oUserCertification->user_certification_image_status;

                $oUserCertificationSource = UserCertificationSource::find([
                    'certification_id = :certification_id: AND type = :type: AND source_type = :source_type:',
                    'bind'    => [
                        'certification_id' => $oUserCertification->user_certification_id,
                        'type'             => UserCertificationSource::TYPE_FIRST,
                        'source_type'      => 'img'
                    ],
                    'columns' => 'source_url as url,status,result'
                ]);
                if ( $oUserCertificationSource ) {
                    $url = $oUserCertificationSource;
                } else {
                    $status = 'NOT';
                }
            }
        }
        $result = [
            'status' => $status,
            'detail' => $url
        ];
        $this->success($result);
    }


    /**
     * addAction 添加实名认证信息
     *
     * @param int $nUserId
     */
    public function addAction( $nUserId = 0 )
    {
        $sRealname          = $this->getParams('realname', 'string', '');
        $sNumber            = $this->getParams('number', 'string', '');
        $sFrontImg          = $this->getParams('front_img', 'string', '');
        $sBackImg           = $this->getParams('back_img', 'string', '');
        $sUserImg           = $this->getParams('user_img', 'string', '');
        $sCertificationType = $this->getParams('certification_type', 'string');
        if ( !$sCertificationType ) {
            $sCertificationType = $this->getParams('user_certification_type', 'string', 'anchor');
        }

        try {

            if ( !in_array($sCertificationType, [
                'anchor',
                'photographer'
            ]) ) {
                throw new Exception(
                    ResponseError::getError(ResponseError::PARAM_ERROR),
                    ResponseError::PARAM_ERROR
                );
            }

            $oUser = User::findFirst($nUserId);

            switch ( $oUser->user_is_certification ) {
                case 'Y':
                    // 已实名认证
                    throw new Exception(
                        ResponseError::getError(ResponseError::USER_IS_CERTIFICATION),
                        ResponseError::USER_IS_CERTIFICATION
                    );
                    break;
                case 'C':
                    // 认证中
                    throw new Exception(
                        ResponseError::getError(ResponseError::USER_CERTIFICATION_CHECK),
                        ResponseError::USER_CERTIFICATION_FAIL
                    );
                    break;
                case 'D':
                    // 禁止认证
                    throw new Exception(
                        ResponseError::getError(ResponseError::USER_CERTIFICATION_FORBID),
                        ResponseError::USER_CERTIFICATION_FORBID
                    );
                    break;
                case 'R':
                case 'N':
                default:
            }


            $oUserCertification = UserCertification::findFirst([
                'user_id=:user_id: order by user_certification_id desc',
                'bind' => [
                    'user_id' => $nUserId,
                ]
            ]);

            if ( empty($oUserCertification) ) {
                $oUserCertification = new UserCertification();
            } else if ( $oUserCertification->user_certification_status == 'C' ) {
                // 正在核对
                throw new Exception(
                    ResponseError::getError(ResponseError::USER_CERTIFICATION_CHECK),
                    ResponseError::USER_CERTIFICATION_FAIL
                );
            }

            $oUser = User::findFirst($nUserId);

            if ( $oUser->user_group_id == 0 ) {
                $oUserCertification->group_certification_status = 'Y';
            }
            $oUserCertification->user_id                   = $nUserId;
            $oUserCertification->user_front_img            = $sFrontImg;
            $oUserCertification->user_back_img             = $sBackImg;
            $oUserCertification->user_img                  = $sUserImg;
            $oUserCertification->user_certification_number = $sNumber;
            $oUserCertification->user_realname             = $sRealname;
            $oUserCertification->user_certification_type   = $sCertificationType;
            $oUserCertification->user_certification_status = 'C';

            if ( $oUserCertification->save() === FALSE ) {
                throw new Exception(implode(',', $oUserCertification->getMessages()));
            }
            $oUser->user_is_certification = 'C';
            $oUser->save();
            // 发送实名认证系统消息
            $this->sendCertificationMsg($oUser->user_id, $oUserCertification);

        } catch ( Exception $e ) {
            $this->error(
                ResponseError::OPERATE_FAILED,
                sprintf('%s[%s]', ResponseError::getError(ResponseError::OPERATE_FAILED), $e->getMessage())
            );
        }

        $this->success();
    }

    /**
     * 视频认证
     * @apiVersion 1.3.0
     * @apiSampleRequest http://dev.api.sxypaopao.com/v1/user/certification/video
     * @api {get} /user/certification/video 视频认证提交
     * @apiName 视频认证-video
     * @apiGroup Certification
     * @apiDescription 视频认证提交
     * @apiParam (正常请求){String} access_token  token值
     * @apiParam (正常请求){String} video  视频地址
     * @apiParam (debug) {String} debug  debug
     * @apiParam (debug) {String} cli_api_key  debug
     * @apiParam (debug) {String} uid  用户id
     * @apiParam (debug){String} video  视频地址
     * @apiSuccess {number} c 返回码
     * @apiSuccess {string} m 返回文字信息
     * @apiSuccess {number} t 服务器当前时间戳
     * @apiSuccess {Object} d 内容
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *         "c": 0,
     *         "d": "",
     *         "m": "请求成功",
     *         "t": 1534911421
     *     }
     * @apiError UserNotFound The id of the User was not found.
     * @apiErrorExample Error-Response:
     *     HTTP/1.1 404 Not Found
     *     {
     *       "error": "UserNotFound"
     *     }
     */
    public function videoAction( $nUserId = 0 )
    {
        $sVideo             = $this->getParams('video', 'string', '');
        $sCertificationType = $this->getParams('certification_type', 'string');
        if ( !$sCertificationType ) {
            $sCertificationType = $this->getParams('user_certification_type', 'string', 'anchor');
        }

        try {

            if ( !in_array($sCertificationType, [
                'anchor',
                'photographer'
            ]) ) {
                throw new Exception(
                    ResponseError::getError(ResponseError::PARAM_ERROR),
                    ResponseError::PARAM_ERROR
                );
            }
            if ( empty($sVideo) ) {
                throw new Exception(
                    sprintf('%s[%s]', ResponseError::getError(ResponseError::PARAM_ERROR), 'video'),
                    ResponseError::USER_IS_CERTIFICATION
                );
            }
            $oUser = User::findFirst($nUserId);

            if ( $sCertificationType == 'anchor' ) {

                if ( $oUser->user_is_anchor == 'Y' ) {
                    //重新审核视频
                    throw new Exception(
                        ResponseError::getError(ResponseError::USER_IS_CERTIFICATION),
                        ResponseError::USER_IS_CERTIFICATION
                    );
                }
            } else {
                if ( $oUser->user_is_photographer == 'Y' ) {
                    //重新审核视频
                    throw new Exception(
                        ResponseError::getError(ResponseError::USER_IS_CERTIFICATION),
                        ResponseError::USER_IS_CERTIFICATION
                    );
                }
            }

            //第一次审核视频
            $oUserCertification = UserCertification::findFirst([
                'user_id=:user_id: order by user_certification_id desc',
                'bind' => [
                    'user_id' => $nUserId,
                ]
            ]);
            if ( empty($oUserCertification) ) {
                $oUserCertification = new UserCertification();
                // 将实名认证 设置为未认证
                $oUserCertification->user_certification_status       = 'NOT';
                $oUserCertification->user_certification_image_status = 'NOT';
                $oUserCertification->user_certification_type         = $sCertificationType;
            } else {
                switch ( $oUserCertification->user_certification_video_status ) {
                    case 'Y':
                        // 已认证
                        throw new Exception(
                            ResponseError::getError(ResponseError::USER_IS_CERTIFICATION),
                            ResponseError::USER_IS_CERTIFICATION
                        );
                        break;
                    case 'C':
                        // 认证中
                        throw new Exception(
                            ResponseError::getError(ResponseError::USER_CERTIFICATION_CHECK),
                            ResponseError::USER_CERTIFICATION_FAIL
                        );
                        break;
                    case 'D':
                        // 禁止认证
                        throw new Exception(
                            ResponseError::getError(ResponseError::USER_CERTIFICATION_FORBID),
                            ResponseError::USER_CERTIFICATION_FORBID
                        );
                        break;
                }
            }
            $connection = $oUserCertification->getWriteConnection();
            $connection->begin();
            $oUserCertification->user_id                         = $nUserId;
            $oUserCertification->user_certification_video_status = 'C';

            if ( $oUserCertification->save() === FALSE ) {
                $connection->rollback();
                throw new Exception(implode(',', $oUserCertification->getMessages()));
            }
            $oUserCertificationSource = UserCertificationSource::findFirst([
                'certification_id = :certification_id: AND type = :type: AND source_type = :source_type:',
                'bind' => [
                    'certification_id' => $oUserCertification->user_certification_id,
                    'type'             => UserCertificationSource::TYPE_FIRST,
                    'source_type'      => 'video'
                ]
            ]);
            if ( !$oUserCertificationSource ) {
                $oUserCertificationSource                   = new UserCertificationSource();
                $oUserCertificationSource->sort_num         = 1;
                $oUserCertificationSource->certification_id = $oUserCertification->user_certification_id;
                $oUserCertificationSource->type             = UserCertificationSource::TYPE_FIRST;
                $oUserCertificationSource->source_type      = 'video';
            }
            $oUserCertificationSource->source_url = $sVideo;
            $oUserCertificationSource->status     = 'C';
            if ( $oUserCertificationSource->save() === FALSE ) {
                $connection->rollback();
                throw new Exception(implode(',', $oUserCertificationSource->getMessages()));
            }
            $connection->commit();

            // 发送实名认证系统消息
            $this->sendCertificationMsg($oUser->user_id, $oUserCertification);

        } catch ( Exception $e ) {
            $this->error(
                ResponseError::OPERATE_FAILED,
                sprintf('%s[%s]', ResponseError::getError(ResponseError::OPERATE_FAILED), $e->getMessage())
            );
        }

        $this->success();
    }


    /**
     * 图片认证提交
     * @apiVersion 1.3.0
     * @apiSampleRequest http://dev.api.sxypaopao.com/v1/user/certification/image
     * @api {get} /user/certification/image 图片认证提交
     * @apiName 图片认证-image
     * @apiGroup Certification
     * @apiDescription 图片认证提交
     * @apiParam (正常请求){String} access_token  token值
     * @apiParam (正常请求){String} image  图片地址 以半角逗号分隔  5张图片
     * @apiParam (正常请求){String='anchor(主播)','photographer(摄影师)'} certification_type  认证类型
     * @apiParam (debug) {String} debug  debug
     * @apiParam (debug) {String} debug_auth  debug
     * @apiParam (debug) {String} uid  用户id
     * @apiParam (debug){String} image  图片地址 以半角逗号分隔  5张图片
     * @apiParam (debug){String='anchor(主播)','photographer(摄影师)'} certification_type  认证类型
     * @apiSuccess {number} c 返回码
     * @apiSuccess {string} m 返回文字信息
     * @apiSuccess {number} t 服务器当前时间戳
     * @apiSuccess {Object} d 内容
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *         "c": 0,
     *         "d": "",
     *         "m": "请求成功",
     *         "t": 1534911421
     *     }
     * @apiError UserNotFound The id of the User was not found.
     * @apiErrorExample Error-Response:
     *     HTTP/1.1 404 Not Found
     *     {
     *       "error": "UserNotFound"
     *     }
     */
    public function imageAction( $nUserId = 0 )
    {
        $sImages            = $this->getParams('image', 'string', '');
        $sCertificationType = $this->getParams('certification_type', 'string');
        if ( !$sCertificationType ) {
            $sCertificationType = $this->getParams('user_certification_type', 'string', 'anchor');
        }
        try {

            if ( !in_array($sCertificationType, [
                'anchor',
                'photographer'
            ]) ) {
                throw new Exception(
                    ResponseError::getError(ResponseError::PARAM_ERROR),
                    ResponseError::PARAM_ERROR
                );
            }
            $sImagesArr = explode(',', $sImages);
            foreach ( $sImagesArr as $key => $item ) {
                if ( empty($item) ) {
                    unset($sImagesArr[ $key ]);
                }
            }
            $imageCount = 5;
            if ( count($sImagesArr) < $imageCount ) {
                throw new Exception(
                    sprintf(ResponseError::getError(ResponseError::USER_CERTIFICATION_IMAGES_COUNT), $imageCount),
                    ResponseError::USER_CERTIFICATION_IMAGES_COUNT
                );
            }

            $oUser = User::findFirst($nUserId);

            if ( $sCertificationType == 'anchor' ) {
                if ( $oUser->user_is_anchor == 'Y' ) {
                    //重新审核视频
                    throw new Exception(
                        ResponseError::getError(ResponseError::USER_IS_CERTIFICATION),
                        ResponseError::USER_IS_CERTIFICATION
                    );
                }
            } else {
                if ( $oUser->user_is_photographer == 'Y' ) {
                    //重新审核视频
                    throw new Exception(
                        ResponseError::getError(ResponseError::USER_IS_CERTIFICATION),
                        ResponseError::USER_IS_CERTIFICATION
                    );
                }
            }


            //第一次审核视频
            $oUserCertification = UserCertification::findFirst([
                'user_id=:user_id: order by user_certification_id desc',
                'bind' => [
                    'user_id' => $nUserId,
                ]
            ]);
            if ( empty($oUserCertification) ) {
                $oUserCertification = new UserCertification();
                // 将实名认证 设置为未认证
                $oUserCertification->user_certification_status       = 'NOT';
                $oUserCertification->user_certification_video_status = 'NOT';
                $oUserCertification->user_certification_type         = $sCertificationType;
            } else {
                switch ( $oUserCertification->user_certification_image_status ) {
                    case 'Y':
                        // 已认证
                        throw new Exception(
                            ResponseError::getError(ResponseError::USER_IS_CERTIFICATION),
                            ResponseError::USER_IS_CERTIFICATION
                        );
                        break;
                    case 'C':
                        // 认证中
                        throw new Exception(
                            ResponseError::getError(ResponseError::USER_CERTIFICATION_CHECK),
                            ResponseError::USER_CERTIFICATION_FAIL
                        );
                        break;
                    case 'D':
                        // 禁止认证
                        throw new Exception(
                            ResponseError::getError(ResponseError::USER_CERTIFICATION_FORBID),
                            ResponseError::USER_CERTIFICATION_FORBID
                        );
                        break;
                }
            }
            $connection = $oUserCertification->getWriteConnection();
            $connection->begin();
            $oUserCertification->user_id                         = $nUserId;
            $oUserCertification->user_certification_image_status = 'C';

            if ( $oUserCertification->save() === FALSE ) {
                $connection->rollback();
                throw new Exception(implode(',', $oUserCertification->getMessages()));
            }

            $saveAll     = [];
            $create_time = time();
            foreach ( $sImagesArr as $key => $item ) {
                $saveAll[] = [
                    'certification_id' => $oUserCertification->user_certification_id,
                    'source_url'       => $item,
                    'sort_num'         => $key,
                    'status'           => 'C',
                    'type'             => UserCertificationSource::TYPE_FIRST,
                    'create_time'      => $create_time,
                    'update_time'      => $create_time,
                ];
            }

            $sDeleteSql = sprintf("delete from user_certification_source where certification_id = %d and source_type = 'img'", $oUserCertification->user_certification_id);
            $connection->execute($sDeleteSql);

            $oUserCertificationSource = new UserCertificationSource();
            if ( $oUserCertificationSource->saveAll($saveAll) === FALSE ) {
                $connection->rollback();
                throw new Exception(implode(',', $oUserCertificationSource->getMessages()));
            }
            $connection->commit();

            // 发送实名认证系统消息
            $this->sendCertificationMsg($oUser->user_id, $oUserCertification);
        } catch ( Exception $e ) {
            $this->error(
                ResponseError::OPERATE_FAILED,
                sprintf('%s[%s]', ResponseError::getError(ResponseError::OPERATE_FAILED), $e->getMessage())
            );
        }

        $this->success();
    }


    /**
     * @apiVersion 1.3.0
     * @apiSampleRequest http://dev.api.sxypaopao.com/v1/user/certification/wechat
     * @api {get} /user/certification/wechat 微信认证状态
     * @apiName certification-wechat
     * @apiGroup Certification
     * @apiDescription 微信认证状态
     * @apiParam (正常请求){String} access_token  token值
     * @apiParam (正常请求){String} sign  签名
     * @apiParam (正常请求){String} timestamp  时间戳
     * @apiParam (debug) {String} debug  debug
     * @apiParam (debug) {String} cli_api_key  debug
     * @apiParam (debug) {String} uid  用户id
     * @apiSuccess {number} c 返回码
     * @apiSuccess {string} m 返回文字信息
     * @apiSuccess {number} t 服务器当前时间戳
     * @apiSuccess {Object} d 内容
     * @apiSuccess {string='Y(认证通过)','N(失败)','C(认证中)','NOT(没有提交过)'} d.status 状态
     * @apiSuccess {string} d.remark 失败原因
     * @apiSuccess {string} d.wechat 微信
     * @apiSuccess {string} d.price 价格
     * @apiSuccess {number} d.wechat_min_price  微信设置最小值
     * @apiSuccess {number} d.wechat_max_price  微信设置最大值
     * @apiSuccess {number} d.wechat_interval_price  微信设置间隔值
     * @apiSuccess {string} d.rule_href  密友等级说明h5
     * @apiSuccessExample Success-Response:
     *    {
     *        "c": 0,
     *        "m": "请求成功",
     *        "d": {
     *                "status": "N",
     *                "remark": "",
     *                "wechat": "",
     *                "price": "1",
     *                "wechat_min_price": 1000,
     *                "wechat_max_price": 10000,
     *                "wechat_interval_price": 1000,
     *        },
     *        "t": 1554092221
     *    }
     * @apiError UserNotFound The id of the User was not found.
     * @apiErrorExample Error-Response:
     *     HTTP/1.1 404 Not Found
     *     {
     *       "error": "UserNotFound"
     *     }
     */
    public function wechatAction( $nUserId = 0 )
    {

        try {
            $oUser  = User::findFirst($nUserId);
            $status = 'NOT';
            $result = '';
            $wechat = $oUser->user_wechat;
            $price  = $oUser->user_wechat_price;
            // 微信认证中
            $oWechatCertification = WechatCertification::findFirst([
                'wechat_certification_user_id = :wechat_certification_user_id:',
                'bind'  => [
                    'wechat_certification_user_id' => $nUserId
                ],
                'order' => 'wechat_certification_create_time desc'
            ]);
//            if ( $oWechatCertification && $oWechatCertification->wechat_certification_status != 'Y' ) {
            if ( $oWechatCertification ) {
                $status = $oWechatCertification->wechat_certification_status;
                $result = $oWechatCertification->wechat_certification_remark;
                $wechat = $oWechatCertification->wechat_certification_value;
                $price  = $oWechatCertification->wechat_certification_price;
            }

            // 获取收费微信价格列表
            $kvData    = Kv::many([
                Kv::WECHAT_MAX_PRICE,
                Kv::WECHAT_MIN_PRICE,
                Kv::WECHAT_INTERVAL_PRICE
            ]);
            $priceList = [];
            for ( $i = $kvData[ Kv::WECHAT_MIN_PRICE ]; $i <= $kvData[ Kv::WECHAT_MAX_PRICE ]; $i += $kvData[ Kv::WECHAT_INTERVAL_PRICE ] ) {
                $priceList[] = [
                    'price' => (string)$i
                ];
            }

            $row = [
                'status'                => $status,
                'remark'                => $result,
                'wechat'                => $wechat,
                'price'                 => $price > 0 ? $price : '',
                'wechat_min_price'      => intval($kvData[ Kv::WECHAT_MIN_PRICE ]),
                'wechat_max_price'      => intval($kvData[ Kv::WECHAT_MAX_PRICE ]),
                'wechat_interval_price' => intval($kvData[ Kv::WECHAT_INTERVAL_PRICE ]),
                'rule_href'             => 'http://yuyin-tv.baiduux.com/h5/miyoudengji.html',
                'price_list'            => $priceList
            ];


        } catch ( Exception $e ) {
            $this->error($e->getCode(), $e->getMessage());
        }
        $this->success($row);
    }


    /**
     * @apiVersion 1.3.0
     * @apiSampleRequest http://dev.api.sxypaopao.com/v1/user/certification/addWechat
     * @api {post} /user/certification/addWechat 提交微信认证
     * @apiName certification-addwechat
     * @apiGroup Certification
     * @apiDescription 提交微信认证
     * @apiParam (正常请求){String} access_token  token值
     * @apiParam (正常请求){String} sign  签名
     * @apiParam (正常请求){String} timestamp  时间戳
     * @apiParam (正常请求){String} wechat 微信号
     * @apiParam (正常请求){String} wechat_price 微信价格
     * @apiParam (debug) {String} debug  debug
     * @apiParam (debug) {String} cli_api_key  debug
     * @apiParam (debug) {String} uid  用户id
     * @apiParam (debug){String} wechat 微信号
     * @apiParam (debug){String} wechat_price 微信价格
     * @apiSuccess {number} c 返回码
     * @apiSuccess {string} m 返回文字信息
     * @apiSuccess {number} t 服务器当前时间戳
     * @apiSuccess {Object} d 内容
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *         "c": 0,
     *         "d": "",
     *         "m": "请求成功",
     *         "t": 1534911421
     *     }
     * @apiError UserNotFound The id of the User was not found.
     * @apiErrorExample Error-Response:
     *     HTTP/1.1 404 Not Found
     *     {
     *       "error": "UserNotFound"
     *     }
     */
    public function addWechatAction( $nUserId = 0 )
    {
        $wechat      = $this->getParams('wechat', 'string', '');
        $wechatPrice = $this->getParams('wechat_price', 'string', '');

        try {
            // 判断是否为主播或代理商
            $oUser = User::findFirst($nUserId);
            if ( $oUser->user_is_anchor == 'N' && $oUser->user_is_photographer == 'N' ) {
                throw new Exception(
                    ResponseError::getError(ResponseError::OPERATE_FAILED),
                    ResponseError::OPERATE_FAILED
                );
            }
            $oWechatCertification = WechatCertification::findFirst([
                'wechat_certification_user_id = :wechat_certification_user_id: AND wechat_certification_status != "N"',
                'bind'  => [
                    'wechat_certification_user_id' => $nUserId
                ],
                'order' => 'wechat_certification_create_time desc'
            ]);
            if ( $oWechatCertification && $oWechatCertification->wechat_certification_status == 'C' ) {
                throw new Exception(
                    ResponseError::getError(ResponseError::WECHAT_BEING_CHECK),
                    ResponseError::WECHAT_BEING_CHECK
                );
            }

            // 获取收费微信价格列表
            $kvData    = Kv::many([
                Kv::WECHAT_MAX_PRICE,
                Kv::WECHAT_MIN_PRICE,
                Kv::WECHAT_INTERVAL_PRICE
            ]);
            $priceList = [];
            for ( $i = $kvData[ Kv::WECHAT_MIN_PRICE ]; $i <= $kvData[ Kv::WECHAT_MAX_PRICE ]; $i += $kvData[ Kv::WECHAT_INTERVAL_PRICE ] ) {
                $priceList[] = (string)$i;
            }

            if ( !in_array($wechatPrice, $priceList) ) {
                $wechatPrice = $wechatPrice * 1000;
                if ( !in_array($wechatPrice, $priceList) ) {
                    throw new Exception(
                        '配置价格设置错误',
                        ResponseError::PARAM_ERROR
                    );
                }
            }


            $oWechatCertification                               = new WechatCertification();
            $oWechatCertification->wechat_certification_value   = $wechat;
            $oWechatCertification->wechat_certification_user_id = $nUserId;
            $oWechatCertification->wechat_certification_status  = 'C';
            $oWechatCertification->wechat_certification_price   = $wechatPrice;
            if ( !$oWechatCertification->save() ) {
                throw new Exception(sprintf('%s [%s]', ResponseError::getError(ResponseError::OPERATE_FAILED), implode(',', $oWechatCertification->getMessages())), ResponseError::OPERATE_FAILED);
            }
        } catch ( Exception $e ) {
            $this->error($e->getCode(), $e->getMessage());
        }
        $this->success();

    }


    /**
     * @apiVersion 1.2.0
     * @apiSampleRequest http://dev.api.sxypaopao.com/v1/user/certification/firstAdd
     * @api {get} /user/certification/firstAdd 001-190905首次提交审核
     * @apiName Certification-firstAdd
     * @apiGroup certification
     * @apiDescription 001-190905首次提交审核
     * @apiParam (正常请求) {String} realname  真实姓名
     * @apiParam (正常请求) {String} number  身份证号
     * @apiParam (正常请求) {String} front_img  正面照
     * @apiParam (正常请求) {String} back_img  后面照
     * @apiParam (正常请求) {String} user_img  手持照
     * @apiParam (正常请求) {String} certification_type  认证类型
     * @apiParam (正常请求) {String} video  视频地址
     * @apiParam (正常请求) {String} image  图片地址
     * @apiParam (debug) {String} debug_auth  认证
     * @apiParam (debug) {String} uid  用户id
     * @apiParam (debug) {String} realname  真实姓名
     * @apiParam (debug) {String} realname  真实姓名
     * @apiParam (debug) {String} number  身份证号
     * @apiParam (debug) {String} front_img  正面照
     * @apiParam (debug) {String} back_img  后面照
     * @apiParam (debug) {String} user_img  手持照
     * @apiParam (debug) {String} certification_type  认证类型
     * @apiParam (debug) {String} video  视频地址
     * @apiParam (debug) {String} image  图片地址
     * @apiSuccess {number} c 返回码
     * @apiSuccess {string} m 返回文字信息
     * @apiSuccess {number} t 服务器当前时间戳
     * @apiSuccess {Object} d 内容
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *         "c": 0,
     *         "d": "",
     *         "m": "请求成功",
     *         "t": 1534911421
     *     }
     */
    public function firstAddAction( $nUserId = 0 )
    {
        $sRealname          = $this->getParams('realname', 'string', '');
        $sNumber            = $this->getParams('number', 'string', '');
        $sFrontImg          = $this->getParams('front_img', 'string', '');
        $sBackImg           = $this->getParams('back_img', 'string', '');
        $sUserImg           = $this->getParams('user_img', 'string', '');
        $sCertificationType = $this->getParams('certification_type', 'string');
        $sVideo             = $this->getParams('video', 'string', '');
        $sImages            = $this->getParams('image', 'string', '');

        try {

            if ( !in_array($sCertificationType, [
                'anchor',
                'photographer'
            ]) ) {
                throw new Exception(
                    sprintf('%s[%s]', ResponseError::getError(ResponseError::PARAM_ERROR), '认证类型'),
                    ResponseError::PARAM_ERROR
                );
            }
            if ( empty($sVideo) ) {
                throw new Exception(
                    sprintf('%s[%s]', ResponseError::getError(ResponseError::PARAM_ERROR), '视频地址'),
                    ResponseError::PARAM_ERROR
                );
            }

            if ( empty($sImages) ) {
                throw new Exception(
                    sprintf('%s[%s]', ResponseError::getError(ResponseError::PARAM_ERROR), '图片地址'),
                    ResponseError::PARAM_ERROR
                );
            }

            $sImagesArr = explode(',', $sImages);
            foreach ( $sImagesArr as $key => $item ) {
                if ( empty($item) ) {
                    unset($sImagesArr[ $key ]);
                }
            }
            $imageCount = 5;
            if ( count($sImagesArr) < $imageCount ) {
                throw new Exception(
                    sprintf(ResponseError::getError(ResponseError::USER_CERTIFICATION_IMAGES_COUNT), $imageCount),
                    ResponseError::USER_CERTIFICATION_IMAGES_COUNT
                );
            }

            $oUser = User::findFirst($nUserId);
            switch ( $oUser->user_is_certification ) {
                case 'Y':
                    // 已实名认证
                    throw new Exception(
                        ResponseError::getError(ResponseError::USER_IS_CERTIFICATION),
                        ResponseError::USER_IS_CERTIFICATION
                    );
                    break;
                case 'C':
                    // 认证中
                    throw new Exception(
                        ResponseError::getError(ResponseError::USER_CERTIFICATION_CHECK),
                        ResponseError::USER_CERTIFICATION_FAIL
                    );
                    break;
                case 'D':
                    // 禁止认证
                    throw new Exception(
                        ResponseError::getError(ResponseError::USER_CERTIFICATION_FORBID),
                        ResponseError::USER_CERTIFICATION_FORBID
                    );
                    break;
                case 'R':
                case 'N':
                default:
            }
            $oUserCertification = UserCertification::findFirst([
                'user_id=:user_id: order by user_certification_id desc',
                'bind' => [
                    'user_id' => $nUserId,
                ]
            ]);

            if ( $oUserCertification ) {
                // 正在核对
                throw new Exception(
                    ResponseError::getError(ResponseError::USER_CERTIFICATION_CHECK),
                    ResponseError::USER_CERTIFICATION_FAIL
                );
            }


            $this->db->begin();

            // 实名认证开始
            $oUserCertification = new UserCertification();

            if ( $oUser->user_group_id == 0 ) {
                $oUserCertification->group_certification_status = 'Y';
            }
            $oUserCertification->user_id                         = $nUserId;
            $oUserCertification->user_front_img                  = $sFrontImg;
            $oUserCertification->user_back_img                   = $sBackImg;
            $oUserCertification->user_img                        = $sUserImg;
            $oUserCertification->user_certification_number       = $sNumber;
            $oUserCertification->user_realname                   = $sRealname;
            $oUserCertification->user_certification_type         = $sCertificationType;
            $oUserCertification->user_certification_status       = 'C';
            $oUserCertification->user_certification_video_status = 'C';
            $oUserCertification->user_certification_image_status = 'C';

            if ( $oUserCertification->save() === FALSE ) {
                $this->db->rollback();
                throw new Exception($oUserCertification->getMessage());
            }
            // 实名认证结束

            // 视频认证开始
            $oUserCertificationSource                   = new UserCertificationSource();
            $oUserCertificationSource->sort_num         = 1;
            $oUserCertificationSource->certification_id = $oUserCertification->user_certification_id;
            $oUserCertificationSource->type             = UserCertificationSource::TYPE_FIRST;
            $oUserCertificationSource->source_type      = 'video';
            $oUserCertificationSource->source_url       = $sVideo;
            $oUserCertificationSource->status           = 'C';
            if ( $oUserCertificationSource->save() === FALSE ) {
                $this->db->rollback();
                throw new Exception($oUserCertificationSource->getMessage());
            }
            // 视频认证结束

            // 图片认证开始

            $saveAll     = [];
            $create_time = time();
            foreach ( $sImagesArr as $key => $item ) {
                $saveAll[] = [
                    'certification_id' => $oUserCertification->user_certification_id,
                    'source_url'       => $item,
                    'sort_num'         => $key,
                    'status'           => 'C',
                    'type'             => UserCertificationSource::TYPE_FIRST,
                    'create_time'      => $create_time,
                    'update_time'      => $create_time,
                ];
            }

            $sDeleteSql = sprintf("delete from user_certification_source where certification_id = %d and source_type = 'img'", $oUserCertification->user_certification_id);
            $this->db->execute($sDeleteSql);

            $oUserCertificationSource = new UserCertificationSource();
            if ( $oUserCertificationSource->saveAll($saveAll) === FALSE ) {
                $this->db->rollback();
                throw new Exception($oUserCertificationSource->getMessage());
            }
            // 图片认证结束

            $oUser->user_is_certification = 'C';
            if ( $oUser->save() === FALSE ) {
                $this->db->rollback();
                throw new Exception($oUser->getMessage());
            }

            // 发送实名认证系统消息
            $this->sendCertificationMsg($oUser->user_id, $oUserCertification);

            $this->db->commit();
        } catch ( Exception $e ) {
            $this->error($e->getCode(), $e->getMessage());
        }
        $this->success();
    }


}