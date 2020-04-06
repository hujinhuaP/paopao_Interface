CLI (雨音后台运行脚本)
========================

./bin
    
    chatHeartbeat    —— 一对一视频心跳
    videoChatPay    —— 一对一视频聊天付费
	liveranking —— 排行榜逻辑
	taskQueue   —— 任务队列逻辑
	changeMatchRoom -- 红人匹配转移到普通匹配
	delayoffline -- 聊天中延迟下线
	
定时任务 crontab

        #每日公会收益统计
        1 0 * * * /usr/local/php/bin/php /data/wwwroot/paopao_new/cli/bootstrap/bootstrap.php static groupIncomeStat > /dev/null 2>&1
        #每日公会主播收益统计
        1 0 * * * /usr/local/php/bin/php /data/wwwroot/paopao_new/cli/bootstrap/bootstrap.php static anchorIncomeStat > /dev/null 2>&1
        #更新签约用户在线状态 中午12点转换
        0 12 * * * /usr/local/php/bin/php /data/wwwroot/paopao_new/cli/bootstrap/bootstrap.php chat checkSignAnchorOnlineStatus > /dev/null 2>&1
        # 每日平台统计 需要在公会统计 以及代理商统计之后完成，需要根据公会统计和代理商统计数值 获取数据
        10 0 * * * /usr/local/php/bin/php /data/wwwroot/paopao_new/cli/bootstrap/bootstrap.php static dailyDataStat  > /dev/null 2>&1
        # 每日代理商统计
        1 0 * * * /usr/local/php/bin/php /data/wwwroot/paopao_new/cli/bootstrap/bootstrap.php static dailyAgentStat > /dev/null 2>&1
        # 当天代理商统计
        * * * * * /usr/local/php/bin/php /data/wwwroot/paopao_new/cli/bootstrap/bootstrap.php static todayAgentStat > /dev/null 2>&1
        # 当天平台统计
        50 * * * * /usr/local/php/bin/php /data/wwwroot/paopao_new/cli/bootstrap/bootstrap.php static dailyDataStatToday  > /dev/null 2>&1
        # 根据支付次数判断聊天状态
        * * * * * /usr/local/php/bin/php /data/wwwroot/paopao_new/cli/bootstrap/bootstrap.php chat checkStatusByPay  > /dev/null 2>&1
        # 刷新数据库中记录的当天统计
        0 0 * * * /usr/local/php/bin/php /data/wwwroot/paopao_new/cli/bootstrap/bootstrap.php static refreshTodayMatchDuration  > /dev/null 2>&1
        # 时间段数据统计
        0 * * * * /usr/local/php/bin/php /data/wwwroot/paopao_new/cli/bootstrap/bootstrap.php static intervalStat  > /dev/null 2>&1
        # 检测聊天状态
        * * * * * /usr/local/php/bin/php /data/wwwroot/paopao_new/cli/bootstrap/bootstrap.php chat checkStatus  > /dev/null 2>&1
        # 当天公会收益统计
        */30 * * * * /usr/local/php/bin/php /data/wwwroot/paopao_new/cli/bootstrap/bootstrap.php static todayGroupIncomeStat > /dev/null 2>&1
        # 当天公会主播收益统计
        */30 * * * * /usr/local/php/bin/php /data/wwwroot/paopao_new/cli/bootstrap/bootstrap.php static todayAnchorIncomeStat > /dev/null 2>&1
        # 统计留存
        0 * * * * /usr/local/php/bin/php /data/wwwroot/paopao_new/cli/bootstrap/bootstrap.php static oldDateRetain > /dev/null 2>&1
        # 当天的主播数据统计
        */30 * * * * /usr/local/php/bin/php /data/wwwroot/paopao_new/cli/bootstrap/bootstrap.php static todayAnchorStat > /dev/null 2>&1
        # 每日主播数据统计
        0 0 * * * /usr/local/php/bin/php /data/wwwroot/paopao_new/cli/bootstrap/bootstrap.php static dailyAnchorStat  > /dev/null 2>&1
        # 在线主播状态处理 根据活动记录
        * * * * * /usr/local/php/bin/php /data/wwwroot/paopao_new/cli/bootstrap/bootstrap.php online anchor  > /dev/null 2>&1
        # 财务分类统计
        * * * * * /usr/local/php/bin/php /data/wwwroot/paopao_new/cli/bootstrap/bootstrap.php static dailyRechargeStat > /dev/null 2>&1
        # 当天财务分类统计
        50 * * * * /usr/local/php/bin/php /data/wwwroot/paopao_new/cli/bootstrap/bootstrap.php static dailyRechargeStatToday  > /dev/null 2>&1
        # 每周一 设置上周 主播称号
        0 0 * * 1 /usr/local/php/bin/php /data/wwwroot/paopao_new/cli/bootstrap/bootstrap.php anchor titleChange  > /dev/null 2>&1
        # 定时推送
        0 * * * * /usr/local/php/bin/php /data/wwwroot/paopao_new/cli/bootstrap/bootstrap.php systemmessage crontab  > /dev/null 2>&1
    