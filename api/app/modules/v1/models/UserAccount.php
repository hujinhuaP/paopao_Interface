<?php 

namespace app\models;

/**
* UserAccount 用户账号
*/
class UserAccount extends ModelBase
{
    /** @var int token有效时间(s) */
	const TOKEN_EXPIRE_TIME = 2592000;

    /** @var string 缓存的key */
    protected static $_key;

	public function beforeCreate()
    {
        $this->user_update_time = time();
        $this->user_create_time = time();
    }

    public function beforeUpdate()
    {
        $this->user_update_time = time();
    }

    public function afterSave()
    {
        $this->getDI()
            ->getShared("modelsCache")
            ->delete(static::$_key);
    }

    public function afterDelete()
    {
        $this->getDI()
            ->getShared("modelsCache")
            ->delete(static::$_key);
    }

    public static function findFirst($parameters = null)
    {
        // Convert the parameters to an array
        if (!is_array($parameters)) {
            $parameters = [$parameters];
        }

        static::$_key = self::_createKey($parameters);

        // Check if a cache key wasn't passed
        // and create the cache parameters
        // if (!isset($parameters['cache'])) {
        //     $parameters['cache'] = [
        //         'key'      => static::$_key,
        //         'lifetime' => 60*60*24,
        //     ];
        // } elseif ($parameters['cache'] == false) {
        //     unset($parameters['cache']);
        // }
        return parent::findFirst($parameters);
    }

    public static function find($parameters = null)
    {
        // Convert the parameters to an array
        if (!is_array($parameters)) {
            $parameters = [$parameters];
        }

        static::$_key = self::_createKey($parameters);

        // Check if a cache key wasn't passed
        // and create the cache parameters
        // if (!isset($parameters['cache'])) {
        //     $parameters['cache'] = [
        //         'key'      => static::$_key,
        //         'lifetime' => 60,
        //     ];
        // } elseif ($parameters['cache'] == false) {
        //     unset($parameters['cache']);
        // }

        return parent::find($parameters);
    }

    /**
     * Implement a method that returns a string key based
     * on the query parameters
     */
    protected static function _createKey($parameters)
    {
        $uniqueKey = [];
        unset($parameters['cache']);
        foreach ($parameters as $key => $value) {
            if (is_scalar($value)) {
                $uniqueKey[] = $key . ':' . $value;
            } elseif (is_array($value)) {
                $uniqueKey[] = $key . ':[' . self::_createKey($value) . ']';
            }
        }

        return 'user:account:data:'.join(',', $uniqueKey);
    }
}