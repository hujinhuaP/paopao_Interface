<?php 

namespace app\models;

/**
* ShortPosts 动态
*/
class ShortPosts extends ModelBase
{
    const PAY_TYPE_FREE      = 'free';
    const PAY_TYPE_PART_FREE = 'part_free';
    const PAY_TYPE_PAY       = 'pay';
    /** @var string 作品 */
    const TYPE_EXHIBITION    = 'exhibition';

    /**
     * @param $user_id
     * 获取自己的动态数量
     */
    public static function getPostsCount($user_id) {
        return self::count([
            'short_posts_user_id = :short_posts_user_id:',
            'bind' => [
                'short_posts_user_id' => $user_id
            ],
            'cache' => [
                'lifetime' => 3600,
                'key'      => sprintf('user_posts_count:%s',$user_id)
            ]
        ]);
    }

    public function initialize()
    {
        $this->useDynamicUpdate(true);
        $this->keepSnapshots(true);
    }
	public function beforeCreate()
    {
		$this->short_posts_create_time = time();
		$this->short_posts_update_time = time();
		$modelsCache = self::getModelsCache();
		$modelsCache->delete(sprintf('user_posts_count:%s',$this->short_posts_user_id));
		if($this->short_posts_price == 0 ){
		    $this->short_posts_pay_type = self::PAY_TYPE_FREE;
        }
    }

    public function beforeDelete()
    {
        $modelsCache = self::getModelsCache();
        $modelsCache->delete(sprintf('user_posts_count:%s',$this->short_posts_user_id));
    }

    public function beforeUpdate()
    {
        $hasChanged = $this->hasChanged(['short_posts_comment_num','short_posts_gift_num']);
        if($hasChanged){
            $this->short_posts_update_time = time();
        }
    }


}