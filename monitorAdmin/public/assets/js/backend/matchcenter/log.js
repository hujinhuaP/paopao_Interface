define(['jquery','bootstrap','backend','table','form'],function ($,undefined,Backend,Table,Form) {

    var Controller = {
        connecting: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'matchcenter/log/connecting',
                    detail_url: '',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,

                columns: [
                    [
                        {field: 'state',checkbox: true,},
                        {field: 'id',title: 'ID'},
                        {field: 'user.user_id',title: __('User ID')},
                        {field: 'user.user_nickname', title: __('Nickname'), operate: 'LIKE %...%', placeholder: __('Like search'), style: 'width:200px'},
                        {field: 'anchor_user.user_id',title: __('Match user ID')},
                        {field: 'anchor_user.user_nickname', title: __('Match user nickname'), operate: 'LIKE ...%', placeholder: __('Like search'), style: 'width:200px'},
                        {field: 'create_time',title: __('Create time'),operate: 'BETWEEN',type: 'datetime',addclass: 'datetimepicker',data: 'data-date-format="YYYY-MM-DD HH:mm:ss"',formatter:function (value,row,index) {
                                return Table.api.formatter.datetime.call(this,value,row,index);
                            }},
                        {field: 'duration', title: __('Matching duration') +'(s)',operate:false,formatter:function (value, row, index) {
                                return Controller.api.formatter.formatSeconds( row.php_current_time - row.create_time);
                            }},
                        {field: 'duration', title: __('Billing time') +'(min)',operate:false,formatter:function (value, row, index) {
                                return parseInt((parseInt(row.php_current_time) - parseInt(row.create_time))/ 60) + 1;
                            }},
                        {field: 'chat_type', title: __('Match type'), searchList: {
                                'match': __('Random'),
                                'normal': __('Point play'),
                            },
                            events: Table.api.events.operate,
                            formatter:function (value, row, index) {
                                return value == 'match' ? __('Random') : __('Point play');
                            }}
                    ]
                ],

                // templateView: true,
                search: false,
                // showColumns: false,
                // showToggle: false,
                // showExport: false,
                commonSearch: true,
                searchFormVisible: false,
                pagination: true,
                pageSize: 20,
                pk: 'id',
                sortName: 'id',
                sortOrder: 'desc',
            });

            Table.api.bindevent(table);
        },
        history: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'matchcenter/log/history',
                    detail_url: '',
                }
            });

            var table = $("#table");

            // 指定搜索条件
            $(document).on("click", ".btn-singlesearch", function () {
                var options = table.bootstrapTable('getOptions');
                var queryParams = options.queryParams;
                options.pageNumber = 1;
                var data_type = $(this).attr('data-type');
                if(data_type == 'error'){
                    $(this).html("<i class=\"fa fa-user\"></i> 全部");
                    $(this).attr('data-type','all');
                }else{
                    $(this).html("<i class=\"fa fa-user\"></i> 时长与计费时长不匹配");
                    $(this).attr('data-type','error');
                }
                options.queryParams = function (params) {
                    //这一行必须要存在,否则在点击下一页时会丢失搜索栏数据
                    params = queryParams(params);

                    //如果希望追加搜索条件,可使用
                    var filter = params.filter ? JSON.parse(params.filter) : {};
                    var op = params.op ? JSON.parse(params.op) : {};
                    params.filter = JSON.stringify(filter);
                    params.op = JSON.stringify(op);
                    params.error_flg = data_type;

                    //如果希望忽略搜索栏搜索条件,可使用
                    //params.filter = JSON.stringify({url: 'login'});
                    //params.op = JSON.stringify({url: 'like'});
                    return params;
                };
                table.bootstrapTable('refresh', {});
                return false;
            });

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,

                columns: [
                    [
                        {field: 'state',checkbox: true,},
                        {field: 'id',title: 'ID'},
                        {field: 'user.user_id',title: __('User ID')},
                        {field: 'user.user_nickname', title: __('Nickname'), operate: 'LIKE %...%', placeholder: __('Like search'), style: 'width:200px'},
                        {field: 'anchor_user.user_id',title: __('Match user ID')},
                        {field: 'anchor_user.user_nickname', title: __('Match user nickname'), operate: 'LIKE ...%', placeholder: __('Like search'), style: 'width:200px'},
                        {field: 'create_time',title: __('Create time'),defaultValue: $default_start + '|' + $default_end,operate: 'BETWEEN',type: 'datetime',addclass: 'datetimepicker',data: 'data-date-format="YYYY-MM-DD HH:mm:ss"',formatter:function (value,row,index) {
                                return Table.api.formatter.datetime.call(this,value,row,index);
                            }},
                        {field: 'duration',title: __('Matching duration') +'(s)', operate:'BETWEEN',formatter: function(value,row,index){
                                let duration = value;
                                if(value == 0 && row.status == 4){
                                    duration = row.php_current_time - row.create_time;
                                }
                                return Controller.api.formatter.formatSeconds(duration);
                            }},
                        {field: 'timepay_count',title: __('Billing time') +'(min)',operate: 'BETWEEN',formatter: function(value,row,index){
                                if(value > 0 || row.status == 6){
                                    return value;
                                }else{
                                    return parseInt((parseInt(row.php_current_time) - parseInt(row.create_time))/ 60) + 1;
                                }
                        }},
                        {field: 'chat_type', title: __('Match type'), searchList: {
                                'match': __('Random'),
                                'normal': __('Point play'),
                            },
                            events: Table.api.events.operate,
                            formatter:function (value, row, index) {
                                return value == 'match' ? __('Random') : __('Point play');
                            }},
                        {field: 'status', title: __('Status'), searchList: {
                                '4': __('On chat'),
                                '6': __('已挂断'),
                            },
                            events: Table.api.events.operate,
                            formatter:function (value, row, index) {
                                return value == '4' ? __('On chat') : __('已挂断');
                            }},
                        {field: 'hangup_user_id',title: __('Hang up user ID'),operate:false},
                        {field: 'hangup_user_id',title: __('挂断用户类型'),operate:false,formatter: function(value,row,index){
                                if(row.user.user_id == value){
                                    return '用户';
                                }else{
                                    return '主播';
                                }
                            }},
                        {field: 'user_chat_pay.free_times',title: __('用户消耗免费时长'),operate:false},
                        {field: 'user_consume_coin',title: __('用户消费金币'),operate:false,formatter: function(value,row,index){
                                if(row.status == 6){
                                    return value;
                                }else{
                                    return '未结算';
                                }
                            }},
                        {field: 'anchor_get_dot',title: __('主播获得佣金'),operate:false,formatter: function(value,row,index){
                                if(value > 0 || row.status == 6){
                                    return value;
                                }else{
                                    return '未结算';
                                }
                            }},
                         {field: 'hangup_type', title: __('挂断类型'), searchList: {
                                'manual': __('手动'),
                                'auto': __('自动'),
                            },
                            events: Table.api.events.operate,
                            formatter:function (value, row, index) {
                                return value == 'manual' ? __('手动') : __('自动');
                            }},
                        {field: 'detail',title: __('挂断描述'),operate:false}
                    ]
                ],

                // templateView: true,
                search: false,
                // showColumns: false,
                // showToggle: false,
                // showExport: false,
                commonSearch: true,
                searchFormVisible: false,
                pagination: true,
                pageSize: 20,
                pk: 'id',
                sortName: 'id',
                sortOrder: 'desc',
            });

            Table.api.bindevent(table);
        },
        add: function () {
            Form.api.bindevent($("form[role=form]"));

        },api: {
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
        }
    };
    return Controller;
});