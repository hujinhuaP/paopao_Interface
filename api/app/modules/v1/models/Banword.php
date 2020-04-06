<?php 

namespace app\models;

/**
* Banword 禁止的关键字
*/
class Banword extends ModelBase
{

    /** @var string 禁用位置 个人信息 */
    const LOCATION_PROFILE = 'profile';
    /** @var string 禁用位置 动态回复 */
    const LOCATION_POSTS = 'posts';

    public function beforeCreate()
    {
		$this->app_version_create_time = time();
		$this->app_version_update_time = time();
    }

    public function beforeUpdate()
    {
        $this->app_version_update_time = time();
    }

    /**
     * @param $content
     * 过滤禁用词
     */
    public function filterContent($content,$type = 'chat')
    {
        $oBanword = Banword::find([
            'banword_location = :banword_location:',
            'bind' => [
                'banword_location' => $type
            ],
            'columns' => 'banword_content',
            'cache'   => [
                'lifetime' => false,
                'key'      => 'banword_arr:'.$type
            ]
        ]);
        $arr = array_column($oBanword->toArray(),'banword_content');

        foreach($arr as $item){
            $content = str_replace($item,'**',$content);
        }
        return $content;

    }

    /**
     * @param $content
     * @param string $type
     * @return bool
     * 判断是否包含关键词
     */
    public function checkHasBanword($content,$type = 'chat')
    {
        $oBanword = Banword::find([
            'banword_location = :banword_location:',
            'bind' => [
                'banword_location' => $type
            ],
            'columns' => 'banword_content',
            'cache'   => [
                'lifetime' => false,
                'key'      => 'banword_arr:'.$type
            ]
        ]);
        $arr = array_column($oBanword->toArray(),'banword_content');

        $flg = FALSE;
        foreach($arr as $item){
            if(strpos($content,$item) !== false){
                $flg = TRUE;
                break;
            }
        }
        return $flg;
    }
}