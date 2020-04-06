<?php 

namespace app\models;

/**
* UserVideoLike 分类
*/
class UserVideoLike extends ModelBase
{

    public $id;
    public $user_id;
    public $video_id;
    public $create_time;

	public function beforeCreate()
    {
		$this->create_time = time();
    }

    /**
     * 添加视频的点赞数
     */
    public function afterCreate() {
        /*添加流水*/
        $video = UserVideo::findFirst("id={$this->video_id}");
        if($video){
            $video->like_num += 1;
            $video->save();
            $message_model = new UserVideoMessage();
            $message_model->addData($video->user_id,$this->video_id,1,'',$this->user_id);
        }
    }

    /**
     * 减少点赞数
     */
    public function afterDelete() {
        $model      = new UserVideo();
        $connection = $model->getWriteConnection();
        /*添加流水*/
        $sql = "UPDATE user_video SET like_num=like_num - 1 WHERE id = :id AND like_num > 1";
        $connection->execute($sql, [
            'id'   => $this->video_id,
        ]);
        if($connection->affectedRows() <= 0){
            $sql = "UPDATE user_video SET like_num=0 WHERE id = :id";
            $connection->execute($sql, [
                'id'   => $this->video_id,
            ]);
            $connection->affectedRows();
        }
    }
}