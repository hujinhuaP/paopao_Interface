<?php

namespace app\tasks;

use Phalcon\Exception;

/**
 * IsrobotTask 机器人服务简单版
 */
class IsrobotTask extends MainTask
{
    /** @var string APP API URL */
    private $_appApiUrl = '';

    /** @var string 机器人是否关闭 */
    private $_isrobotClose = 'N';

    /** @var array 机器人 */
    private $_isrobot = [];

    /** @var array 主播 */
    private $_anchor = [];

    /** @var array 机器人发言 */
    private $_isrobotSpeak = [
        // 进入房间
        'enter' => [

        ],

        // 离开房间
        'leave' => [

        ],

        // 讲话
        'speak' => [
        ],
        // 男主播讲话
        'male' => [
        ],
        // 女主播讲话
        'female' => [
        ],
    ];

    /** @var array 主播ID */
    private $_anchorUserId = [];

    /** @var array 机器人进入的房间ID */
    private $_userRoomId = [];

    /** @var array 房间的机器人 */
    private $_room = [];

    /** @var int 用户等级特效 */
    private $_userLevelEffect = 0;

    /**
     * mainAction
     */
    public function mainAction()
    {
        $this->_appApiUrl = APP_API_URL;

        while (1) {
            try {
                $this->updateDataHandle();

                foreach ($this->_anchorUserId as $nAnchorUserId) {

                    usleep((1000000/count($this->_anchorUserId)));

                    // 进入房间
                    if (mt_rand(0,10000) % 3 == 1 && $this->_isrobotClose == 'N') {
                        $nUserId = array_rand($this->_userRoomId, 1);

                        if ($this->_userRoomId[$nUserId]['anchor_user_id'] == 0) {
                            $this->enterRoomHandle($nUserId, $nAnchorUserId);

                            // 进来讲话
                            if (mt_rand(0, 10) == 0 && !empty($this->_isrobotSpeak['enter'])) {
                                $sContent = $this->_isrobotSpeak['enter'][mt_rand(0, count($this->_isrobotSpeak['enter'])-1)];
                                $this->speakHandle($nUserId, $nAnchorUserId, $sContent);
                            }
                        }


                        // 离开房间
                    } elseif (mt_rand(0,10000) % 5 == 2) {
                        $nUserId = array_rand($this->_userRoomId, 1);
                        if ($this->_userRoomId[$nUserId]['anchor_user_id'] == $nAnchorUserId) {
                            // 离开讲话
                            if (mt_rand(0, 10) == 0 && !empty($this->_isrobotSpeak['leave'])) {
                                $sContent = $this->_isrobotSpeak['leave'][mt_rand(0, count($this->_isrobotSpeak['leave'])-1)];
                                $this->speakHandle($nUserId, $nAnchorUserId, $sContent);
                            }
                            $this->leaveRoomHandle($nUserId, $nAnchorUserId);
                        }

                        // 讲话
                    } elseif (mt_rand(0,10000) % 3 == 1 && $this->_isrobotClose == 'N') {
                        if (!empty($this->_room[$nAnchorUserId])) {
                            $nUserId = array_rand($this->_room[$nAnchorUserId], 1);
                            $nJoinTime = $this->_userRoomId[$nUserId]['join_time'];

                            switch ($this->_anchor[$nAnchorUserId]['user_sex']) {
                                // 男主播讲话
                                case 1:
                                    $aIsrobotSpeak = array_merge($this->_isrobotSpeak['male'], $this->_isrobotSpeak['speak']);
                                    if (mt_rand(0, ((int)((time()-$nJoinTime)/10))) == 0 && !empty($aIsrobotSpeak)) {
                                        $sContent = $aIsrobotSpeak[mt_rand(0, count($aIsrobotSpeak)-1)];
                                        $this->speakHandle($nUserId, $nAnchorUserId, $sContent);
                                    }
                                    break;
                                // 女主播讲话
                                case 2:
                                    $aIsrobotSpeak = array_merge($this->_isrobotSpeak['famale'], $this->_isrobotSpeak['speak']);
                                    if (mt_rand(0, ((int)((time()-$nJoinTime)/10))) == 0 && !empty($aIsrobotSpeak)) {
                                        $sContent = $aIsrobotSpeak[mt_rand(0, count($aIsrobotSpeak)-1)];
                                        $this->speakHandle($nUserId, $nAnchorUserId, $sContent);
                                    }
                                    break;
                                default:
                                    if (mt_rand(0, ((int)((time()-$nJoinTime)/10))) == 0 && !empty($this->_isrobotSpeak['speak'])) {
                                        $sContent = $this->_isrobotSpeak['speak'][mt_rand(0, count($this->_isrobotSpeak['speak'])-1)];
                                        $this->speakHandle($nUserId, $nAnchorUserId, $sContent);
                                    }
                                    break;
                            }
                        }

                        // 下注
                    } else {
                        if (!empty($this->_room[$nAnchorUserId])) {
                            $nUserId = array_rand($this->_room[$nAnchorUserId], 1);
                            $this->gameHandle($nUserId, $nAnchorUserId);
                        }
                    }
                }
            } catch (\PDOException $e) {
                try{
                    $this->db->connect();
                }catch(\PDOException $e){
                    echo $e;
                }
            } catch(Exception $e) {
                echo $e;
            }  catch (\Exception $e) {
                echo $e;
            }
            sleep(1);
            echo "Waiting for the threads to finish...\n";
        }
    }

