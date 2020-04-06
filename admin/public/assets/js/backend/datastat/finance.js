define(['jquery','bootstrap','backend','table','form'],function ($,undefined,Backend,Table,Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init();

            //绑定事件
            $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
                var panel = $($(this).attr("href"));
                if (panel.size() > 0) {
                    Controller.table[panel.attr("id")].call(this);
                    $(this).on('click', function (e) {
                        $($(this).attr("href")).find(".btn-refresh").trigger("click");
                    });
                }
                //移除绑定的事件
                $(this).unbind('shown.bs.tab');
            });

            //必须默认触发shown.bs.tab事件
            $('ul.nav-tabs li.active a[data-toggle="tab"]').trigger("shown.bs.tab");
        },
        table: {
            first: function () {
                // 表格1
                var table1 = $("#table1");
                table1.bootstrapTable({
                    url: 'datastat/finance/index',
                    toolbar: '#toolbar1',
                    sortName: 'stat_time',
                    search: false,
                    searchFormVisible: true,
                    columns: [
                        [
                            {field: 'state',checkbox: true},
                            {field: 'stat_time',title: __('Stat time'),sortable:true,operate: 'BETWEEN',defaultValue: $defalutStart+'|'+$defalutEnd,type: 'datetime',addclass: 'datetimepicker',data: 'data-date-format="YYYY-MM-DD"',formatter:function (value,row,index) {
                                    return Table.api.formatter.datetime.call(this,value,row,index).slice(0,11);
                                }},
                            {field: 'recharge_money_success_count',title: __('今日充值金额'), operate:false},
                            {field: 'recharge_wechat_money',title: __('今日微信充值'), operate:false},
                            {field: 'recharge_alipay_money',title: __('今日支付宝充值'), operate:false},
                            {field: 'vip_success_money_count',title: __('今日VIP充值'), operate:false},
                            {field: 'vip_wechat_money',title: __('今日VIP微信充值'), operate:false},
                            {field: 'vip_alipay_money',title: __('今日VIP支付宝充值'), operate:false},
                            {field: 'withdraw_money',title: __('今日提现金额'), operate:false},
                            {field: 'withdraw_wechat_money',title: __('今日微信提现金额'), operate:false},
                            {field: 'withdraw_alipay_money',title: __('今日支付宝提现金额'), operate:false}
                        ]
                    ],
                });

                // 为表格1绑定事件
                Table.api.bindevent(table1);

            },
            second: function () {
                // 表格2
                var table2 = $("#table2");
                table2.bootstrapTable({
                    url: 'datastat/finance/detaillist',
                    extend: {
                        index_url: '',
                        add_url: '',
                        edit_url: '',
                        del_url: '',
                        multi_url: '',
                        table: ''
                    },
                    toolbar: '#toolbar2',
                    sortName: 'recharge_stat_time',
                    search: false,
                    searchFormVisible: true,
                    columns: [
                        [
                            {field: 'state', checkbox: true},
                            {field: 'recharge_stat_time',title: __('Stat time'),sortable:true,operate: 'BETWEEN',defaultValue: $defalutStart+'|'+$defalutEnd,type: 'datetime',addclass: 'datetimepicker',data: 'data-date-format="YYYY-MM-DD"',formatter:function (value,row,index) {
                                    return Table.api.formatter.datetime.call(this,value,row,index).slice(0,11);
                                }},
                            {
                                field: 'recharge_stat_order_type', title: __('订单类型'), searchList: {
                                    'recharge': __('充值'),
                                    'vip': __('VIP'),
                                },
                                formatter: function (value, row, index) {
                                    return value == 'recharge' ? __('充值') : __('VIP');
                                }
                            },
                            {
                                field: 'recharge_stat_pay_type', title: __('Pay type'), searchList: {
                                    'elves': __('第四方支付'),
                                    'wx': __('Wechat'),
                                    'zfb': __('Alipay'),
                                    'apple': __('Apple pay'),
                                    'wxh5': __('Wechat') + 'H5',
                                    'alipayh5': __('Alipay') + 'H5',
                                    'quanmin': __('Wechat') + '全民支付',
                                    '318211': __('Wechat') + '318211支付',
                                    'skycat': __('Wechat') + '澎程H5',
                                    'test': __('Test'),
                                }, formatter: function (value, row, index) {
                                    switch (value) {
                                        case 'elves' :
                                            return __('第四方支付');
                                            break;
                                        case 'wx' :
                                            return __('Wechat');
                                            break;

                                        case 'zfb' :
                                            return __('Alipay');
                                            break;

                                        case 'apple' :
                                            return __('Apple pay');
                                            break;
                                        case 'wxh5' :
                                            return __('Wechat') + 'H5';
                                            break;

                                        case 'alipayh5' :
                                            return __('Alipay') + 'H5';
                                            break;
                                        case 'quanmin' :
                                            return __('Wechat') + '全民支付';
                                            break;
                                        case '318211' :
                                            return __('Wechat') + '318211支付';
                                            break;
                                        case 'skycat' :
                                            return __('Wechat') + '澎程支付';
                                            break;
                                        case 'test' :
                                        default :
                                            return __('Test');
                                            break;
                                    }
                                }},
                            {field: 'recharge_stat_pay_money',title: __('充值金额'), operate:false},
                            {field: 'recharge_stat_pay_success_money',title: __('充值成功金额'), operate:false},
                            {field: 'recharge_stat_order_count',title: __('充值订单'), operate:false},
                            {field: 'recharge_stat_order_success_count',title: __('充值成功订单'), operate:false},
                        ]
                    ]
                });

                // 为表格2绑定事件
                Table.api.bindevent(table2);
            },
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