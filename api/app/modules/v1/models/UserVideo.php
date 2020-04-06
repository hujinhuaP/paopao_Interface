<?php 

namespace app\models;

use app\services\VipTodayVideoService;

/**
* UserVideo 分类
*/
class UserVideo extends ModelBase
{

    public $id;
    public $user_id;
    public $type;
    public $title;
    public $cover;
    public $play_url;
    public $music_id;
    public $like_num;
    public $reply_num;
    public $share_num;
    public $create_time;
    public $lat;
    public $lng;
    public $city;
    public $hot_time;
    public $duration;
    public $watch_type;
    public $watch_price;

    const WATCH_TYPE_FREE = 'free';
    const WATCH_TYPE_CHARGE = 'charge';

    /**
     * @param $user_id
     * 获取用户小视频总数
     */
    public static function getVideoCount($user_id) {
        return self::count([
            'user_id = :user_id:',
            'bind' => [
                'user_id' => $user_id
            ],
            'cache' => [
                'lifetime' => 3600,
                'key'      => sprintf('user_video_count:%s',$user_id)
            ]
        ]);
    }

    public function beforeCreate()
    {
		$this->create_time = time();
        $modelsCache = self::getModelsCache();
        $modelsCache->delete(sprintf('user_video_count:%s',$this->user_id));
    }

    public function beforeDelete()
    {
        $modelsCache = self::getModelsCache();
        $modelsCache->delete(sprintf('user_video_count:%s',$this->user_id));
    }

    public function getIdByType($type){
        $data = UserVideo::query()
            ->columns("id,cover")
            ->where("type={$type} and is_show = 1")
            ->orderBy("hot_time desc,id desc")
            ->execute()
            ->toArray();
        return $data;

    }

    /**
     * @param \app\models\User $oUser
     * 检查用户是否有该权限
     * 如果是本人 可以观看
     *      判断是否付费 如果没有付费 则需要付费后观看
     */
    public function checkVideoAuth($oUser)
    {
        if($this->user_id == $oUser->user_id || $this->watch_price == 0 || $this->watch_type == self::WATCH_TYPE_FREE){
            // 本人观看
            return  true;
        }
        $oUserVideoPay = UserVideoPay::findFirst([
            'video_id = :video_id: AND user_id = :user_id:',
            'bind'  =>
                [ 'video_id' => $this->id,'user_id' => $oUser->user_id ],
        ]);
        if($oUserVideoPay){
            return true;
        }
//        if($oUser->user_member_expire_time > time()){
//            // 是VIP 再判断是否在今天免费的名额中
//            $oVipTodayVideoService = new VipTodayVideoService($oUser->user_id);
//            $existsVideo = $oVipTodayVideoService->getData();
//            if($existsVideo){
//                if(in_array($this->id,$existsVideo)){
//                    // 是今天免费的视频中的
//                    return true;
//                }
//                if(count($existsVideo) < 3){
//                    // 不满三部 则可以添加
//                    $oVipTodayVideoService->save($this->id);
//                    $nowExistsVideo = $oVipTodayVideoService->getData();
//                    if(count($nowExistsVideo) <= 3){
//                        // 添加进免费的视频中
//                        return true;
//                    }
//                    if(count($nowExistsVideo) > 3){
//                        // 需要删除
//                        $oVipTodayVideoService->delete_item($this->id);
//                        return false;
//                    }
//                }
//            }
//        }
        return false;
    }

}