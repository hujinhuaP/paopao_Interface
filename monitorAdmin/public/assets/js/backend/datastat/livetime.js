define(['jquery','bootstrap','backend','table','form'],function ($,undefined,Backend,Table,Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'datastat/livetime/index',
                    detail_url: 'datastat/livetime/detail',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,

                columns: [
                    [
                        {field: 'state',checkbox: true,},
                        {field: 'stat_date',title: __('Stat time'),sortable:true,operate: 'BETWEEN',type: 'datetime',addclass: 'datetimepicker',data: 'data-date-format="YYYY-MM-DD"',formatter:function (value,row,index) {
                                return Table.api.formatter.datetime.call(this,value,row,index).slice(0,11);
                            }},
                        {field: 'user_id',title: __('User ID')},
                        {field: 'user.user_nickname', title: __('Username'), operate: 'LIKE %...%', placeholder: __('Like search'), style: 'width:200px'},
                        {field: 'group.group_name', title: __('Group name'), operate:false},
                        {field: 'anchor_sign_live_start_time', title: __('Sign online time'),operate:false,formatter: function (value, row, index, custom) {
                                let start_time = row.anchor_sign_live_start_time;
                                let end_time = row.anchor_sign_live_end_time;
                                if(start_time == 0){
                                    start_time = 24;
                                }
                                if(start_time < 10){
                                    start_time = '0' + start_time;
                                }
                                if(end_time == 0){
                                    end_time = 24;
                                }
                                if(end_time < 10){
                                    end_time = '0' + end_time;
                                }
                                return start_time + ':00 - ' + end_time + ':00';
                            }},
                        {field: 'online_duration',title: __('Online duration'), operate:false,formatter: function(value,row,index){
                                return Controller.api.formatter.formatSeconds(value);
                            }},
                        {field: 'affect_online_duration',title: __('Affect online duration'), operate:false,formatter: function(value,row,index){
                                return Controller.api.formatter.formatSeconds(value);
                            }},
                        {field: 'affect_called_times',title: __('Affect called times'), operate:false},
                        {field: 'affect_call_times',title: __('Affect call times'), operate:false},
                        {field: 'affect_receive_user_count',title: __('Affect count of user which anchor receive msg'), operate:false},
                        {field: 'affect_reply_user_count',title: __('Affect count of user which anchor reply msg'), operate:false},
                        {field: 'operate',title: __('Operate'),table: table,
                            events: Table.api.events.operate,
                            buttons: [
                            ],
                            formatter: function (value,row,index) {
                                this.table.data('operate-dragsort',false);
                                row[Table.config.dragsortfield] = 0;
                                return Table.api.formatter.operate.call(this,value,row,index);
                            }}

                    ]
                ],

                // templateView: true,
                // search: false,
                // showColumns: false,
                // showToggle: false,
                // showExport: false,
                commonSearch: true,
                searchFormVisible: false,
                pageSize: 20,
                pk: 'id',
                sortName: 'stat_date',
                sortOrder: 'desc',
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        detail: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'datastat/livetime/detail/ids/'+stat_id,
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,

                columns: [
                    [
                        {field: 'state',checkbox: true,},
                        {field: 'user_id',title: __('User ID')},
                        {field: 'user.user_nickname', title: __('Username'), operate: 'LIKE %...%', placeholder: __('Like search'), style: 'width:200px'},
                        {field: 'online_time',title: __('Online time'),operate: 'BETWEEN',type: 'datetime',addclass: 'datetimepicker',data: 'data-date-format="YYYY-MM-DD HH:mm:ss"',formatter:function (value,row,index) {
                                return Table.api.formatter.datetime.call(this,value,row,index);
                            }},
                        {field: 'offline_time',title: __('Offline time'),operate: 'BETWEEN',type: 'datetime',addclass: 'datetimepicker',data: 'data-date-format="YYYY-MM-DD HH:mm:ss"',formatter:function (value,row,index) {
                                return Table.api.formatter.datetime.call(this,value,row,index);
                            }},
                        {field: 'duration',title: __('Online duration'), operate:false,formatter: function(value,row,index){
                                return Controller.api.formatter.formatSeconds(value);
                            }},
                    ]
                ],

                // templateView: true,
                search: false,
                // showColumns: false,
                // showToggle: false,
                // showExport: false,
                commonSearch: false,
                searchFormVisible: false,
                pagination: false,
                pageSize: 20,
                pk: 'id',
                sortName: 'id',
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