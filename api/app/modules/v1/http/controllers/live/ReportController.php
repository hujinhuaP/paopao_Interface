<?php

/*
 +------------------------------------------------------------------------+
 | Green Live                                                             |
 +------------------------------------------------------------------------+
 | Copyright (c) 2017-2017 Green Live Team (https://www.greenlive.com)    |
 +------------------------------------------------------------------------+
 |举报控制器                                                              |
 +------------------------------------------------------------------------+
 |                                      |
 +------------------------------------------------------------------------+
 */

namespace app\http\controllers\live;

use Exception;

use app\helper\ResponseError;
use app\models\AnchorReportLog;
use app\http\controllers\ControllerBase;

/**
 * ReportController 举报控制器
 */
class ReportController extends ControllerBase
{
    use \app\services\SystemMessageService;

    /**
     * indexAction 举报内容
     *
     * @param  int $nUserId
     */
    public function indexAction($nUserId = 0)
    {
        $row['report'] = [
            [ 'id'      => '1',
              'content' => '未成年人'
            ],
            [ 'id'      => '2',
              'content' => '驾驶吸烟'
            ],
            [ 'id'      => '3',
              'content' => '商业广告'
            ],
        ];
        $this->success($row);
    }

    /**
     * @apiVersion 1.3.0
     * @apiSampleRequest http://dev.api.sxypaopao.com/v1/live/report/room
     * @api {post} /live/report/room 举报
     * @apiName report-room
     * @apiGroup User
     * @apiDescription 举报
     * @apiParam (正常请求){String} access_token  token值
     * @apiParam (正常请求){String} title 类型
     * @apiParam (正常请求){String} content 详情
     * @apiParam (正常请求){String} images 图片地址 半角逗号分隔
     * @apiParam (正常请求){String} anchor_user_id 主播id
     * @apiParam (debug) {String} debug  debug
     * @apiParam (debug) {String} cli_api_key  debug
     * @apiParam (debug) {String} uid  用户id
     * @apiParam (debug){String} title 类型
     * @apiParam (debug){String} content 详情
     * @apiParam (debug){String} images 图片地址 半角逗号分隔
     * @apiParam (debug){String} anchor_user_id 主播id
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
    public function roomAction($nUserId = 0)
    {
        $sContent      = $this->getParams('content', 'string', '');
        $nAnchorUserId = $this->getParams('anchor_user_id', 'int', 0);
        $sTitle        = $this->getParams('title', 'string', '未知');
        $sImages       = $this->getParams('images', 'string', '');

        try {
            if(empty($nAnchorUserId)){
                throw new Exception(ResponseError::getError(ResponseError::PARAM_ERROR), ResponseError::PARAM_ERROR);
            }
            $oAnchorReportLog                            = new AnchorReportLog();
            $oAnchorReportLog->user_id                   = $nUserId;
            $oAnchorReportLog->anchor_user_id            = $nAnchorUserId;
            $oAnchorReportLog->anchor_report_log_content = $sContent;
            $oAnchorReportLog->anchor_report_title       = $sTitle;
            $oAnchorReportLog->anchor_report_images      = $sImages;
            $oAnchorReportLog->save();
        } catch ( Exception $e ) {
            $this->error($e->getCode(), $e->getMessage());
        }
        $this->sendReportMsg($nUserId);

        $this->success();
    }
}