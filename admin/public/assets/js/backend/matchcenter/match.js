define(['jquery','bootstrap','backend','table','form'],function ($,undefined,Backend,Table,Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'matchcenter/match/index',
                    detail_url: '',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,

                columns: [
                    [
                        {field: 'state',checkbox: true},
                        {field: 'id',title: 'ID'},
                        {field: 'user.user_id',title: __('User ID')},
                        {field: 'user.user_nickname', title: __('Nickname'), operate: 'LIKE %...%', placeholder: __('Like search'), style: 'width:200px'},
                        {field: 'user_type', title: __('用户类型'), searchList:{
                                'new': __('新用户'),
                                'old': __('老用户'),
                            },
                            formatter: function (value, row, index) {
                                if(value == 'new'){
                                    return __('新用户');
                                }else{
                                    return __('老用户');
                                }
                            }
                        },
                        {field: 'duration', title: __('等待时长'), operate: 'BETWEEN'},
                        {field: 'match_success', title: __('是否成功'), searchList:{
                                'Y': __('Success'),
                                'N': __('失败'),
                            },
                            formatter: function (value, row, index) {
                                if(value == 'Y'){
                                    return __('Success');
                                }else{
                                    return __('失败');
                                }
                            }
                        },
                        {field: 'user_private_chat_log.duration',title: __('通话时长'),
                            formatter: function (value, row, index) {
                                if(row.user_private_chat_log.status == 4){
                                    return '通话中';
                                }else if(row.user_private_chat_log.status == 6){
                                    return Controller.api.formatter.formatSeconds(value);
                                }else{
                                    return '--';
                                }
                            }},
                        {field: 'anchor.user_id',title: __('Anchor id')},
                        {field: 'anchor.user_nickname',title: __('Anchor') + __('Nickname'),operate: 'LIKE %...%', placeholder: __('Like search'), style: 'width:200px'},
                        {field: 'anchor_type', title: __('主播类型'), searchList:{
                                'hot': __('红人'),
                                'normal': __('普通'),
                            },
                            formatter: function (value, row, index) {
                                if(row.match_success == 'N'){
                                    return '--';
                                }else{
                                    if(value == 'hot'){
                                        return __('红人');
                                    }else{
                                        return __('普通');
                                    }
                                }
                            }
                        },
                        {field: 'create_time',title: __('开始时间'),operate: 'BETWEEN',type: 'datetime',addclass: 'datetimepicker',data: 'data-date-format="YYYY-MM-DD HH:mm:ss"',formatter:function (value,row,index) {
                                return Table.api.formatter.datetime.call(this,value,row,index);
                            }},
                        {field: 'update_time',title: __('结束时间'),operate: 'BETWEEN',type: 'datetime',addclass: 'datetimepicker',data: 'data-date-format="YYYY-MM-DD HH:mm:ss"',formatter:function (value,row,index) {
                                return Table.api.formatter.datetime.call(this,value,row,index);
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
                pagination: true,
                pageSize: 20,
                pk: 'id',
                sortName: 'id',
                sortOrder: 'desc',
            });

            Table.api.bindevent(table);
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
        }
    };
    return Controller;
});