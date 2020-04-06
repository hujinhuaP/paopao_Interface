<?php 

namespace app\models;

/**
* UserGiftRank 用户礼物排行榜
*/
class UserGiftRank extends ModelBase
{
	/** @var string 总榜类型 */
    const RANK_ALL = 'all';
    /** @var string 周榜类型 */
    const RANK_WEEK = 'week';
    /** @var string 日榜类型 */
    const RANK_DAY = 'day';

	public function beforeCreate()
    {
		$this->user_gift_rank_create_time = time();
		$this->user_gift_rank_update_time = time();
    }

    public function beforeUpdate()
    {
        $this->user_gift_rank_update_time = time();
    }
}