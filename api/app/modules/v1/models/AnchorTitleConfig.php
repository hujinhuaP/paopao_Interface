<?php

namespace app\models;

/**
 * AnchorTitleConfig 主播称号配置
 */
class AnchorTitleConfig extends ModelBase
{

    public $anchor_title_id;
    public $anchor_title_name;
    public $anchor_title_number;
    public $anchor_title_create_time;
    public $anchor_title_update_time;

    /**
     * @param $anchor_title_id
     * @return array
     * 根据id获取 称号值 以及 称号名字
     */
    public static function getInfo($anchor_title_id)
    {
        if ( $anchor_title_id == 0 ) {
            return [
                'number' => '0',
                'name'   => ''
            ];
        }
        $data = self::findFirst([
            'anchor_title_id = :anchor_title_id:',
            'bind'  => [
                'anchor_title_id' => $anchor_title_id
            ],
            'cache' => [
                'lifetime' => 3600,
                'key'      => 'anchor_title:' . $anchor_title_id
            ]
        ]);
        if ( !$data ) {
            return [
                'number' => '0',
                'name'   => ''
            ];
        }
        return [
            'number' => (string)$data->anchor_title_number,
            'name'   => $data->anchor_title_name,
        ];
    }

    public function beforeCreate()
    {
        $this->anchor_title_create_time = time();
        $this->anchor_title_update_time = time();
    }

    public function beforeUpdate()
    {
        $this->anchor_title_update_time = time();
    }

}