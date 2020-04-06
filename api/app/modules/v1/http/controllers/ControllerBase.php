<?php

/*
 +------------------------------------------------------------------------+
 | Green Live                                                             |
 +------------------------------------------------------------------------+
 | Copyright (c) 2017-2017 Green Live Team (https://www.greenlive.com)    |
 +------------------------------------------------------------------------+
 | 控制器基类                                                             |
 +------------------------------------------------------------------------+
 |                                      |
 +------------------------------------------------------------------------+
 */

namespace app\http\controllers;

use app\helper\OpensslEncryptHelper;
use app\models\User;
use Phalcon\Exception;
use Phalcon\Mvc\Controller;
use Phalcon\Http\Response\Exception as HttpResponseException;
use Phalcon\Paginator\Adapter\QueryBuilder as Paginator;
use Phalcon\Mvc\Model\Query\Builder;

use app\helper\ResponseError;

/**
 * ControllerBase
 * @property \app\helper\TIM timServer
 * @property \app\helper\LiveService liveServer
 * @property \Redis redis
 */
class ControllerBase extends Controller
{

    public function initialize()
    {

    }


    /** @var User */
    public $oUser = NULL;
    /**
     * getParams 获取参数
     *
     * @return mixed
     */
    public function getParams(... $param)
    {
        $data = call_user_func_array([
            $this->request,
            'get'
        ], $param);
        if($param == 'app_name' && $data == 'yuyin'){
            $data = 'tianmi';
        }
        return $data;
    }

    /**
     * getParams 获取参数
     *
     * @return mixed
     */
    public function getEncodeParams(... $param)
    {
        $data = call_user_func_array([
            $this->request,
            'get'
        ], $param);
        return OpensslEncryptHelper::decryptWithOpenssl($data, OpensslEncryptHelper::APP_KEY . OpensslEncryptHelper::APP_IV);
    }

    /**
     * lang
     * @param  [type] $param [description]
     * @return [type]        [description]
     */
    public function lang(... $param)
    {
        if ( count($param) >= 2 ) {
            array_unshift($param[1], $param[0]);
            $param = $param[1];
        }
        return call_user_func_array('sprintf', $param);
    }

    /**
     * success response
     *
     * @param  string $data
     * @return
     * @throws Phalcon\Http\Response\Exception
     */
    public function success($data = '', $encode = TRUE)
    {
        $format = $this->getParams('format', 'string', 'json');
        if ( $this->request->get('cli_api_key', 'string') == $this->config->application->cli_api_key || APP_ENV == 'dev' ) {
            $encode = false;
        }

        switch ( strtolower($format) ) {
            case 'jsonp':
                $callback = $this->getParams('callback', 'string', 'JSONP');
                $this->response->setHeader('Content-Type', 'text/html');
                $this->response->setHeader('Content-Type', 'application/json;charset=UTF-8');
                $data = json_encode([
                    'c' => ResponseError::SUCCESS,
                    'm' => ResponseError::getError(ResponseError::SUCCESS),
                    'd' => $data ?: new \stdClass(),
                    't' => (string)time()
                ], JSON_UNESCAPED_UNICODE);
                $data = sprintf('%s(%s)', $callback, $data);
                break;

            default:
                $this->response->setHeader('Content-Type', 'text/html');
                $this->response->setHeader('Content-Type', 'application/json;charset=UTF-8');
                $data = json_encode([
                    'c' => ResponseError::SUCCESS,
                    'm' => ResponseError::getError(ResponseError::SUCCESS),
                    'd' => $data ?: new \stdClass(),
                    't' => (string)time()
                ], JSON_UNESCAPED_UNICODE);
                if ( $encode ) {
                    $data = OpensslEncryptHelper::encryptWithOpenssl($data,OpensslEncryptHelper::APP_KEY,OpensslEncryptHelper::APP_IV);
                }
                break;
        }

        // Set the content of the response
        $this->response->setContent($data);
        $this->response->send();
        throw new HttpResponseException();
    }

