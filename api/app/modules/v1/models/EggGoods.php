<?php

namespace app\models;

class EggGoods extends ModelBase
{
    /** @var string 类型 - 金币 */
    const CATEGORY_COIN    = 'coin';
    /** @var string 类型 - 钻石 */
    const CATEGORY_DIAMOND = 'diamond';
    /** @var string 类型 - VIP */
    const CATEGORY_VIP = 'vip';

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=false)
     */
    public $egg_goods_id;
    /**
     *
     * @var string
     * @Column(type="string", length=11, nullable=false)
     */
    public $egg_goods_category;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=false)
     */
    public $egg_goods_value;

    /**
     *
     * @var string
     * @Column(type="string", length=11, nullable=false)
     */
    public $egg_goods_name;

    /**
     *
     * @var integer
     * @Column(type="float", length=11, nullable=false)
     */
    public $egg_goods_point;

    /**
     *
     * @var string
     * @Column(type="string", length=11, nullable=false)
     */
    public $egg_goods_image;

    /**
     *
     * @var string
     * @Column(type="string", length=11, nullable=false)
     */
    public $egg_goods_notice_flg;

    /**
     * 砸蛋
     * 随机一个1到10000 的数
     * 遍历礼物  取礼物对应的区间
     *      在区间内则为当前礼物
     *      全部取完后 如果都没获取到礼物 则取安慰奖
     * @param int $nNumber
     * @return array $reward
     */
    public static function getReward( $nNumber )
    {
        $reward        = [];
        $oEggGoodsList = EggGoods::find([
            'egg_goods_point > 0',
            'order' => 'egg_goods_point desc'
        ]);

        $kvData = Kv::many([
            Kv::CONSOLATION_CATEGORY,
            Kv::CONSOLATION_VALUE,
            Kv::CONSOLATION_NAME,
            Kv::CONSOLATION_IMAGE
        ]);

        if ( count($oEggGoodsList) == 0 ) {
            $reward[ -1 ] = [
                'egg_goods_id'         => -1,
                'egg_goods_category'   => $kvData[ Kv::CONSOLATION_CATEGORY ],
                'egg_goods_value'      => $kvData[ Kv::CONSOLATION_VALUE ],
                'egg_goods_name'       => $kvData[ Kv::CONSOLATION_NAME ],
                'egg_goods_image'      => $kvData[ Kv::CONSOLATION_IMAGE ],
                'rand'                 => 0,
                'egg_goods_notice_flg' => 'N',
                'reward_number'        => $nNumber
            ];
            return $reward;
        }

        while ( $nNumber > 0 ) {
            //
            $rand       = mt_rand(1, 10000);
            $minValue   = 0;
            $itemReward = NULL;
            foreach ( $oEggGoodsList as $oEggGoods ) {
                $tmp          = $oEggGoods->toArray();
                $tmp['rand']  = $rand;
                $itemMinValue = $minValue + 1;
                $maxValue     = 10000 * $oEggGoods->egg_goods_point / 100 + $minValue;
                $itemMaxValue = $maxValue;
                $minValue     = $itemMaxValue;

                if ( $rand >= $itemMinValue && $rand <= $itemMaxValue ) {
                    // 在这个值里面
                    $itemReward = $oEggGoods;

                    break;
                }
            }
            if ( $itemReward ) {
                $tmp = $itemReward->toArray();
                unset($tmp['egg_goods_point']);
                if ( array_key_exists($itemReward->egg_goods_id, $reward) ) {
                    $tmp['reward_number']                = $reward[ $itemReward->egg_goods_id ]['reward_number'] + 1;
                    $reward[ $itemReward->egg_goods_id ] = $tmp;
                } else {
                    $tmp['reward_number']                = 1;
                    $reward[ $itemReward->egg_goods_id ] = $tmp;
                }
            } else {
                // 安慰奖
                if ( array_key_exists(-1, $reward) ) {
                    $reward[ -1 ] = [
                        'egg_goods_id'         => -1,
                        'egg_goods_category'   => $kvData[ Kv::CONSOLATION_CATEGORY ],
                        'egg_goods_value'      => $kvData[ Kv::CONSOLATION_VALUE ],
                        'egg_goods_name'       => $kvData[ Kv::CONSOLATION_NAME ],
                        'egg_goods_image'      => $kvData[ Kv::CONSOLATION_IMAGE ],
                        'rand'                 => $rand,
                        'egg_goods_notice_flg' => 'N',
                        'reward_number'        => $reward[ -1 ]['reward_number'] + 1
                    ];
                } else {
                    $reward[ -1 ] = [
                        'egg_goods_id'         => -1,
                        'egg_goods_category'   => $kvData[ Kv::CONSOLATION_CATEGORY ],
                        'egg_goods_value'      => $kvData[ Kv::CONSOLATION_VALUE ],
                        'egg_goods_name'       => $kvData[ Kv::CONSOLATION_NAME ],
                        'egg_goods_image'      => $kvData[ Kv::CONSOLATION_IMAGE ],
                        'rand'                 => $rand,
                        'egg_goods_notice_flg' => 'N',
                        'reward_number'        => 1
                    ];
                }

            }
            $nNumber--;
        }
        sort($reward);

        return $reward;
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'egg_goods';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return EggGoods[]|BuyHammerLog|\Phalcon\Mvc\Model\ResultSetInterface
     */
    public static function find( $parameters = NULL )
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return EggGoods|\Phalcon\Mvc\Model\ResultInterface
     */
    public static function findFirst( $parameters = NULL )
    {
        return parent::findFirst($parameters);
    }


}
