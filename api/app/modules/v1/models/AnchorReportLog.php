<?php 

namespace app\models;

/**
* AnchorReportLog 举报主播记录表
*/
class AnchorReportLog extends ModelBase
{
	
	public function beforeCreate()
    {
		$this->anchor_report_log_create_time = time();
		$this->anchor_report_log_update_time = time();
    }

    public function beforeUpdate()
    {
        $this->anchor_report_log_update_time = time();
    }
}