    /**
     * error response
     *
     * @param  int $nErrorCode
     * @param  string $sErrorMsg
     * @return
     * @throws Phalcon\Http\Response\Exception
     */
    public function error($nErrorCode = 0, $sErrorMsg = '',$encode = TRUE)
    {
        $callback = $this->getParams('callback', 'string', '');
        if ( $this->request->get('cli_api_key', 'string') == $this->config->application->cli_api_key || APP_ENV == 'dev' ) {
            $encode = false;
        }
        switch ( $callback ) {
            case 'jsonp':
                $callback = $this->getParams('callback', 'string', 'JSONP');
                $this->response->setHeader('Content-Type', 'text/html');
                $data = json_encode([
                    'c' => $nErrorCode,
                    'm' => $sErrorMsg ?: ResponseError::getError($nErrorCode),
                    'd' => new \stdClass(),
                    't' => time()
                ], JSON_UNESCAPED_UNICODE);
                $data = sprintf('%s(%s)', $callback, $data);
                break;

            default:
                $this->response->setHeader('Content-Type', 'text/html');
                $data = json_encode([
                    'c' => $nErrorCode,
                    'm' => $sErrorMsg ?: ResponseError::getError($nErrorCode),
                    'd' => new \stdClass(),
                    't' => (string)time()
                ], JSON_UNESCAPED_UNICODE);
                if($encode){
                    $data = OpensslEncryptHelper::encryptWithOpenssl($data,OpensslEncryptHelper::APP_KEY,OpensslEncryptHelper::APP_IV);
                }
                break;
        }

        // Set the content of the response
        $this->response->setContent($data);
        $this->response->send();
        throw new HttpResponseException();
    }

    /**
     * page 分页
     *
     * @param  Phalcon\Mvc\Model\Query\Builder $oBuilder
     * @param  int $nPage
     * @param  int $nPagesize
     * @return
     */
    public function page(Builder $oBuilder, $nPage, $nPagesize)
    {
        $paginator = new Paginator([
            "builder" => $oBuilder,
            "limit"   => $nPagesize,
            "page"    => $nPage,
        ]);
        $page      = $paginator->getPaginate();
        return [
            'items'     => $page->items->toArray(),
            'page'      => $page->current,
            'pagesize'  => $page->limit,
            'pagetotal' => $page->total_pages,
            'total'     => $page->total_items,
            'prev'      => $page->before,
            'next'      => $page->next,
        ];
    }

    public function selfPage($dataSql, $totalSql, $nPage, $nPagesize)
    {
        $res = $this->db->query($dataSql);
        $res->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
        $result = $res->fetchAll();
        $offset = ($nPage - 1) * $nPagesize;
        if ( $totalSql ) {
            $totalResult = $this->db->query($totalSql)->fetch();
            $total       = $totalResult['total_count'] ?? 0;
        } else {
            $total = 1000;
            if ( count($result) < $nPagesize ) {
                $total = $offset + count($result);
            }
        }
        $pageTotal = ceil($total / $nPagesize);
        return [
            'items'     => $result,
            'page'      => $nPage,
            'pagesize'  => $nPagesize,
            'pagetotal' => $pageTotal,
            'total'     => $total,
            'prev'      => $nPage <= 1 ? $nPage : $nPage - 1,
            'next'      => $nPage >= $pageTotal ? $pageTotal : $nPage + 1,
        ];

    }

    /**
     * banword 禁用词
     *
     * @param  string $value
     * @return bool
     */
    public function banword($value = '', $type = 'chat', $returnWord = FALSE)
    {
        $sql    = 'SELECT * FROM banword where banword_location = :banword_location AND :banword_content LIKE CONCAT("%", banword_content, "%") LIMIT 1';
        $result = $this->db->query($sql, [
            'banword_content'  => $value,
            'banword_location' => $type,
        ])->fetchAll();
        if ( $returnWord ) {
            return $result ? $result[0]['banword_content'] : '';
        } else {
            return $result ? TRUE : FALSE;
        }
    }

