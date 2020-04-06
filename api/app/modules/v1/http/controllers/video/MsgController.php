<?php


namespace app\http\controllers\video;

use app\helper\ResponseError;
use app\http\controllers\ControllerBase;
use app\models\UserVideo;
use Phalcon\Exception;
use app\models\UserVideoMessage;
use app\models\User;

/**
 * 视频消息
 */
class MsgController extends ControllerBase
{
    //获取视频消息列表
    public function indexAction($nUserId = 0)
    {
        $nPage     = $this->getParams('page', 'int', 1);
        $nPagesize = $this->getParams('pagesize', 'int', 20);
        $row       = [];
        try {
            $columns = "u.user_id,u.user_nickname,u.user_avatar,u.user_level,vm.type,vm.content,vm.create_time,uv.id,uv.cover,vm.is_read";
            $builder = $this->modelsManager
                ->createBuilder()
                ->from([ 'vm' => UserVideoMessage::class ])
                ->join(User::class, 'u.user_id=vm.operate_user_id', 'u')
                ->join(UserVideo::class, 'uv.id=vm.video_id', 'uv')
                ->columns($columns)
                ->where("vm.user_id = :user_id: AND u.user_is_deny_speak = 'N'", [ 'user_id' => $nUserId ])
                ->orderBy("vm.id desc");

            $row = $this->page($builder, $nPage, $nPagesize);

        } catch ( Exception $e ) {
            $this->error(
                ResponseError::FAIL,
                sprintf('%s[%s]', ResponseError::getError(ResponseError::FAIL), $e->getMessage())
            );
        }
        $model = new UserVideoMessage();
        $model->readMessage($nUserId);
        $this->success($row);
    }

    public function getUnReadNumAction($nUserId = 0)
    {
        $model = new UserVideoMessage();
        $num   = $model->getUnreadNum($nUserId);
        $data  = UserVideoMessage::findFirst("user_id={$nUserId} order by id DESC");
        $time  = '0';
        $msg   = '';
        $type   = '';
        if ( $data ) {
            $time = $data->create_time;
            $msg  = $data->content;
            $type = $data->type;
        }
        $this->success([ 'num'  => $num,
                         'time' => $time,
                         'msg'  => $msg,
                         'type' => $type
        ]);
    }

}