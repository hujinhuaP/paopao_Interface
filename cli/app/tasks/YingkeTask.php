<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/11/3
 * Time: 13:17
 * desc: 抓取映客热门直播主播，仅第一页
 * 原URL:http://baseapi.busi.inke.cn/live/LiveHotList
 */
namespace app\tasks;

class YingKeTask extends MainTask
{
    public  function getYingKeAnchorAction()
    {
        while(1){
            try{
                //数据库内置了20条数据
                $url = 'http://baseapi.busi.inke.cn/live/LiveHotList';
                //获取映客主播列表
                $anchor_list = self::getAnchorList($url);
                $i = 0;
                $now = time();
                //获取数据库中第三方主播
                $sql = "select user.user_id from user join anchor on anchor.user_id = user.user_id where anchor_type = 2 order by user.user_id asc";
                $anchor = $this->db->query($sql)->fetchAll();
                $category = $this->db->query("select anchor_category_id,anchor_category_name from anchor_category  where anchor_category_status = 'Y' order by anchor_category_id asc limit 1 ")->fetch();
                foreach($anchor as $item){
                    if(empty($anchor_list[$i])){
                        break;
                    }
                    $sex = 1;
                    if($anchor_list[$i]['gender'] == 0){
                        $sex = 2;
                    }
                    $temp_nickname = $anchor_list[$i]['nick'].time().$i;

                    $temp_sql = "update user set user_nickname = '{$temp_nickname}' where user_nickname = '{$anchor_list[$i]['nick']}'";
                    $this->db->execute($temp_sql);

                    $sql = "update user set user_nickname = '{$anchor_list[$i]['nick']}',user_avatar = '{$anchor_list[$i]['avatar']}',user_sex = {$sex} where user_id = {$item['user_id']}";
                    $this->db->execute($sql);
                    $anchor_sql = "update anchor set anchor_local = '{$anchor_list[$i]['live_address']}',
                           anchor_live_play_flv = '{$anchor_list[$i]['down_url']}',
                           anchor_live_title = '{$anchor_list[$i]['live_title']}',
                           anchor_live_img = '{$anchor_list[$i]['avatar']}',
                           anchor_category_id = {$category['anchor_category_id']},
                           anchor_category_name = '{$category['anchor_category_name']}',
                           anchor_hot_time = {$now} WHERE user_id={$item['user_id']}";

                    $this->db->execute($anchor_sql);
                    $api_url = $this->config->application->app_api_url.'live/anchor/createChatRoom?user_id='.$item['user_id'];
                    file_get_contents($api_url);
                    $i++;
                }
                //不够20条补充到20
                while($i < 20){
                    if(empty($anchor_list[$i])){
                        break;
                    }
                    $sex = 1;
                    if($anchor_list[$i]['gender'] == 0){
                        $sex = 2;
                    }
                    $sMicrotime           = sprintf('%.10f', microtime(1));
                    $aTime                = explode('.', $sMicrotime);
                    $sAnchorLiveLogNumber = date('YmdHis', $aTime[0]) . $aTime[1];

                    $query_sql = " select user_id from user where user_nickname = '{$anchor_list[$i]['nick']}' ";
                    $result_query = $this->db->query($query_sql)->fetchAll();
                    if(!empty($result_query)){
                        $anchor_list[$i]['nick'] = $anchor_list[$i]['nick'].rand(10000,99999);
                    }
                    $insert_sql = "INSERT INTO `user` ( `user_nickname`, `user_avatar`,  `user_sex`) VALUES('{$anchor_list[$i]['nick']}','{$anchor_list[$i]['avatar']}',{$sex})";
                    $this->db->execute($insert_sql);
                    $user_id = $this->db->lastInsertId();
                    $insert_anchor = "insert into `anchor` ( `user_id`,`anchor_category_id`, `anchor_category_name`, `anchor_is_live`, `anchor_hot_time`, `anchor_live_title`, `anchor_live_img`, `anchor_local`, `anchor_live_play_flv`, `anchor_type`,`anchor_live_log_number`)
                                 values({$user_id},{$category['anchor_category_id']},'{$category['anchor_category_name']}','Y',{$now},'{$anchor_list[$i]['live_title']}','{$anchor_list[$i]['avatar']}','{$anchor_list[$i]['live_address']}','{$anchor_list[$i]['down_url']}',2,'{$sAnchorLiveLogNumber}')";

                    $this->db->execute($insert_anchor);
                    $api_url = $this->config->application->app_api_url.'live/anchor/createChatRoom?user_id='.$user_id;
                    file_get_contents($api_url);
                    $i++;
                }
                echo " clone YingKe Anchor success"."\n";
            } catch (\Phalcon\Db\Exception $e) {
                try{
                    $this->db->connect();
                }catch(\PDOException $e){
                    echo $e;
                }
            } catch (\PDOException $e) {
                try{
                    $this->db->connect();
                }catch(\PDOException $e){
                    echo $e;
                }
            } catch (\Exception $e) {
                echo $e;
            }
            sleep(300);
        }
    }