    public function getCurl($url, $method = 'POST', $data = [], $header = [])
    {
        $ch = curl_init();
        if ( is_array($header) && !empty($header) ) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        }
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); // 信任任何证书
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE); // 检查证书中是否设置域名
        curl_setopt($ch, CURLOPT_TIMEOUT, 3); //3秒超时
        if ( is_array($data) && !empty($data) ) {
            $data = http_build_query($data);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }

    /**
     * 苹果支付验证
     * @param     $receipt_data
     * @param int $sandbox
     *
     * @return mixed
     */
    protected function acurl($receipt_data, $sandbox = 0)
    {
        //小票信息
        $POSTFIELDS = [ "receipt-data" => trim($receipt_data) ];
        $POSTFIELDS = json_encode($POSTFIELDS);

        //正式购买地址 沙盒购买地址
        $url_buy     = "https://buy.itunes.apple.com/verifyReceipt";
        $url_sandbox = "https://sandbox.itunes.apple.com/verifyReceipt";
        $url         = $sandbox ? $url_sandbox : $url_buy;
        $defaults    = [
            CURLOPT_POST           => 1,
            CURLOPT_HEADER         => 0,
            CURLOPT_URL            => $url,
            CURLOPT_FRESH_CONNECT  => 1,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_FORBID_REUSE   => 1,
            CURLOPT_TIMEOUT        => 60,
            CURLOPT_SSL_VERIFYPEER => FALSE,
            CURLOPT_POSTFIELDS     => $POSTFIELDS
            //苹果购买后返回的票据信息
        ];

        $ch = curl_init();
        curl_setopt_array($ch, $defaults);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }

    /**
     * @param $version  用户的版本
     * false 小于  true 等于
     */
    public function checkVersionMatch($version)
    {
        $nowVersion    = '1.0.0';
        $versionArr    = explode('.', $version);
        $newVersionArr = explode('.', $nowVersion);
        if ( !$versionArr ) {
            return FALSE;
        }
        if ( $versionArr[0] > $newVersionArr[0] ) {
            return TRUE;
        } else if ( $versionArr[0] < $newVersionArr[0] ) {
            return FALSE;
        }
        if ( $versionArr[1] > $newVersionArr[1] ) {
            return TRUE;
        } else if ( $versionArr[1] < $newVersionArr[1] ) {
            return FALSE;
        }
        if ( $versionArr[2] >= $newVersionArr[2] ) {
            return TRUE;
        }
        return FALSE;
    }

    public function forceUpdate($nUserId)
    {
        $user_version = $this->redis->hGet('user_app_version', $nUserId);
        $this->log->info($nUserId . '|||' . $user_version);
        if ( !$user_version ) {
            $user_version = $this->getParams('app_version');
            $this->redis->hSet('user_app_version', $nUserId, $user_version);
            if ( !$this->checkVersionMatch($user_version) ) {
                $this->error(10002, '请更新app到最新版本，体验更优哦');
            }
        } else {
            if ( !$this->checkVersionMatch($user_version) ) {
                //如果是存在缓存 那么需要判断是否更新了版本
                $user_version = $this->getParams('app_version');
                $this->redis->hSet('user_app_version', $nUserId, $user_version);
                if ( !$this->checkVersionMatch($user_version) ) {
                    $this->error(10002, '请更新app到最新版本，体验更优哦');
                }
            }
        }
    }

    /**
     * httpRequest 发送http请求
     *
     * @param  string $url
     * @param  array $param
     * @param  string $header
     * @return string
     */
    protected function httpRequest($url, $param = null, $header = null)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);                    //设置header
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        if ( !empty($param) ) {
            curl_setopt($ch, CURLOPT_POST, 1);                      //post提交方式
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($param));
        }
        if ( !empty($header) ) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);           // 增加 HTTP Header（头）里的字段
        }
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);        // 终止从服务端进行验证
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        $data = curl_exec($ch);                                 //运行curl
        curl_close($ch);
//        $data = OpensslEncryptHelper::decryptWithOpenssl($data,OpensslEncryptHelper::APP_KEY,OpensslEncryptHelper::APP_IV);
        return $data;
    }

    /*
     * 开启了debug则 会打印数据
     */
    public function getDump($params)
    {
        if ( $this->getParams('debug') ) {
            echo '<pre>';
            var_dump($params);
        }
    }
}