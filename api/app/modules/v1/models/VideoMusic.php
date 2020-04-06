<?php 

namespace app\models;

/**
* VideoMusic 分类
*/
class VideoMusic extends ModelBase
{

    public $id;
    public $name;
    public $duration;
    public $author;
    public $cover;
    public $create_time;

	public function beforeCreate()
    {
		$this->create_time = time();
    }

}