    /**
     * updateDataHandle 更新数据
     */
    private function updateDataHandle()
    {

        // 判断机器人是否开启
        $sQuerySql = 'SELECT * FROM kv WHERE kv_key="isrobot:is:close"';
        $oResult   = $this->db->query($sQuerySql);
        $oResult->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
        $data = $oResult->fetchAll();
        $this->_isrobotClose = $data[0]['kv_value'];
        if ($this->_isrobotClose == 'Y') {
            return ;
        }

        // 更新机器人
        $sQuerySql = 'SELECT * FROM user WHERE user_is_isrobot="Y" order by user_id desc limit 15000 ';
        $oResult   = $this->db->query($sQuerySql);
        $oResult->setFetchMode(\Phalcon\Db::FETCH_ASSOC);

        $this->_isrobot = [];

        foreach ($oResult->fetchAll() as &$v) {
            $this->_isrobot[$v['user_id']] = $v;
        }

        // 添加机器人
        foreach ($this->_isrobot as $nUserId => $aUser) {
            if (!isset($this->_userRoomId[$nUserId])) {
                $this->_userRoomId[$nUserId]['anchor_user_id'] = '0';
                $this->_userRoomId[$nUserId]['join_time'] = '0';
            };
        }

        $aUserId = array_keys($this->_isrobot);

        // 减少机器人
        foreach ($this->_userRoomId as $nUserId => $v) {
            if (!in_array($nUserId, $aUserId) && $v['anchor_user_id'] != '0') {
                $this->leaveRoomHandle($nUserId, $v['anchor_user_id']);
                unset($this->_userRoomId[$nUserId]);
            } elseif (!in_array($nUserId, $aUserId)) {
                unset($this->_userRoomId[$nUserId]);
            }
        }

        // 更新直播房间
        $sQuerySql     = 'SELECT * FROM anchor WHERE anchor_is_live="Y"';
        $oResult       = $this->db->query($sQuerySql);
        $oResult->setFetchMode(\Phalcon\Db::FETCH_ASSOC);

        $this->_anchor = [];
        $this->_anchorUserId = [];

        foreach (($oResult->fetchAll()) as $v) {
            $this->_anchor[$v['user_id']] = $v;
            $this->_anchorUserId[] = $v['user_id'];
        }


        foreach ($this->_userRoomId as $nUserId => $v) {
            // 直播关闭让机器人退出房间
            if (!in_array($v['anchor_user_id'], $this->_anchorUserId) && $v['anchor_user_id'] != '0') {
                $this->leaveRoomHandle($nUserId, $v['anchor_user_id']);
            }
        }

        // 更新等级特效
        $sQuerySql = 'SELECT user_level FROM user_level_privilege WHERE user_level_privilege_code="enter_room" LIMIT 1';
        $oResult = $this->db->query($sQuerySql);
        $oResult->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
        $data = $oResult->fetchAll();
        $this->_userLevelEffect = $data[0]['user_level'];

        // 更新机器人发言
        $sQuerySql = 'SELECT isrobot_talk_type,isrobot_talk_content FROM isrobot_talk ORDER BY isrobot_talk_id DESC LIMIT 1000';
        $oResult = $this->db->query($sQuerySql);
        $oResult->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
        $data = $oResult->fetchAll();

        $this->_isrobotSpeak['male']   = [];
        $this->_isrobotSpeak['famale'] = [];
        $this->_isrobotSpeak['speak']  = [];

        foreach ($data as $key => $value) {

            switch ($value['isrobot_talk_type']) {
                case 'male':
                    $this->_isrobotSpeak['male'][] = $value['isrobot_talk_content'];
                    break;
                case 'famale':
                    $this->_isrobotSpeak['famale'][] = $value['isrobot_talk_content'];
                    break;
                default:
                    $this->_isrobotSpeak['speak'][] = $value['isrobot_talk_content'];
                    break;
            }
        }

    }

