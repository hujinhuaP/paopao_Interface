define(['jquery','bootstrap','backend','table','form'],function ($,undefined,Backend,Table,Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'user/invitereward/index'
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,

                columns: [
                    [
                        {field: 'state',checkbox: true},
                        {field: 'invite_user.user_id',title: __('user ID')},
                        {field: 'invite_user.user_nickname', title: __('Nickname'), operate: 'LIKE %...%', placeholder: __('Like search'), style: 'width:200px'},
                        {field: 'user.user_id',title: __('被邀请用户ID')},
                        {field: 'user.user_nickname', title: __('被邀请用户昵称'), operate: 'LIKE %...%', placeholder: __('Like search'), style: 'width:200px'},
                        {field: 'user_recharge_combo_fee',title: __('被邀请用户充值金额（元）'), operate:false},
                        {field: 'invite_user.user_is_anchor', title: __('邀请用户类型'), searchList:{
                                'Y' : __('Anchor'),
                                'N' : __('用户'),
                            },
                            events: Table.api.events.operate,
                            formatter:function (value, row, index) {
                                return value == 'Y' ? __('Anchor') : __('用户');
                            }},
                        {field: 'invite_ratio',title: __('奖励比例')},
                        {field: 'recharge_invite_coin',title: __('邀请用户所获得奖励'), operate:false,
                            formatter: function (value, row, index) {
                                if(row.recharge_invite_coin > 0){
                                    return row.recharge_invite_coin;
                                }else{
                                    return row.recharge_invite_dot;
                                }
                            }},
                        {field: 'user_invite_reward_log_create_time', title: __('Operate time'), operate: 'BETWEEN', type: 'datetime', addclass: 'datetimepicker', data: 'data-date-format="YYYY-MM-DD HH:mm:ss"', formatter:function (value, row, index) {
                                return Table.api.formatter.datetime.call(this, value, row, index);
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
                pageSize: 20,
                pk: 'user_invite_reward_log_id',
                sortName: 'user_invite_reward_log_id',
                sortOrder: 'desc',
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        user: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'datastat/expand/user'
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,

                columns: [
                    [
                        {field: 'state',checkbox: true},
                        {field: 'stat_time',title: __('Stat time'),sortable:true,operate: 'BETWEEN',type: 'datetime',addclass: 'datetimepicker',data: 'data-date-format="YYYY-MM-DD"',formatter:function (value,row,index) {
                                return Table.api.formatter.datetime.call(this,value,row,index).slice(0,11);
                            }},
                        {field: 'user_invite_device_count',title: __('普通用户邀请人数'), operate:false},
                        {field: 'user_invite_device_recharge_success_count',title: __('普通用户邀请充值成功人数'), operate:false},
                        {field: 'user_invite_device_recharge_money_success_count',title: __('普通用户邀请累计充值金额（元）'), operate:false},
                        {field: 'anchor_invite_device_count',title: __('主播邀请人数'), operate:false},
                        {field: 'anchor_invite_device_recharge_success_count',title: __('主播邀请充值成功人数'), operate:false},
                        {field: 'anchor_invite_device_recharge_money_success_count',title: __('主播邀请累计充值金额（元）'), operate:false},
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