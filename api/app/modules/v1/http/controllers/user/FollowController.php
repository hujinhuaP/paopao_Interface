<?php 

/*
 +------------------------------------------------------------------------+
 | Green Live                                                             |
 +------------------------------------------------------------------------+
 | Copyright (c) 2017-2017 Green Live Team (https://www.greenlive.com)    |
 +------------------------------------------------------------------------+
 | 用户关注控制器                                                         |
 +------------------------------------------------------------------------+
 |                                      |
 +------------------------------------------------------------------------+
 */

namespace app\http\controllers\user;

use Exception;


use app\models\User;
use app\models\Anchor;
use app\models\UserFollow;
use app\helper\ResponseError;
use app\services\RoomFollowService;
use app\http\controllers\ControllerBase;

/**
* FollowController 粉丝关注
*/
class FollowController extends ControllerBase
{
    use \app\services\SystemMessageService;

    /**
     * addAction 添加关注
     * 
     * @param  int $nUserId
     */
    public function addAction($nUserId=0)
    {
        $nToUserId = $this->getParams('user_id', 'int', 0);
        $nAnchorUserId = $this->getParams('anchor_user_id', 'int', 0);

        try {

            if ($nToUserId == 0 || $nToUserId == $nUserId) {
                throw new Exception(
                    sprintf('%s %s', 'user_id', ResponseError::getError(ResponseError::PARAM_ERROR)),
                    ResponseError::PARAM_ERROR
                );
            }

            $oUserFollow = UserFollow::findFirst([
                'user_id=:user_id: and to_user_id=:to_user_id:',
                'bind' => [
                    'user_id'=>$nUserId,
                    'to_user_id'=>$nToUserId,
                ]
            ]);

            if ($oUserFollow) {
                throw new Exception(ResponseError::getError(ResponseError::IS_FOLLOW), ResponseError::IS_FOLLOW);
            }

            $oUserFollow = new UserFollow();
            $oUserFollow->user_id = $nUserId;
            $oUserFollow->to_user_id = $nToUserId;

            if ($oUserFollow->save() === FALSE) {
                throw new Exception(
                    sprintf('%s[%s]', ResponseError::getError(ResponseError::OPERATE_FAILED), implode(',', $oUserFollow->getMessages())),
                    ResponseError::OPERATE_FAILED
                );
            }

            $oUser = User::findFirst($nUserId);
            $oUser->user_follow_total = UserFollow::count(['user_id=:user_id:', 'bind'=>['user_id'=>$nUserId]]);
            if ($oUser->save() === FALSE) {
                throw new Exception(
                    sprintf('%s[%s]', ResponseError::getError(ResponseError::OPERATE_FAILED), implode(',', $oUser->getMessages())),
                    ResponseError::OPERATE_FAILED
                );
            }

            // 发送关注消息
            $this->sendFollowMsg($nToUserId, $oUser);
            //往直播间推送消息
            if ($nToUserId == $nAnchorUserId) {
                //发送消息的人的信息
                $oSendUser = User::findFirst(['user_id =:user_id:','bind'=>['user_id'=>$nUserId]]);
                $row['user_nickname'] = $oSendUser->user_nickname;
                $row['user_id'] = $nUserId;
                $this->timServer->setRid($nAnchorUserId);
                $this->timServer->setUid('');
                $this->timServer->sendFollowAdd(['user' =>$row]);
            }

            $oUser = User::findFirst($nToUserId);

            if($oUser){
                $oUser->user_fans_total = UserFollow::count(['to_user_id=:to_user_id:', 'bind'=>['to_user_id'=>$nToUserId]]);
                if ( $oUser->save() === FALSE) {
                    throw new Exception(
                        sprintf('%s[%s]', ResponseError::getError(ResponseError::OPERATE_FAILED), implode(',', $oUser->getMessages())),
                        ResponseError::OPERATE_FAILED
                    );
                }
            }

            // 记录本场直播新增关注的用户
            if ($nToUserId == $nAnchorUserId) {
                $oAnchor = Anchor::findFirst([
                    'user_id=:user_id:',
                    'bind' => [
                        'user_id' => $nToUserId,
                    ]
                ]);
            }

        } catch (Exception $e) {
            $this->error($e->getCode(), $e->getMessage());
        }
        $this->success();
    }
    
