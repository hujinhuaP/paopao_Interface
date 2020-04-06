<?php 

namespace app\models;

/**
* UserPrivateChatDialog 一对一私聊对话
*/
class UserPrivateChatDialog extends ModelBase
{
    public $id ;
    public $inviter_id = 0;
    public $invitee_id = 0;
    public $create_time = 0;
    public $update_time = 0;
    public $status = 0;
    public $inviter_unread = 0;
    public $invitee_unread = 0;

	public function beforeCreate()
    {
		$this->create_time = time();
		$this->update_time = time();
    }

    public function addData($inviter_id,$invitee_id,$inChat = FALSE){

        $model = new UserPrivateChatDialog();
        $model->inviter_id = $inviter_id;
        $model->invitee_id = $invitee_id;
        if($inChat){
            $model->status = 1;
        }
        $model->create();
        return $model->id;
    }

    public function getDialogId($inviter_id, $invitee_id, $inChat = FALSE){
        $res = UserPrivateChatDialog::findFirst("(inviter_id = {$inviter_id} and invitee_id={$invitee_id}) or (inviter_id = {$invitee_id} and invitee_id={$inviter_id}) ");

        if(!$res){
            $res = $this->addData($inviter_id,$invitee_id,$inChat);
            return $res;
        }else{
            if($inChat){
                $res->status = 1;
                $res->save();
            }
            return $res->id;
        }

    }

}