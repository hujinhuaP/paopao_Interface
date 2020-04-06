<?php 

namespace app\models;

/**
* UserNameConfig 用户名称
*/
class UserNameConfig extends ModelBase
{
    /** @var string 前缀 */
    const TYPE_PREFIX = 'prefix';
    /** @var string 后缀 */
    const TYPE_SUFFIX = 'suffix';


    public function getRandName()
    {
        return sprintf('%s的%s',$this->getRandPrefix(),$this->getRandSuffix());
    }

    protected function getRandPrefix(){
        // 从缓存中取
        $nRedis = self::getRedis();
        $cacheKey = self::getCacheKey(self::TYPE_PREFIX);
        $prefix = $nRedis->sRandMember($cacheKey);
        if(!$prefix){
            // 从数据库中取出数据
            $oUserNameConfig = UserNameConfig::find([
                'user_name_config_type = :user_name_config_type:',
                'bind' => [
                    'user_name_config_type' => self::TYPE_PREFIX
                ]
            ]);
            if(!$oUserNameConfig){
                return '火星';
            }
            $oUserNameConfig = array_column($oUserNameConfig->toArray(),'user_name_config_value');
            $nRedis->sAddArray($cacheKey,$oUserNameConfig);
            $prefix = $oUserNameConfig[rand(0,count($oUserNameConfig) - 1)];
        }
        return $prefix;
    }


    /**
     * @return array|string
     * 随机后缀
     */
    protected function getRandSuffix(){
        // 从缓存中取
        $nRedis = self::getRedis();
        $cacheKey = self::getCacheKey(self::TYPE_SUFFIX);
        $prefix = $nRedis->sRandMember($cacheKey);
        if(!$prefix){
            // 从数据库中取出数据
            $oUserNameConfig = UserNameConfig::find([
                'user_name_config_type = :user_name_config_type:',
                'bind' => [
                    'user_name_config_type' => self::TYPE_SUFFIX
                ]
            ]);
            if(!$oUserNameConfig){
                return '豆豆';
            }
            $oUserNameConfig = array_column($oUserNameConfig->toArray(),'user_name_config_value');
            $nRedis->sAddArray($cacheKey,$oUserNameConfig);
            $prefix = $oUserNameConfig[rand(0,count($oUserNameConfig) - 1)];
        }
        if(mb_strlen($prefix) == 1){
            $bArr = [
                '阿','小','老'
            ];
            $prefix = $bArr[rand(0,2)] . $prefix;
        }
        return $prefix;
    }
}