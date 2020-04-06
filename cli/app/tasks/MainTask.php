<?php

namespace app\tasks;

use Phalcon\Exception;
use \Phalcon\Cli\Task;

/**
 * MainTask 默认控制器
 * @property \Redis  redis
 */
class MainTask extends Task
{

    private $_key_prefix = 'user_Login:';
    /**
     * APP 请求向量
     */
    const IV = 'XNeNVCmSoP8ZL8PS';

    /**
     * APP 请求秘钥
     */
    const KEY = 'WAJMtRSXs8ezK9LD';


    public function mainAction()
    {
        echo "Congratulations! You are now flying with Phalcon CLI!", "\n";
    }


    /**
     * httpRequest 发送http请求
     * 
     * @param  string $url   
     * @param  array  $param   
     * @param  string $header
     * @return string
     */
    protected function httpRequest($url, $param=NULL, $header=NULL)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);                    //设置header
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        if (!empty($param)) {
            curl_setopt($ch, CURLOPT_POST, 1);                      //post提交方式
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($param));
        }
        if (!empty($header)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER,$header);           // 增加 HTTP Header（头）里的字段
        }
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);        // 终止从服务端进行验证
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        $data = curl_exec($ch);                                 //运行curl
        curl_close($ch);
//        $data = $this->decryptWithOpenssl($data);
        return $data;
    }

    /**
     * notify websocket推送
     * 
     * @param  int $nRid  
     * @param  int $nUid  
     * @param  array  $aData 
     * @param  string $sType 
     * @param  string $sMsg  
     * @return bool
     */
    protected function notify($nRid, $nUid, $aData=[], $sType='', $sMsg='')
    {
        $aMsg = json_encode([
            'type' => $sType,
            'data' => (object)$aData,
        ], JSON_UNESCAPED_UNICODE);

        $param = [
            'rid' => $nRid,
            'uid' => $nUid?: '',
            'msg' => $aMsg,
        ];

        $url = APP_API_URL.'im/notify';

        return $this->httpRequest($url, $param);
    }


    /**
     * @param $endDate
     * @param string $type
     * 获取多天留存
     *
     */
    public function getUserLoginStatData($endDate, $dateCount = 2)
    {
        $startTimestamp = strtotime($endDate);
        $endDateFlg = date('Ymd', strtotime($endDate));
        $keyArr         = [
            $this->_getDataKey($endDateFlg)
        ];

        $startDateFlg = date('Ymd',$startTimestamp - ($dateCount - 1) * 86400);
        $tmpStamp  = $startTimestamp;
        for ( $i = $dateCount; $i > 1; $i-- ) {
            $dateItem = date('Ymd', $tmpStamp - 86400);
            $keyArr[] = $this->_getDataKey($dateItem);
            $tmpStamp -= 86400;
        }
        $newKey = sprintf('%s%sto%s', $this->_key_prefix, $startDateFlg, $endDateFlg);
        switch ( $dateCount ) {
            case 3:
                $this->redis->bitOp('and', $newKey, $keyArr[0], $keyArr[1], $keyArr[2]);
                break;
            case 7:
                $this->redis->bitOp('and', $newKey, $keyArr[0], $keyArr[1], $keyArr[2], $keyArr[3], $keyArr[4], $keyArr[5], $keyArr[6]);
                break;
            case 30:
                $this->redis->bitOp('and', $newKey, $keyArr[0], $keyArr[1], $keyArr[2], $keyArr[3], $keyArr[4], $keyArr[5], $keyArr[6], $keyArr[7], $keyArr[8], $keyArr[9], $keyArr[10], $keyArr[11], $keyArr[12], $keyArr[13], $keyArr[14], $keyArr[15], $keyArr[16], $keyArr[17], $keyArr[18], $keyArr[19], $keyArr[20], $keyArr[21], $keyArr[22], $keyArr[23], $keyArr[24], $keyArr[25], $keyArr[26], $keyArr[27], $keyArr[28], $keyArr[29]);
                break;
            case 2:
            default:
                $this->redis->bitOp('and', $newKey, $keyArr[0], $keyArr[1]);
        }
        $statCount = intval($this->redis->bitCount($newKey));
        if(strtotime($endDate) < strtotime(date('Ymd',time()))){
            // 如果不是以当天为统计的数据 直接删除
            $this->redis->delete($newKey);
        }else{
            $this->redis->expire($newKey, 86400);
        }
        return $statCount;
    }

    protected function _getDataKey($date)
    {
        return sprintf('%s%s', $this->_key_prefix, $date);
    }

    protected function _getUserLoginStatKey($endDate,$dateCount)
    {
        $startTimestamp = strtotime($endDate);
        $endDateFlg = date('Ymd', strtotime($endDate));
        $startDateFlg = date('Ymd',$startTimestamp - ($dateCount - 1) * 86400);

        return sprintf('%s%sto%s', $this->_key_prefix, $startDateFlg, $endDateFlg);
    }

    /**
     *
     */
    public function getAppInfo($flg = ''){
        if(!$flg){
            $flg = 'tianmi';
        }
        $redisKey = sprintf('app_list:%s',$flg);
        $appInfo = $this->redis->hGetAll($redisKey);
        if ( !$appInfo ) {
            // 如果没有信息 那么从数据库查
            $sql = 'SELECT * FROM app_list WHERE app_flg=:app_flg LIMIT 1';

            $oResult = $this->db->query($sql, [
                'app_flg' => $flg,
            ]);
            $oResult->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
            $aAppList = $oResult->fetchAll();
            if ( !$aAppList ) {
                $sql = 'SELECT * FROM app_list WHERE app_flg="paopao" LIMIT 1';

                $oResult = $this->db->query($sql);
                $oResult->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
                $aAppList = $oResult->fetchAll();
            }
            $appInfo  = $aAppList[0];
            $this->redis->hMset($redisKey,$appInfo);
        }
        return $appInfo;
    }

    /**
     * 删除model缓存
     */
    public function deleteRedisModelCacheAction()
    {
        $data = $this->redis->keys('_PHCRcaches_*');
        $flg = $this->redis->delete($data);
        var_dump($flg);die;
    }

    public function deleteDeviceBindAction()
    {
        $sql = 'select min(id) as last_id,count(1) as total from device_active_log group by device_active_device_no having total > 1';
        $oResult = $this->db->query($sql);
        $oResult->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
        $aAppList = $oResult->fetchAll();

        $ids = [];
        foreach ($aAppList as $item){
            $ids[] = $item['last_id'];
        }
        if($ids){
            $idsStr = implode(',',$ids);
            $sql = "delete from device_active_log where id in ({$idsStr})";
            $this->db->execute($sql);
        }
    }

    /**
     * 解密字符串
     * @param string $data 字符串
     * @param string $key 加密key
     * @return string
     */
    public function decryptWithOpenssl($data, $key = self::KEY, $iv = self::IV)
    {
        return openssl_decrypt(base64_decode($data), "AES-128-CBC", $key, OPENSSL_RAW_DATA, $iv);
    }

    /**
     * 加密字符串
     * 参考网站： https://segmentfault.com/q/1010000009624263
     * @param string $data 字符串
     * @param string $key 加密key
     * @return string
     */
    public function encryptWithOpenssl($data, $key = self::KEY, $iv = self::IV)
    {
//        echo base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, "1234567890123456", pkcs7_pad("123456"), MCRYPT_MODE_CBC, "1234567890123456"));
//        echo base64_encode(openssl_encrypt("123456","AES-128-CBC","1234567890123456",OPENSSL_RAW_DATA,"1234567890123456"));
//        $encrypted = mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $data, MCRYPT_MODE_CBC, self::$iv);
//        return base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, "1234567890123456", pkcs7_pad("123456"), MCRYPT_MODE_CBC, "1234567890123456"));
        return base64_encode(openssl_encrypt($data, "AES-128-CBC", $key, OPENSSL_RAW_DATA, $iv));
    }


    public function testAction()
    {
        $client = new \JPush\Client($this->config->push->jpush->app_key, $this->config->push->jpush->master_secret);
        $device   = $client->device();
        $data = $device->getDevices('66667763');
        var_dump($data);die;


        // 从流水表中 统计主播周榜存入缓存
        $sQuerySql = "SELECT user_id as anchor_user_id,SUM(consume) live_gift_dot 
FROM `user_finance_log` WHERE create_time>:time AND user_amount_type = 'dot' and consume > 0 and target_user_id> 0 group by user_id ORDER BY consume desc";

        $result = $this->db->query($sQuerySql, [
            'time' => strtotime(date('Y-m-d 23:59:59', strtotime('last day this week')))+1,
        ]);
        $weekKey = 'ranking:anchor:'.date('o-W');
        $result->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
        foreach ($result->fetchAll() as $key => $row) {
            $this->redis->zAdd($weekKey,floatval(sprintf('%.2f', $row['live_gift_dot'])),$row['anchor_user_id']);
        }
        die;

        // 将主播今日收益 存入周榜统计
        $todayKey = 'anchor:today:dot:'.date('Ymd');
        $weekKey = 'ranking:anchor:'.date('o-W');
        $allData = $this->redis->hGetAll($todayKey);

        $this->redis->delete($weekKey);
        foreach($allData as $userId => $dot){
            $this->redis->zIncrBy($weekKey, $dot, $userId);
        }
    }

}