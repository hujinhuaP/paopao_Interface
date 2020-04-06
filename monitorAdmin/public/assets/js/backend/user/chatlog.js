define(['jquery','bootstrap','backend','table','form'],function ($,undefined,Backend,Table,Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'user/chatlog/index',
                    detail_url: '',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                showExport: false,
                columns: [
                    [
                        {field: 'state',checkbox: true},
                        {field: 'id',title: 'ID'},
                        {field: 'send_user.user_id',title: '发送' + __('User ID')},
                        {field: 'send_user.user_nickname', title: '发送' + __('Nickname'), operate: 'LIKE %...%', placeholder: __('Like search'), style: 'width:200px'},
                        {field: 'send_user.user_is_anchor', title: __('发送用户类型'), searchList:{
                                'Y': __('主播'),
                                'N': __('用户'),
                            },
                            formatter: function (value, row, index) {
                                if(value == 'Y'){
                                    return __('主播');
                                }else{
                                    return __('用户');
                                }
                            }
                        },
                        {field: 'get_user.user_id',title: '接收' + __('User ID')},
                        {field: 'get_user.user_nickname', title: '接收' + __('Nickname'), operate: 'LIKE %...%', placeholder: __('Like search'), style: 'width:200px'},
                        {field: 'get_user.user_is_anchor', title: __('接收用户类型'), searchList:{
                                'Y': __('主播'),
                                'N': __('用户'),
                            },
                            formatter: function (value, row, index) {
                                if(value == 'Y'){
                                    return __('主播');
                                }else{
                                    return __('用户');
                                }
                            }
                        },
                        {field: 'user_chat_type', title: __('内容类型'), searchList:{
                                'word': __('文字'),
                                'image': __('图片'),
                                'voice': __('语音'),
                                'gift': __('礼物'),
                                'video': __('视频'),
                            },
                            formatter: function (value, row, index) {
                                switch (value) {
                                    case 'word':
                                        return __('文字');
                                        break;
                                    case 'image':
                                        return __('图片');
                                        break;
                                    case 'voice':
                                        return __('语音');
                                        break;
                                    case 'gift':
                                        return __('礼物');
                                        break;
                                    case 'video':
                                        return __('视频');
                                        break;
                                }
                            }
                        },
                        {field: 'user_chat_content', title: __('Content'), operate: 'LIKE %...%', placeholder: __('Like search'), style: 'width:200px'},
                        {field: 'user_chat_price',title: __('聊天支付价格'),operate:false},
                        {field: 'user_chat_create_time',title: __('Stat time'),sortable:true,operate: 'BETWEEN',defaultValue: $defalutStart+'|'+$defalutEnd,type: 'datetime',addclass: 'datetimepicker',data: 'data-date-format="YYYY-MM-DD HH:mm:ss"',formatter:function (value,row,index) {
                                return Table.api.formatter.datetime.call(this,value,row,index);
                            }},
                        {field: 'user_chat_update_time',title: __('Operate time'),operate: 'BETWEEN',type: 'datetime',addclass: 'datetimepicker',data: 'data-date-format="YYYY-MM-DD HH:mm:ss"',formatter:function (value,row,index) {
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
                pk: 'user_chat_id',
                sortName: 'user_chat_id',
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