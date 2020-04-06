define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'user/viporder/index',
                    detail_url: '',
                    multi_url: '',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,

                columns: [
                    [
                        {field: 'state', checkbox: true, },
                        {
                            field: 'user_vip_order_number',
                            title: __('Order id'),
                            operate: 'LIKE %...%',
                            placeholder: __('Like search'),
                            style: 'width:200px'
                        },
                        {
                            field: 'user_vip_transaction_id',
                            title: __('Third order id'),
                            operate: 'LIKE %...%',
                            placeholder: __('Like search'),
                            style: 'width:200px'
                        },
                        {
                            field: 'user_vip_order_type', title: __('Pay type'), searchList: {
                                'elves': __('第四方支付'),
                                'wx': __('Wechat'),
                                'zfb': __('Alipay'),
                                'apple': __('Apple pay'),
                                'wxh5': __('Wechat') + 'H5',
                                'alipayh5': __('Alipay') + 'H5',
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
                                    case 'skycat' :
                                        return __('Wechat') + '澎程支付';
                                    case 'test' :
                                    default :
                                        return __('Test');
                                        break;
                                }
                            }},
                        {field: 'user_id', title: __('User id')},
                        {field: 'user.user_nickname', title: __('Username'), operate: 'LIKE %...%', placeholder: __('Like search'), style: 'width:200px'},
                        {field: 'user_vip_order_combo_month', title: __('Month'), operate: false, formatter:function (value, row, index) {
                                return __('%d Month', value);
                            }},
                        {field: 'user_vip_order_combo_fee', title: __('Amount'), operate: false},
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
                            field: 'user_vip_order_status', title: __('Status'), searchList: {
                                'Y': __('Success'),
                                'N': __('Wait pay'),
                            }, formatter: function (value, row, index) {
                                return value == 'Y' ? __('Success') : __('Wait pay');
                            }
                        },
                        {field: 'user_vip_order_create_time', title: __('Create time'), operate: 'BETWEEN', type: 'datetime', addclass: 'datetimepicker', data: 'data-date-format="YYYY-MM-DD HH:mm:ss"', formatter:function (value, row, index) {
                                return Table.api.formatter.datetime.call(this, value, row, index);
                            }},
                        {field: 'user_vip_order_update_time', title: __('Update time'), operate: 'BETWEEN', type: 'datetime', addclass: 'datetimepicker', data: 'data-date-format="YYYY-MM-DD HH:mm:ss"', formatter:function (value, row, index) {
                                return Table.api.formatter.datetime.call(this, value, row, index);
                            }},
                    ]
                ],

                // templateView: true,
                // search: false,
                // showColumns: false,
                // showToggle: false,
                // showExport: false,
                commonSearch: true,
                searchFormVisible: false,
                pageSize: 12,
                pk: 'user_vip_order_id',
                sortName: 'user_vip_order_id',
                sortOrder: 'desc'
            });

            // 为表格绑定事件
            Table.api.bindevent(table);

            table.on('load-success.bs.table', function (e, json) {
                $("#totalRechargeSuccessFee").html(json.vip_money);
                $("#monthRechargeSuccessFee").html(json.month_money);
                $("#todayRechargeSuccessFee").html(json.today_money);
            });
        },
        edit: function () {
        },
        add: function () {
        },
        api: {
            formatter: {
            },
        }
    };
    return Controller;
});