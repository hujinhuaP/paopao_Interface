<?php
/*
 +------------------------------------------------------------------------+
 | Green Live                                                             |
 +------------------------------------------------------------------------+
 | Copyright (c) 2017-2017 Green Live Team (https://www.greenlive.com)    |
 +------------------------------------------------------------------------+
 |直播视频云回调                                                          |
 +------------------------------------------------------------------------+
 |                                      |
 +------------------------------------------------------------------------+
 */

namespace app\http\controllers\live;

use Exception;
use app\http\controllers\ControllerBase;
/**
 * CallbackController 直播视频云回调
 */
class CallbackController extends ControllerBase {
    use \app\services\UserService;

    /**
     * liveStatusAction 直播状态回调
     */
    public function liveStatusAction() {
        $sParams = file_get_contents("php://input");
//        $this->log->info($sParams);
        $aParams = json_decode($sParams, true);
        if ($this->getParams('debug') == 1) {
            $aParams = json_decode('{"appid":1256015004,"channel_id":"20989_yingyan_10002","duration":183,"end_time":1521690118,"event_type":100,"file_format":"flv","file_id":"7447398155134540229","file_size":16041914,"media_start_time":2967,"record_file_id":"7447398155134540229","sign":"76fdeb6d74e7b2fccc340a262dd82218","start_time":1521689864,"stream_id":"20989_yingyan_10002","stream_param":"bizid=20989&txSecret=208b82650fc0c18823878ccf53eabe9a&txTime=5AB5247F","t":1521690721,"task_id":"9531622708116201570","video_id":"210032122_7f4828cfe7df49dfbe1ba2a262878b12","video_url":"http://1256015004.vod2.myqcloud.com/0b6a9c37vodgzp1256015004/681ef9c87447398155134540229/f0.flv"}', 1);
        }
        if (!isset($aParams['event_type'])) {
            $this->log->error($sParams);
        } else {
            // event_type（通知类型）
            // 目前腾讯云支持三种消息类型的通知：0 — 断流； 1 — 推流；100 — 新的录制文件已生成；200 — 新的截图文件已生成。
            /*
                stream_id | channel_id（直播码）
                在直播码模式下，stream_id 和 channel_id 两个字段都是同一个值，有两个不同的名字主要是历史原因所致。

                t（过期时间）
                来自腾讯云的消息通知的默认过期时间是10分钟，如果一条通知消息中的 t 值所指定的时间已经过期，则可以判定这条通知无效，进而可以防止网络重放攻击。t 的格式为十进制UNIX时间戳，即从1970年1月1日（UTC/GMT的午夜）开始所经过的秒数。

                sign（安全签名）
                sign = MD5(key + t) ：腾讯云把加密key 和 t 进行字符串拼接后，通过MD5计算得出sign值，并将其放在通知消息里，您的后台服务器在收到通知消息后可以根据同样的算法确认sign是否正确，进而确认消息是否确实来自腾讯云后台。
             */
            if ($aParams['sign'] !== md5($this->config->live->authKey . $aParams['t'])) {
                $this->log->error($aParams);
                exit('{"code":0}');
            }
            // if (time() > $aParams['t']) {
            // 	$this->log->error($sParams);
            // 	exit('{"code":0}');
            // }
            switch ($aParams['event_type']) {
                case 0:
                    $this->unpublishHandle($aParams);
                    break;
                case 1:
                    $this->publishHandle($aParams);
                    break;
                case 100:
                    $this->recordHandle($aParams);
                    break;
                case 200:
                    $this->screenshotHandle($aParams);
                    break;
                default:
                    $this->log->error($sParams);
                    break;
            }
        }
        echo '{"code":0}';
    }

    /**
     * publishHandle 推流处理程序
     *
     * @return bool
     */
    private function publishHandle($aParams) {
        return;
        /**
         * {
         * "app": "101.226.84.104",
         * "appid": 1252571077,
         * "appname": "live",
         * "channel_id": "10888_100076",
         * "errcode": 0,
         * "errmsg": "ok",
         * "event_time": 1509672428,
         * "event_type": 1,
         * "idc_id": 33,
         * "node": "100.119.17.9",
         * "sequence": "653434696377249304",
         * "set_id": 2,
         * "sign": "7235928fbd90e8dbd3ed78795e36dd3e",
         * "stream_id": "10888_100076",
         * "stream_param": "bizid=10888&txSecret=60a0d69459e7db02d74955dd0670a4b9&txTime=59FC927F",
         * "t":1509673029,
         * "user_ip ":"183.14 .31 .248 "
         * }
         */
    }