    /**
     * POST 请求
     * @param string $url
     * @param array $param
     * @param boolean $post_file 是否文件上传
     * @return string content
     */
    public static function http_post($url, $param = [], $post_file = false, $isJson = false)
    {
        if (is_array($param) && count($param)) {
            $param = json_encode($param);
        }
        $oCurl = curl_init();
        if (stripos($url, "https://") !== FALSE) {
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($oCurl, CURLOPT_SSLVERSION, 1); //CURL_SSLVERSION_TLSv1
        }
        if (is_string($param) || $post_file) {
            $strPOST = $param;
        } else {
            $aPOST = array();
            foreach ($param as $key => $val) {
                $aPOST[] = $key . "=" . urlencode($val);
            }
            $strPOST = join("&", $aPOST);
        }
        if (isset($_SESSION['CURLOPT_HTTPHEADER'])) {
            curl_setopt($oCurl, CURLOPT_HTTPHEADER, $_SESSION['CURLOPT_HTTPHEADER']);
        }
        if ($isJson) {
            curl_setopt($oCurl, CURLOPT_HTTPHEADER, array(
                    'Content-Type: application/json',
                    'Content-Length: ' . strlen($strPOST))
            );
        }
        curl_setopt($oCurl, CURLOPT_URL, $url);
        curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($oCurl, CURLOPT_POST, true);
        curl_setopt($oCurl, CURLOPT_POSTFIELDS, $strPOST);
        $sContent = curl_exec($oCurl);
        $aStatus = curl_getinfo($oCurl);
        curl_close($oCurl);

        if ($sContent) {
            return $sContent;
        } else {
            return false;
        }
    }

    // 映客的热门主播地址为：http://baseapi.busi.inke.cn/live/LiveHotList
    // 仅抓取第11-40个主播
    public static  function getAnchorList($url)
    {
        $html_info = self::http_post($url);
        $decode_html_info = json_decode($html_info,true);
        $anchor_list = [];
        if ($decode_html_info['message'] == 'success') {
            if($decode_html_info['data'][10]){
                $temp_anchor = [];
                for ($i = 11; $i <= 40; $i++) {
                    // 映客主播ID
                    $temp_anchor['third_party_uid'] = $decode_html_info['data'][$i]['uid'];
                    // 映客主播ID房间ID
                    $temp_anchor['third_party_room_id'] = $decode_html_info['data'][$i]['liveid'];
                    // 映客主播头像
                    $temp_anchor['avatar'] = $decode_html_info['data'][$i]['portrait'];
                    // 映客主播昵称
                    $temp_anchor['nick'] = addslashes ($decode_html_info['data'][$i]['nick']);
                    // 映客直播标题
                    $temp_anchor['live_title'] = addslashes ($decode_html_info['data'][$i]['name']);
                    // 映客主播性别
                    $temp_anchor['gender'] = $decode_html_info['data'][$i]['gender'];
                    // 映客直播地点
                    $temp_anchor['live_address'] = addslashes ($decode_html_info['data'][$i]['city']);

                    $live_stream_flv = self::getLiveStream($temp_anchor['third_party_uid'], $temp_anchor['third_party_room_id']);
                    if(empty($live_stream_flv)){
                        continue;
                    }
                    $temp_anchor['down_url'] = $live_stream_flv;

                    array_push($anchor_list, $temp_anchor);
                }
            }else{
                $count = count($decode_html_info['data']);
                for ($i = 0; $i < $count; $i++) {
                    // 映客主播ID
                    $temp_anchor['third_party_uid'] = $decode_html_info['data'][$i]['uid'];
                    // 映客主播ID房间ID
                    $temp_anchor['third_party_room_id'] = $decode_html_info['data'][$i]['liveid'];
                    // 映客主播头像
                    $temp_anchor['avatar'] = $decode_html_info['data'][$i]['portrait'];
                    // 映客主播昵称
                    $temp_anchor['nick'] = addslashes ($decode_html_info['data'][$i]['nick']);
                    // 映客直播标题
                    $temp_anchor['live_title'] = addslashes ($decode_html_info['data'][$i]['name']);
                    // 映客主播性别
                    $temp_anchor['gender'] = $decode_html_info['data'][$i]['gender'];
                    // 映客直播地点
                    $temp_anchor['live_address'] = addslashes ($decode_html_info['data'][$i]['city']);
                    $live_stream_flv = self::getLiveStream($temp_anchor['third_party_uid'], $temp_anchor['third_party_room_id']);
                    if(empty($live_stream_flv)){
                        continue;
                    }
                    $temp_anchor['down_url'] = $live_stream_flv;

                    array_push($anchor_list, $temp_anchor);
                }
            }

        }
        return $anchor_list;
    }

    // 映客获取单个主播地址为：http://baseapi.busi.inke.cn/live/LiveInfo?channel_id=&uid=71167152&liveid=&_t=
    public static  function getLiveStream($ykuid, $ykliveid)
    {
        $process = curl_init('http://baseapi.busi.inke.cn/live/LiveInfo?channel_id=&uid=' . $ykuid . '&liveid=' . $ykliveid . '&_t=');
        curl_setopt($process, CURLOPT_HEADER, 0);
        curl_setopt($process, CURLOPT_TIMEOUT, 30);
        curl_setopt($process, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($process, CURLOPT_FOLLOWLOCATION, 1);
        $return = curl_exec($process);
        curl_close($process);
        $decode_html_info = json_decode($return);
        $url = '';
        if ($decode_html_info->message = 'success') {
            if(isset($decode_html_info->data->live_addr[0])){
                if(isset($decode_html_info->data->live_addr[0]->stream_addr)){
                    $url = $decode_html_info->data->live_addr[0]->stream_addr;
                }
                /*if(isset($decode_html_info->data->live_addr[0]->rtmp_stream_addr)){
                    return $decode_html_info->data->live_addr[0]->rtmp_stream_addr;
                }*/
            }
        }

        //返回空即为已停止直播
        return $url;
    }

}
