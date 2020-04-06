define(['jquery','bootstrap','backend','table','form'],function ($,undefined,Backend,Table,Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'datastat/expand/index'
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
                        {field: 'register_ios_male_count',title: __('新增用户（nan）'), operate:false,formatter: function(value,row,index){
                               return value + row.register_and_male_count;
                            }},
                        {field: 'register_ios_male_count',title: __('新增iOS注册用户(男)'), operate:false},
                        {field: 'register_and_male_count',title: __('新增安卓注册用户（男）'), operate:false},
                        {field: 'register_device_count',title: __('新增注册设备'), operate:false},
                        {field: 'register_ios_device_count',title: __('新增注册iOS设备'), operate:false},
                        {field: 'register_and_device_count',title: __('新增注册安卓设备'), operate:false},
                        {field: 'register_anchor_count',title: __('新增注册主播的数量'), operate:false},
                        {field: 'recharge_success_user_count',title: __('充值用户'), operate:false},
                        {field: 'register_user_recharge_success_count',title: __('充值用户(当天注册当天充值)'), operate:false},
                        {field: 'register_user_recharge_success_count_all',title: __('充值用户(当天注册)'), operate:false},
                        {field: 'vip_success_user_count',title: __('VIP用户'), operate:false},
                        {field: 'vip_success_user_count',title: __('充值转换率(24h)'), operate:false,formatter: function(value,row,index){
                            if(parseInt(row.register_ios_male_count) + parseInt(row.register_and_male_count) == 0){
                                return 0;
                            }else{
                                return ((row.recharge_success_user_count) / (row.register_ios_male_count + row.register_and_male_count) * 100).toFixed(2);
                            }
                            }},
                        {field: 'vip_success_user_count',title: __('充值转换率(全部)'), operate:false,formatter: function(value,row,index){
                                if(parseInt(row.register_ios_male_count) + parseInt(row.register_and_male_count) == 0){
                                    return 0;
                                }else{
                                    return ((row.register_user_recharge_success_count_all) / (row.register_ios_male_count + row.register_and_male_count) * 100).toFixed(2);
                                }
                            }},
                        {field: 'recharge_money_success_count',title: __('充值金额'), operate:false},
                        {field: 'recharge_money_success_count_and',title: __('安卓充值金额'), operate:false},
                        {field: 'recharge_money_success_count_ios',title: __('iOS充值金额'), operate:false},
                        {field: 'vip_success_money_count',title: __('VIP充值金额'), operate:false},
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
                        {field: 'user_invite_ios_device_count',title: __('普通用户邀请iOS人数'), operate:false},
                        {field: 'user_invite_and_device_count',title: __('普通用户邀请安卓人数'), operate:false},
                        {field: 'user_invite_device_recharge_success_count',title: __('普通用户邀请充值成功人数'), operate:false},
                        {field: 'user_invite_device_recharge_money_success_count',title: __('普通用户邀请累计充值金额（元）'), operate:false},
                        {field: 'anchor_invite_device_count',title: __('主播邀请人数'), operate:false},
                        {field: 'anchor_invite_ios_device_count',title: __('主播邀请iOS人数'), operate:false},
                        {field: 'anchor_invite_and_device_count',title: __('主播邀请安卓人数'), operate:false},
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