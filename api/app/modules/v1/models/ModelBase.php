<?php 

namespace app\models;

use Phalcon\Mvc\Model;

/**
* ModelBase
*/
class ModelBase extends Model
{

    public static function getLog(){
        $log = \Phalcon\Di::getDefault()->getShared("log");
        return $log;
    }

    /**
     * @return \app\helper\TIM mixed
     */
    public static function getTimServer(){
        $log = \Phalcon\Di::getDefault()->getShared("timServer");
        return $log;
    }


    /**
     * @return \Phalcon\Cache\Frontend\Data
     */
    public static function getModelsCache(){
        $log = \Phalcon\Di::getDefault()->getShared("modelsCache");
        return $log;
    }

    /**
     * @return \Redis
     */
    public static function getRedis(){
        $log = \Phalcon\Di::getDefault()->getShared("redis");
        return $log;
    }

    /**
     */
    public static function getConfig(){
        $config = \Phalcon\Di::getDefault()->getShared("config");
        return $config;
    }


    /**
     * @param string $normal
     * @return string
     */
    public static function getCacheKey( string $normal) {
        return sprintf('%s:%s',get_called_class(),$normal);
    }
    /**
     * @param $data
     *
     * @return bool
     */
    public function saveAll($data){
        $key_arr = array();
        $values_arr = array();
        foreach ($data as &$data_item){
            $key_arr = array_keys($data_item);
            foreach($data_item as $key => $value){
                $data_item[$key] = "'$value'";
//                if(!property_exists($this,$key)){
//                    unset($data_item[$key]);
//                }
            }
            if(empty($key_arr)){
                $key_arr = array_keys($data_item);
            }
            $values_arr[] = '(' . implode(',',$data_item) . ')';
        }
        $key_sql = '(' . implode(',',$key_arr) . ')';
        $value_str = implode(',',$values_arr);

        $source = $this->getSource();
        $sql = "INSERT INTO $source $key_sql values $value_str";
        return $this->getWriteConnection()->execute($sql);
    }

    public function getMessage()
    {
        $messageObj = $this->getMessages();
        return $messageObj[0]->getMessage();
    }


    public static function deleteCache($key)
    {
        $oRedis = self::getRedis();
        return $oRedis->del(sprintf('_PHCRcaches_%s',$key));
    }
}