    /**
     * deleteAction 取消关注
     * 
     * @param  int $nUserId
     */
    public function deleteAction($nUserId=0)
    {
        $nToUserId = $this->getParams('user_id', 'int', 0);

        try {

            if ($nToUserId == 0 || $nToUserId == $nUserId) {
                throw new Exception(
                    sprintf('%s %s', 'user_id', ResponseError::getError(ResponseError::PARAM_ERROR)),
                    ResponseError::PARAM_ERROR
                );
            }

            $oUserFollow = UserFollow::findFirst([
                'user_id=:user_id: and to_user_id=:to_user_id:',
                'bind' => [
                    'user_id'=>$nUserId,
                    'to_user_id'=>$nToUserId,
                ]
            ]);

            if ($oUserFollow === FALSE) {
                throw new Exception(ResponseError::getError(ResponseError::CANCEL_FOLLOW), ResponseError::CANCEL_FOLLOW);
            }
            
            if ($oUserFollow->delete() === FALSE) {
                throw new Exception(
                    sprintf('%s[%s]', ResponseError::getError(ResponseError::OPERATE_FAILED), implode(',', $oUserFollow->getMessages())),
                    ResponseError::OPERATE_FAILED
                );
            }

            $oUser = User::findFirst($nUserId);
            $oUser->user_follow_total = UserFollow::count(['user_id=:user_id:', 'bind'=>['user_id'=>$nUserId]]);
            if ($oUser->save() === FALSE) {
                throw new Exception(
                    sprintf('%s[%s]', ResponseError::getError(ResponseError::OPERATE_FAILED), implode(',', $oUser->getMessages())),
                    ResponseError::OPERATE_FAILED
                );
            }

            $oUser = User::findFirst($nToUserId);
            $oUser->user_fans_total = UserFollow::count(['to_user_id=:to_user_id:', 'bind'=>['to_user_id'=>$nToUserId]]);
            if ($oUser->save() === FALSE) {
                throw new Exception(
                    sprintf('%s[%s]', ResponseError::getError(ResponseError::OPERATE_FAILED), implode(',', $oUser->getMessages())),
                    ResponseError::OPERATE_FAILED
                );
            }


        } catch (Exception $e) {
            $this->error($e->getCode(), $e->getMessage());
        }
        $this->success();
    }