    /**
     * unpublishHandle 断流处理程序
     *
     * @return bool
     */
    private function unpublishHandle($aParams) {
        return;
        /**
         * {
         * "app": "101.226.84.104",
         * "appid": 1252571077,
         * "appname": "live",
         * "channel_id": "10888_100076",
         * "errcode": 1,
         * "errmsg": "recv rtmp deleteStream",
         * "event_time": 1509
         * 672432,
         * "event_type": 0,
         * "idc_id": 33,
         * "node": "100.119.17.9",
         * "sequence": "653434696377249304",
         * "set_id": 2,
         * "sign": "3298209dee878e2fea755f405d5bd42b",
         * "stream_id": "10888_100076",
         * "stream_param": "bizid = 10888 & txSecret = 60 a0d69459e7db02d74955dd0670a4b9 & txTime = 59 FC927F ","
         * t ":1509673033,"
         * user_ip ":"
         * 183.14 .31 .248 "
         * }
         */
        /*

        1	recv rtmp deleteStream	主播端主动断流
        2	recv rtmp closeStream	主播端主动断流
        3	recv() return 0	主播端主动断开TCP连接
        4	recv() return error	主播端TCP连接异常
        7	rtmp message large than 1M	收到流数据异常
        18	push url maybe invalid	推流鉴权失败，服务端禁止推流
        19	3rdparty auth failed	第三方鉴权失败，服务端禁止推流
        其他错误码	直播服务内部异常	如需处理请联系腾讯商务人员或者提交工单，联系电话：4009-100-100

         */
    }

    /**
     * recordHandle 录制文件处理程序
     *
     * @return bool
     */
    private function recordHandle($aParams) {
        /*{
        　　  "appid": 1252033264,
        　   "channel_id": "2519_2500647",
        　   "duration": 272,
        　　  "end_time": 1496220894,
        　　  "event_type": 100,
        　　  "file_format": "flv",
        　　  "file_id": "9031868222958931071",
        　　  "file_size": 30045521,
        　　  "record_file_id": "9031868222958931071",
        　　  "sign": "c2e3bdc344ddb62ab05229d01672a79e",
        　　  "start_time": 1496220622,
        　　  "stream_id": "2519_2500647",
        　　  "stream_param":      "bizid=2519&record=hls|flv&txSecret=d5569fb19d1e858bf683b30c10dec908&txTime=592FBDD9&mix=layer:b;session_id:709036962551160107;t_id:1",
        　　  "t": 1496221502,
        　　  "video_id": "200011683_481565e0befe4e44903839aebe370ef6",
        　　  "video_url": "http://1252033264.vod2.myqcloud.com/d7a4cabbvodgzp1252033264/0257ade99031868222958931071/f0.flv"
        }*/
    }

    /**
     * recordHandle 截图文件处理程序
     *
     * @return bool
     */
    private function screenshotHandle($aParams) {
        /*
        {
            "channel_id": "2016090090936",
            "create_time": 1473645788,
            "event_type": 200,
            "pic_url": "/2016-09-12/2016090090936-screenshot-10-03-08-1280x720.jpg", //文件路径名
            "sign": "8704a0297ab7fdd0d8d94f8cc285cbb7",
            "stream_id": "2016090090936",
            "t": 1473646392
        }

        返回的pic_url 不是真正的图片下载地址只是下载路径，真正的下载地址是需要拼接的，拼接方法是：

        下载前缀：http://(cos_bucketname)-(cos_appid).file.myqcloud.com/
        下载路径：/2016-09-12/2016090090936-screenshot-10-03-08-1280x720.jpg
        完整URL：http://(cos_bucketname)-(cos_appid).file.myqcloud.com/2016-09-12/2016090090936-screenshot-10-03-08-1280x720.jpg
         */
    }
}