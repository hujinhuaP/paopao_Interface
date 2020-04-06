<?php 

/*
 +------------------------------------------------------------------------+
 | Green Live                                                             |
 +------------------------------------------------------------------------+
 | Copyright (c) 2017-2017 Green Live Team (https://www.greenlive.com)    |
 +------------------------------------------------------------------------+
 | 帮助和反馈控制器                                                       |
 +------------------------------------------------------------------------+
 |                                      |
 +------------------------------------------------------------------------+
 */

namespace app\http\controllers\user;

use Exception;
use app\models\Kv;
use app\models\Question;
use app\models\UserFeedbackLog;
use app\helper\ResponseError;
use app\http\controllers\ControllerBase;

class HelpController extends ControllerBase
{
    use \app\services\SystemMessageService;
	/**
	 * hotQuestionAction 热门问题
	 * 
	 * @param  int $nUserId 
	 */
	public function hotQuestionAction($nUserId=0)
	{
		$sFormat = $this->getParams('format', 'string', 'html');

		if ($sFormat == 'html') {
			header('location: '.APP_WEB_URL.'/?'.http_build_query($this->getParams()));
			return;
		}

		$row['questions'] = Question::find([
			'question_status="Y" order by question_sort desc',
			'columns' => 'question_id as id,question_title as title,question_create_time as time,question_content as content',
		]);
		$this->success($row,FALSE);
	}

	/**
	 * questionAction 问题详情
	 * 
	 * @param  int $nUserId
	 */
	public function questionAction($nUserId=0)
	{
		$nQuestionId = $this->getParams('question_id', 'int', 0);
		$sFormat = $this->getParams('format', 'string', 'html');

		if ($sFormat == 'html') {
			header('location: '.APP_WEB_URL.'/details?'.http_build_query($this->getParams()));
			return;
		}

		try {
			$oQuestion = Question::findFirst($nQuestionId);

			if (!$oQuestion) {
				throw new Exception(
                    sprintf('question_id %s', ResponseError::getError(ResponseError::PARAM_ERROR)),
                    ResponseError::PARAM_ERROR
                );
			}

			$row['question']['id']     = $oQuestion->question_id;
			$row['question']['title']  = $oQuestion->question_title;
			$row['question']['answer'] = $oQuestion->question_content;
			$row['question']['time']   = $oQuestion->question_create_time;
			$row['contact_link']       = Kv::get(Kv::KEY_CONTACT_US);

		} catch (Exception $e) {
			$this->error($e->getCode(), $e->getMessage());
		}
		
		$this->success($row,FALSE);
	}

	/**
	 * feedbackAction 反馈
	 * 
	 * @param  int $nUserId
	 */
	public function feedbackAction($nUserId=0)
	{
		$sFormat   = $this->getParams('format', 'string', 'html');
		$sImages   = $this->getParams('images', 'string', '');
		$sContent  = $this->getParams('content', 'string', '');
		$sUserLink = $this->getParams('user_link', 'string', '');
        $sAccessToken = $this->request->get('access_token', 'string', '');
        if ( $sAccessToken == '' ) {
            $sAccessToken = $this->request->getServer('HTTP_AUTHORIZATION');
        }
		try {
//            if(!$nUserId){
//                throw new Exception(
//                    ResponseError::getError(ResponseError::ACCESS_TOKEN_INVALID),
//                    ResponseError::ACCESS_TOKEN_INVALID
//                );
//            }
            if($sAccessToken){
                $auth = explode('-', $this->crypt->decryptBase64(str_replace([
                    '.',
                    '_'
                ], [
                    '+',
                    '/'
                ], urldecode($sAccessToken))));
                $nUserId = isset($auth[0]) ? $auth[0] : $nUserId;
            }

			if ($sContent == '') {
				throw new Exception(
                    sprintf('content %s', ResponseError::getError(ResponseError::PARAM_ERROR)),
                    ResponseError::PARAM_ERROR
                );
			}

			$oUserFeedbackLog = new UserFeedbackLog();
			$oUserFeedbackLog->user_id                   = $nUserId;
			$oUserFeedbackLog->user_feedback_log_content = $sContent;
			$oUserFeedbackLog->user_link                 = $sUserLink;
			$oUserFeedbackLog->user_feedback_log_images  = $sImages;
			$oUserFeedbackLog->save();

		} catch (Exception $e) {
			$this->error($e->getCode(), $e->getMessage());
		}
		if($nUserId){
            $this->sendFeedbackMsg($nUserId);
        }
		$this->success();
	}

}