    /**
     * followsAction 关注列表
     * 
     * @param  int $nUserId
     */
    public function followsAction($nUserId=0)
    {
        $nToUserId = $this->getParams('user_id', 'int', $nUserId);
        $nPage     = $this->getParams('page', 'int', 0);
        $nPagesize = $this->getParams('pagesize', 'int', 20);

        try {
            $builder = $this->modelsManager
                            ->createBuilder()
                            ->from(['tuf'=>UserFollow::class])
                            ->join(User::class, 'u.user_id=tuf.to_user_id', 'u')
                            ->leftJoin(Anchor::class, 'a.user_id=u.user_id', 'a')
                            ->leftJoin(UserFollow::class, sprintf('uf.to_user_id=u.user_id AND uf.user_id="%d"', $nUserId), 'uf')
                            ->columns('u.user_id,u.user_nickname,u.user_sex,u.user_avatar,u.user_level,uf.to_user_id is_follow,
                            u.user_fans_total,u.user_follow_total,u.user_member_expire_time,u.user_birth')
                            ->where('tuf.user_id=:user_id:', ['user_id'=>$nToUserId]);

            $row['follows'] = $this->page($builder, $nPage, $nPagesize);
            
            foreach ($row['follows']['items'] as &$v) {
                $v['is_follow']    = $v['is_follow'] ? 'Y' : 'N';
                $v['user_is_member'] = $v['user_member_expire_time'] == 0 ? 'N' : (time() > $v['user_member_expire_time'] ? 'O' : 'Y');
            }

            $oUser = User::findFirst($nUserId);

            $row['is_remind'] = $oUser->user_remind ? 'Y' : 'N';

        } catch (Exception $e) {
            $this->error($e->getCode(), $e->getMessage());
        }
        $this->success($row);
    }


    /**
     * fansAction 粉丝列表
     * 
     * @param int $nUserId
     */
    public function fansAction($nUserId=0)
    {
        $nToUserId   = $this->getParams('user_id', 'int', $nUserId);
        $nPage     = $this->getParams('page', 'int', 0);
        $nPagesize = $this->getParams('pagesize', 'int', 20);

        try {
            $builder = $this->modelsManager
                            ->createBuilder()
                            ->from(['tuf'=>UserFollow::class])
                            ->join(User::class, 'u.user_id=tuf.user_id', 'u')
                            ->leftJoin(Anchor::class, 'a.user_id=u.user_id', 'a')
                            ->leftJoin(UserFollow::class, sprintf('uf.to_user_id=u.user_id AND uf.user_id="%d"', $nUserId), 'uf')
                            ->columns('u.user_id,u.user_nickname,u.user_sex,u.user_avatar,u.user_level,
                            uf.to_user_id is_follow,u.user_fans_total,u.user_follow_total,u.user_member_expire_time,u.user_birth')
                            ->where('tuf.to_user_id=:to_user_id:', ['to_user_id'=>$nToUserId]);

            $row['fans'] = $this->page($builder, $nPage, $nPagesize);
            
            foreach ($row['fans']['items'] as &$v) {
                $v['is_follow']    = $v['is_follow'] ? 'Y' : 'N';
                $v['user_is_member'] = $v['user_member_expire_time'] == 0 ? 'N' : (time() > $v['user_member_expire_time'] ? 'O' : 'Y');
            }
        } catch (Exception $e) {
            $this->error($e->getCode(), $e->getMessage());
        }
        $this->success($row);
    }

    /**
     * remindsAction 我的开播提醒列表
     * 
     * @param  int $nUserId
     */
    public function remindsAction($nUserId=0)
    {
        $nPage = $this->getParams('page', 'int', 0);
        $nPagesize = $this->getParams('pagesize', 'int', 20);

        try {
            $builder = $this->modelsManager
                            ->createBuilder()
                            ->from(['uf'=>UserFollow::class])
                            ->join(User::class, 'u.user_id=uf.to_user_id', 'u')
                            ->leftJoin(Anchor::class, 'a.user_id=uf.to_user_id', 'a')
                            ->columns('u.user_avatar,u.user_level,u.user_id,u.user_nickname,u.user_intro,uf.user_follow_is_remind is_remind')
                            ->where('uf.user_id=:user_id:', ['user_id'=>$nUserId]);

            $row['follows'] = $this->page($builder, $nPage, $nPagesize);

            $row['remind_total'] = $this->modelsManager
                            ->createBuilder()
                            ->from(UserFollow::class)
                            ->columns('COUNT(*) total')
                            ->where('user_id=:user_id:', ['user_id'=>$nUserId])
                            ->andWhere('user_follow_is_remind="Y"')
                            ->getQuery()
                            ->execute()
                            ->getFirst()
                            ->total;

            $oUser = User::findFirst($nUserId);
            $row['is_remind'] = $oUser->user_remind == 1 ? 'Y' : 'N';
            

        } catch (Exception $e) {
            $this->error($e->getCode(), $e->getMessage());
        }

        $this->success($row);
    }

    /**
     * updateRemind 修改提醒
     * 
     * @param int $nUserId
     */
    public function updateRemindAction($nUserId=0)
    {
        
        $sIsRemind = $this->getParams('is_remind', 'string', 'Y');
        $nToUserId = $this->getParams('user_id', 'int', 0);

        try {
            
            if ($nToUserId == 0) {

                $phql = sprintf("UPDATE %s SET user_remind=:is_remind: WHERE user_id=:user_id:", User::class);

                $this->modelsManager->executeQuery($phql, [
                    'is_remind'  => $sIsRemind == 'Y' ? 1 : 0,
                    'user_id'    => $nUserId,
                ]);

            } else {

                $phql = sprintf("UPDATE %s SET user_follow_is_remind=:is_remind: WHERE user_id=:user_id: AND to_user_id=:to_user_id:", UserFollow::class);

                $this->modelsManager->executeQuery($phql, [
                    'is_remind'  => $sIsRemind,
                    'user_id'    => $nUserId,
                    'to_user_id' => $nToUserId,
                ]);
            }

        } catch (Exception $e) {
            $this->error($e->getCode(), $e->getMessage());
        }

        $this->success();
    }
}