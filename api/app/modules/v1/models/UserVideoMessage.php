<?php 

namespace app\models;
/**
* 用户视频消息
*/
class UserVideoMessage extends ModelBase
{

    public $id;
    public $user_id;
    public $video_id;
    public $type;
    public $content;
    public $is_read;
    public $operate_user_id;
    public $create_time;
	public function beforeCreate()
    {
		$this->create_time = time();
    }
    public function addData($user_id,$video_id,$type,$content='',$operate_user_id){
        $model = new UserVideoMessage();
        $model->user_id = $user_id;
        $model->video_id = $video_id;
        $model->type = $type;
        $model->content = $content;
        $model->operate_user_id = $operate_user_id;
        $model->create();
        return true;
    }

    public function getUnreadNum($user_id){
        $num = UserVideoMessage::count("user_id = {$user_id} and is_read = 0");
        return $num;
    }
    public function readMessage($user_id){
        $model      = new UserVideoMessage();
        $connection = $model->getWriteConnection();
        $sql = "UPDATE user_video_message SET is_read = 1 where user_id = {$user_id}";
        $connection->execute($sql);
    }

}