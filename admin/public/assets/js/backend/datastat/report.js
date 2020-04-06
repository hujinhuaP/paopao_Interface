define(['jquery','bootstrap','backend','table','form'],function ($,undefined,Backend,Table,Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'datastat/report/index'
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,

                columns: [
                    [
                        {field: 'item_name',operate:false},
                        {field: 'stat_time',title: __('基准日期(今日)'),defaultValue: default_date,type: 'datetime',addclass: 'datetimepicker',data: 'data-date-format="YYYY-MM-DD"',formatter:function (value,row,index) {
                                return row.current_data;
                            }},
                        {field: 'last_data',title: __('昨日'), operate:false},
                        {field: 'seven_data',title: __('7日'), operate:false},
                        {field: 'thirty_data',title: __('30日'), operate:false}
                    ]
                ],

                // templateView: true,
                search: false,
                // showColumns: false,
                // showToggle: false,
                // showExport: false,
                pagination: false,
                commonSearch: true,
                searchFormVisible: false,
                pageSize: 20,
                pk: 'id',
                sortName: 'stat_time',
                sortOrder: 'desc',
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        percent: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'datastat/report/percent'
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,

                columns: [
                    [
                        {field: 'state',checkbox: true},
                        {field: 'stat_time',title: __('Stat time'),sortable:true,operate: 'BETWEEN',defaultValue: $defalutStart+'|'+$defalutEnd,type: 'datetime',addclass: 'datetimepicker',data: 'data-date-format="YYYY-MM-DD"',formatter:function (value,row,index) {
                                return Table.api.formatter.datetime.call(this,value,row,index).slice(0,11);
                            }},
                        {field: 'register_device_count',title: __('新增用户'), operate:false},
                        {field: 'active_user_count',title: __('活跃用户'), operate:false,formatter: function(value,row,index){
                                return value - row.active_anchor_count;
                            }},
                        {field: 'recharge_money_success_count',title: __('充值金额'), operate:false},
                        {field: 'vip_success_money_count',title: __('VIP充值金额'), operate:false},
                        {field: 'active_user_count',title: __('新用户占比'), operate:false,formatter: function(value,row,index){
                                if(value == 0){
                                    return '-';
                                }else{
                                    return parseFloat(parseInt(row.register_device_count) / (parseInt(value) - parseInt(row.active_anchor_count))*100).toFixed(2) + '%';
                                }
                            }},
                        {field: 'active_user_count',title: __('充值总转化率'), operate:false,formatter: function(value,row,index){
                                if(value == 0){
                                    return '-';
                                }else{
                                    return parseFloat(parseInt(row.recharge_success_user_count) / (parseInt(value) - parseInt(row.active_anchor_count))*100).toFixed(2) + '%';
                                }
                            }},
                        {field: 'register_device_count',title: __('新用户充值转化率'), operate:false,formatter: function(value,row,index){
                                if(value == 0){
                                    return '-';
                                }else{
                                    return parseFloat(parseInt(row.register_user_recharge_success_count) / parseInt(value)*100).toFixed(2) + '%';
                                }
                            }},
                        {field: 'active_user_count',title: __('留存用户充值转化率'), operate:false,formatter: function(value,row,index){
                                if(value == 0){
                                    return '-';
                                }else{
                                    return parseFloat( (parseInt(row.recharge_success_user_count) - parseInt(row.register_user_recharge_success_count)) / (parseInt(value) - parseInt(row.active_anchor_count) - parseInt(row.register_device_count)) * 100).toFixed(2) + '%';
                                }
                            }},
                        {field: 'active_user_count',title: __('人均R值'), operate:false,formatter: function(value,row,index){
                                if(value == 0){
                                    return '-';
                                }else{
                                    return parseFloat(parseInt(row.recharge_money_success_count) / (parseInt(value) - parseInt(row.active_anchor_count))).toFixed(2);
                                }
                            }},
                        {field: 'recharge_success_user_count',title: __('客单价'), operate:false,formatter: function(value,row,index){
                                if(value == 0){
                                    return '-';
                                }else{
                                    return parseFloat(parseInt(row.recharge_money_success_count) / parseInt(value)).toFixed(2);
                                }
                            }},
                        {field: 'active_user_count',title: __('VIP充值占比'), operate:false,formatter: function(value,row,index){
                                if(value == 0){
                                    return '-';
                                }else{
                                    return parseFloat(parseInt(row.vip_success_user_count) / (parseInt(value) - parseInt(row.active_anchor_count))*100).toFixed(2) + '%';
                                }
                            }},
                        {field: 'match_chat_total',title: __('匹配成功率'), operate:false,formatter: function(value,row,index){
                                if(value == 0){
                                    return '-';
                                }else{
                                    return parseFloat(parseInt(row.match_chat_success_total) / (parseInt(value))*100).toFixed(2) + '%';
                                }
                            }},
                        {field: 'normal_chat_total',title: __('点播接通率'), operate:false,formatter: function(value,row,index){
                                if(value == 0){
                                    return '-';
                                }else{
                                    return parseFloat(parseInt(row.normal_chat_success_total) / (parseInt(value))*100).toFixed(2) + '%';
                                }
                            }},
                    ]
                ],

                // templateView: true,
                search: false,
                // showColumns: false,
                // showToggle: false,
                // showExport: false,
                commonSearch: true,
                searchFormVisible: false,
                pageSize: 20,
                pk: 'id',
                sortName: 'stat_time',
                sortOrder: 'desc',
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        edit: function () {
            Form.api.bindevent($("form[role=form]"));
        },
        add: function () {
            Form.api.bindevent($("form[role=form]"));

        },
        api: {
            formatter: {
                formatSeconds:function(value){
                    let secondTime = parseInt(value);// 秒
                    let minuteTime = 0;// 分
                    let hourTime = 0;// 小时
                    if(secondTime > 60) {//如果秒数大于60，将秒数转换成整数
                        //获取分钟，除以60取整数，得到整数分钟
                        minuteTime = parseInt(secondTime / 60);
                        //获取秒数，秒数取佘，得到整数秒数
                        secondTime = parseInt(secondTime % 60);
                        //如果分钟大于60，将分钟转换成小时
                        if(minuteTime > 60) {
                            //获取小时，获取分钟除以60，得到整数小时
                            hourTime = parseInt(minuteTime / 60);
                            //获取小时后取佘的分，获取分钟除以60取佘的分
                            minuteTime = parseInt(minuteTime % 60);
                        }
                    }
                    let result = "" + parseInt(secondTime) + "秒";

                    if(minuteTime > 0) {
                        result = "" + parseInt(minuteTime) + "分" + result;
                    }
                    if(hourTime > 0) {
                        result = "" + parseInt(hourTime) + "小时" + result;
                    }
                    return result;
                }
            },
        },
    };
    return Controller;
});