    /**
     * enterRoomHandle 进入房间
     *
     * @param  int $nUserId
     * @param  int $nAnchorUserId
     * @return void
     */
    private function enterRoomHandle($nUserId, $nAnchorUserId)
    {
        echo sprintf("(%s) enter room (%s)\n", $nUserId, $nAnchorUserId);
        $this->_userRoomId[$nUserId]['anchor_user_id'] = $nAnchorUserId;
        $this->_userRoomId[$nUserId]['join_time'] = time();
        $this->_room[$nAnchorUserId][$nUserId] = $nUserId;

        $sUrl = $this->_appApiUrl.'live/room/enter';
        $aParam = [
            'isrobot'        => str_replace(['+', '/', '='], ['.', '_', ''], $this->crypt->encryptBase64((string)$nUserId)),
            'anchor_user_id' => $nAnchorUserId,
        ];

        $this->httpRequest($sUrl, $aParam);

        $sUrl = $this->_appApiUrl.'live/room/userList';
        $this->httpRequest($sUrl, $aParam);

        $sql = 'UPDATE anchor set anchor_live_isrobot_total=:anchor_live_isrobot_total WHERE user_id=:anchor_user_id';
        $this->db->execute($sql, [
            'anchor_live_isrobot_total' => isset($this->_room[$nAnchorUserId]) ? count($this->_room[$nAnchorUserId]) : '0',
            'anchor_user_id' => $nAnchorUserId,
        ]);

        $aUser = $this->_isrobot[$nUserId];

        $aPushMessage= [
            'fuser' => [
                'user' => [
                    'user_avatar'     => $aUser['user_avatar'],
                    'user_level'      => $aUser['user_level'],
                    'is_level_effect' => $aUser['user_level'] > $this->_userLevelEffect ? 'Y' : 'N',
                    'user_id'         => $aUser['user_id'],
                    'user_is_member'  => time() <= $aUser['user_member_expire_time'] ? 'Y' : 'N',
                    'user_nickname'   => $aUser['user_nickname'],
                ],
            ],
            'liveonlines' => 1,
            'reconnect'   => '',
        ];

        // $this->notify($nAnchorUserId, 0, $aPushMessage, 'join');
    }

