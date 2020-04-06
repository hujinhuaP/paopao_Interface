<?php 

namespace app\models;

/**
* UserVideoReply 分类
*/
class UserVideoReply extends ModelBase
{

    public $id;
    public $user_id;
    public $video_id;
    public $follow_user_id;
    public $content;
    public $create_time;
	public function beforeCreate()
    {
		$this->create_time = time();
    }

    /**
     * 添加视频的回复条数
     */
    public function afterCreate() {
        /*添加流水*/
        $video = UserVideo::findFirst("id={$this->video_id}");
        if($video){
            $video->reply_num += 1;
            $video->save();
            $message_model = new UserVideoMessage();
            if($this->follow_user_id == 0){
                $message_model->addData($video->user_id,$this->video_id,2,$this->content,$this->user_id);
            }
        }
    }

}