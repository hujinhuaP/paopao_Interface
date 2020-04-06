define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'user/recharge/index',
                    detail_url: '',
                    add_url: '',
                    del_url: '',
                    edit_url: '',
                    multi_url: '',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,

                columns: [
                    [
                        {field: 'state', checkbox: true,},
                        {field: 'user_recharge_order_id', title: 'ID'},
                        {field: 'user_id', title: __('User id')},
                        {
                            field: 'user_recharge_order_number',
                            title: __('Order id'),
                            operate: 'LIKE %...%',
                            placeholder: __('Like search'),
                            style: 'width:200px'
                        },
                        {
                            field: 'user_recharge_order_transaction_id',
                            title: __('Third order id'),
                            operate: 'LIKE %...%',
                            placeholder: __('Like search'),
                            style: 'width:200px'
                        },
                        {field: 'user_recharge_order_coin', title: __('Buy coin'), operate: 'BETWEEN'},
                        {field: 'user_recharge_order_fee', title: __('Pay money'), operate: 'BETWEEN'},
                        {
                            field: 'user_recharge_order_type', title: __('Pay type'), searchList: {
                                'elves': __('第四方支付'),
                                'wx': __('Wechat'),
                                'zfb': __('Alipay'),
                                'apple': __('Apple pay'),
                                'wxh5': __('Wechat') + 'H5',
                                'alipayh5': __('Alipay') + 'H5',
                                'quanmin': __('Wechat') + '全民支付',
                                '318211': __('Wechat') + '318211支付',
                                'skycat': __('Wechat') + '澎程支付',
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
                                    case 'skycat' :
                                        return __('Wechat') + '澎程支付';
                                        break;
                                    case '318211' :
                                        return __('Wechat') + '318211支付';
                                        break;
                                    case 'test' :
                                    default :
                                        return __('Test');
                                        break;
                                }
                            }
                        },
                        {
                            field: 'user_type', title: __('Status'), searchList: {
                                'iOS': __('iOS'),
                                'Android': __('安卓'),
                            }, formatter: function (value, row, index) {
                                if(row.user_vip_order_status == 'N'){
                                    return '-';
                                }else{
                                    return value == 'iOS' ? __('iOS') : __('安卓');
                                }
                            }
                        },
                        {
                            field: 'user_recharge_order_status', title: __('Status'), searchList: {
                                'Y': __('Success'),
                                'N': __('Wait pay'),
                            }, formatter: function (value, row, index) {
                                return value == 'Y' ? __('Success') : __('Wait pay');
                            }
                        },
                        {
                            field: 'user_recharge_is_first', title: __('充值类型'), searchList: {
                                'Y': __('首次充值'),
                                'N': __('二次充值'),
                                'C': __('未完成'),
                            }, formatter: function (value, row, index) {
                                switch (value) {
                                    case 'Y' :
                                        return __('首次充值');
                                        break;

                                    case 'N' :
                                        return __('二次充值');
                                        break;
                                    case 'C' :
                                    default :
                                        return __('未完成');
                                        break;
                                }
                            }
                        },
                        {
                            field: 'reward_vip_day', title: __('赠送VIP天数'), formatter: function (value, row, index) {
                                if(row.user_recharge_order_status == 'Y'){
                                    return value;
                                }else{
                                    return '-';
                                }
                            }
                        },
                        {
                            field: 'user_is_vip', title: __('是否为VIP'), searchList: {
                                'Y': __('Yes'),
                                'N': __('No'),
                            }, formatter: function (value, row, index) {
                                if(row.user_recharge_order_status == 'N'){
                                    return '-';
                                }else{
                                    return value == 'Y' ? __('Yes') : __('No');
                                }
                            }
                        },
                        {
                            field: 'user_recharge_order_create_time',
                            title: __('Create time'),
                            operate: 'BETWEEN',
                            type: 'datetime',
                            addclass: 'datetimepicker',
                            data: 'data-date-format="YYYY-MM-DD HH:mm:ss"',
                            defaultValue:$default_start_datetime + '|' + $default_end_datetime,
                            formatter: function (value, row, index) {
                                return Table.api.formatter.datetime.call(this, value, row, index);
                            }
                        },
                        {
                            field: 'user_recharge_order_update_time',
                            title: __('Pay time'),
                            operate: 'BETWEEN',
                            type: 'datetime',
                            addclass: 'datetimepicker',
                            data: 'data-date-format="YYYY-MM-DD HH:mm:ss"',
                            formatter: function (value, row, index) {
                                return Table.api.formatter.datetime.call(this, value, row, index);
                            }
                        },
                        // {field: 'operate', title: __('Operate'), table: table,
                        //     events: Table.api.events.operate,
                        //     buttons: [
                        //     ],
                        //     formatter: function (value, row, index) {
                        //         this.table.data('operate-dragsort', false);
                        //         row[Table.config.dragsortfield] = 0;
                        //         return Table.api.formatter.operate.call(this, value, row, index);
                        //     }}

                    ]
                ],

                // templateView: true,
                search: false,
                // showColumns: false,
                // showToggle: false,
                // showExport: false,
                commonSearch: true,
                searchFormVisible: false,
                pageSize: 12,
                pk: 'user_recharge_order_id',
                sortName: 'user_recharge_order_id',
                sortOrder: 'desc',
            });

            // 为表格绑定事件
            Table.api.bindevent(table);

            table.on('load-success.bs.table', function (e, json) {
                $("#statRechargeSuccessFee").html(json.recharge_sum);
                $("#todayRechargeSuccessFee").html(json.today_recharge_sum);
                $("#totalRechargeSuccessFee").html(json.total_recharge_sum);
                $("#monthRechargeSuccessFee").html(json.month_money);
                $("#todayRechargeSuccessFee").html(json.today_money);
            });
        },
        edit: function () {

        },
        multi: function () {
            Form.api.bindevent($("form[role=form]"));
        },
        api: {
            formatter: {},
        }
    };
    return Controller;
});