    /**
     * leaveRoomHandle 离开房间
     *
     * @param  int $nUserId
     * @param  int $nAnchorUserId
     * @return void
     */
    private function leaveRoomHandle($nUserId, $nAnchorUserId)
    {
        echo sprintf("(%s) leave room (%s)\n", $nUserId, $nAnchorUserId);
        $this->_userRoomId[$nUserId]['anchor_user_id'] = '0';
        $this->_userRoomId[$nUserId]['join_time'] = '0';
        unset($this->_room[$nAnchorUserId][$nUserId]);

        $sUrl = $this->_appApiUrl.'live/room/leave';
        $aParam = [
            'isrobot'        => str_replace(['+', '/', '='], ['.', '_', ''], $this->crypt->encryptBase64((string)$nUserId)),
            'anchor_user_id' => $nAnchorUserId,
        ];

        $this->httpRequest($sUrl, $aParam);

        $sUrl = $this->_appApiUrl.'live/room/userList';

        $this->httpRequest($sUrl, $aParam);

        $sql = 'UPDATE anchor set anchor_live_isrobot_total=:anchor_live_isrobot_total WHERE user_id=:anchor_user_id';
        $this->db->execute($sql, [
            'anchor_live_isrobot_total' => isset($this->_room[$nAnchorUserId]) ? count($this->_room[$nAnchorUserId]) : '0',
            'anchor_user_id' => $nAnchorUserId,
        ]);

        $aUser = $this->_isrobot[$nUserId];

        $aPushMessage= [
            'fuser' => [
                'user' => [
                    'user_avatar'     => $aUser['user_avatar'],
                    'user_level'      => $aUser['user_level'],
                    'is_level_effect' => $aUser['user_level'] > $this->_userLevelEffect ? 'Y' : 'N',
                    'user_id'         => $aUser['user_id'],
                    'user_is_member'  => time() <= $aUser['user_member_expire_time'] ? 'Y' : 'N',
                    'user_nickname'   => $aUser['user_nickname'],
                ],
            ],
            'liveonlines' => 1,
        ];

        // $this->notify($nAnchorUserId, 0, $aPushMessage, 'leave');
    }

    /**
     * speakHandle 发言
     */
    private function speakHandle($nUserId, $nAnchorUserId, $sContent)
    {
        echo sprintf("(%s) speak (%s) %s\n", $nUserId, $nAnchorUserId, $sContent);
        $sUrl = $this->_appApiUrl.'live/room/sendChat';
        $aParam = [
            'isrobot'        => str_replace(['+', '/', '='], ['.', '_', ''], $this->crypt->encryptBase64((string)$nUserId)),
            'anchor_user_id' => $nAnchorUserId,
            'content'        => $sContent,
        ];
        $this->httpRequest($sUrl, $aParam);
    }

    /**
     * gameHandle 机器人下注
     *
     * @param  int $nUserId
     * @param  int $nAnchorUserId
     */
    private function gameHandle($nUserId, $nAnchorUserId)
    {
        if (!isset($this->_anchor[$nAnchorUserId])) {
            var_dump($this->_anchor);
            return ;
        }

        $aAnchor = $this->_anchor[$nAnchorUserId];

        switch (strtolower($aAnchor['anchor_game_category_code'])) {
            case 'happycow':
            case 'pirateboat':

                $aParam = [
                    'rid' => $nAnchorUserId,
                    'uid' => $nUserId,
                ];

                $url = $this->config->application->game_api_url.'/status';
                $result = $this->httpRequest($url, $aParam);
                $result = json_decode($result, 1);

                // if ($result['code'] != 200) {
                //     echo sprintf("%s %s\n", date('r'), $result['data']);
                //     return;
                // }

                $aMoney = [
                    10,
                    100,
                    1000,
                    10000,
                ];

                $data = mt_rand(0,2).'_'.$aMoney[array_rand($aMoney, 1)];

                $aParam = [
                    'rid'       => $nAnchorUserId,
                    'uid'       => $nUserId,
                    'data'      => $data,
                    'version'   => 0,
                    'timestamp' => time(),
                ];

                $aSign = [];

                foreach ($aParam as $key => $value) {
                    $aSign[] = $key.'='.$value;
                }

                sort($aSign);

                $aParam['sign'] = md5(implode('', $aSign).'www.hn78game.com');
                $aParam['is_r'] = $nUserId;

                $url = $this->config->application->game_api_url.'/commit';
                var_dump(implode('', $aSign),$this->httpRequest($url, $aParam));

                break;

            default:
                # code...
                break;
        }
    }
}