<?php 

/*
 +------------------------------------------------------------------------+
 | Green Live                                                             |
 +------------------------------------------------------------------------+
 | Copyright (c) 2017-2017 Green Live Team (https://www.greenlive.com)    |
 +------------------------------------------------------------------------+
 | 腾讯云直播服务                                                         |
 +------------------------------------------------------------------------+
 |                                      |
 +------------------------------------------------------------------------+
 */

namespace app\helper;

/**
* LiveService
*/
class LiveService implements ILiveServer
{

    /** @var string 域名 */
    protected $domainName     = '';

    /** @var string 视频流名 */
    protected $streamName     = '';

    /** @var string 推流名 */
    protected $pushDomainName = '';

    /** @var string BIZID */
    protected $bizid          = '';

    /** @var string 推流验证key */
    protected $pushAuthKey    = '';
    
    /** @var string 直播应用名 */
    protected $appName        = '';
    

    /**
     * setDomainName 设置域名
     * 
     * @param string $value
     */
    public function setDomainName($value='')
    {
        $this->domainName = $value;
    }

    /**
     * setStreamName 设置视频流名
     * 
     * @param string $value
     */
    public function setStreamName($value='')
    {
        $this->streamName = $value;
    }

    /**
     * setPushDomainName 设置推流名
     * 
     * @param string $value
     */
    public function setPushDomainName($value='')
    {
        $this->pushDomainName = $value;
    }

    /**
     * setBizid 设置bizid
     * 
     * @param string $value
     */
    public function setBizid($value='')
    {
        $this->bizid = $value;
    }

    /**
     * setPushAuthKey 设置推流验证key
     * 
     * @param string $value
     */
    public function setPushAuthKey($value='')
    {
        $this->pushAuthKey = $value;
    }

    /**
     * setAppName 设置直播应用名
     * 
     * @param string $value
     */
    public function setAppName($value='')
    {
        $this->appName = $value;
    }

    /**
     * playUrl 获取直播播放地址
     *
     * @param string $format
     * @return string
     */
    public function playUrl($format='')
    {
//        return sprintf('rtmp://%s/live/%s','play.sxypaopao.com',$this->streamName);
        $sLiveCode = $this->bizid.'_'.$this->appName.'_'.$this->streamName;
        $nTxTime   = strtoupper(dechex(strtotime(date('Y-m-d 23:59:59', strtotime('+1 day')))));
        $sTxSecret = md5($this->pushAuthKey . $sLiveCode . $nTxTime);
        switch ($format) {
            case 'flv':
                $url = sprintf('http://%s/live/%s.flv?bizid=%s&txSecret=%s&txTime=%s',
                    $this->domainName,
                    $sLiveCode,
                    $this->bizid,
                    $sTxSecret,
                    $nTxTime
                );
                break;
            case 'm3u8':
                $url = sprintf('http://%s/live/%s.m3u8?bizid=%s&txSecret=%s&txTime=%s',
                    $this->domainName,
                    $sLiveCode,
                    $this->bizid,
                    $sTxSecret,
                    $nTxTime
                );
                break;
            default:
                $url = sprintf('rtmp://%s/live/%s?bizid=%s&txSecret=%s&txTime=%s',
                    $this->domainName,
                    $sLiveCode,
                    $this->bizid,
                    $sTxSecret,
                    $nTxTime
                );
                break;
        }
        return $url;
    }

    /**
     * pushUrl 获取直播推流地址
     *
     * @return string
     */
    public function pushUrl()
    {
//        $push_url = sprintf('rtmp://%s/live/%s','push.sxypaopao.com',$this->streamName);
//        return array('push_url' => $push_url,'ulr'=>$push_url,'key'=>'');

        $sLiveCode = $this->bizid.'_'.$this->appName.'_'.$this->streamName;
        $nTxTime   = strtoupper(dechex(strtotime(date('Y-m-d 23:59:59', strtotime('+1 day')))));
        $sTxSecret = md5($this->pushAuthKey . $sLiveCode . $nTxTime);
        $push_url = sprintf(
            'rtmp://%s/live/%s?bizid=%s&txSecret=%s&txTime=%s',
            $this->pushDomainName,
            $sLiveCode,
            $this->bizid,
            $sTxSecret,
            $nTxTime
        );
        $url = sprintf(
            'rtmp://%s/live/',
            $this->pushDomainName
        );
        $key = sprintf(
            '%s?bizid=%s&txSecret=%s&txTime=%s',
            $sLiveCode,
            $this->bizid,
            $sTxSecret,
            $nTxTime
        );
        return array('push_url' => $push_url,'ulr'=>$url,'key'=>$key);
    }
}