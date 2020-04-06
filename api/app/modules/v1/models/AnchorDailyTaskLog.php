<?php

namespace app\models;

/**
 * AnchorDailyTaskLog 主播每日任务记录
 */
class AnchorDailyTaskLog extends ModelBase
{

    use \app\services\UserService;

    public function beforeCreate()
    {
        $this->anchor_daily_task_log_create_time = time();
        $this->anchor_daily_task_log_update_time = time();
    }

    public function beforeUpdate()
    {
        $this->anchor_daily_task_log_update_time = time();
    }

    /**
     * @param $user_id
     * 获取用户的日常任务完成数
     */
    public static function getInfo($user_id)
    {
        // 每日任务信息
        $today        = date('Y-m-d');
        $taskType     = TaskConfig::TASK_TYPE_ANCHOR_DAILY;
        $dailyTaskSql = "select tc.task_name,tc.task_flg,tc.task_finish_times,tc.task_reward_dot,tc.task_reward_exp,l.anchor_daily_task_log_id,tc.task_type FROM task_config as tc left join anchor_daily_task_log as l on 
tc.task_id = l.anchor_daily_task_id AND l.anchor_daily_task_date = '{$today}' AND anchor_daily_task_log_user_id = {$user_id}
WHERE tc.task_on = 'Y' AND tc.task_type = '{$taskType}'";

        $oTaskConfig = new TaskConfig();
        $connection  = $oTaskConfig->getReadConnection();
        $res         = $connection->query($dailyTaskSql);
        $res->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
        $taskData                  = $res->fetchAll();
        $daily_task_finished_count = 0;

        foreach ( $taskData as $taskItem ) {
            // 已经领过
            $daily_task_finished_count += 1;
            if ( !$taskItem['anchor_daily_task_log_id'] ) {
                // 没有完成需要 判断完成进度
                $oTaskConfig->task_finish_times = $taskItem['task_finish_times'];
                $oTaskConfig->task_flg          = $taskItem['task_flg'];
                $oTaskConfig->task_type         = $taskItem['task_type'];
                $taskFinished                   = $oTaskConfig->getTaskFinishDone($user_id);
                if ( $taskFinished !== TRUE ) {
                    // 未完成  进度获取值
                    $daily_task_finished_count -= 1;
                }
            }
        }
        $totalTaskCount = count($taskData);

        return [
            'daily_total_count'  => $totalTaskCount,
            'daily_finish_count' => $daily_task_finished_count
        ];
    }

}