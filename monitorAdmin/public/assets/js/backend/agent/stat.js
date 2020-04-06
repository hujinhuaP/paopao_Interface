define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'agent/stat/index',
                    detail_url: '',
                    add_url: '',
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
                        {field: 'state', checkbox: true, },
                        {field: 'stat_time', title: __('Stat time'), sortable:true, operate: 'BETWEEN', type: 'datetime', addclass: 'datetimepicker', data: 'data-date-format="YYYY-MM-DD"', formatter:function (value, row, index) {
                                return Table.api.formatter.datetime.call(this, value, row, index).slice(0,11);
                            }},
                        {field: 'agent_id', title: __('Agent ID') },
                        {field: 'agent.status', title: __('Status'), defaultValue:'Y', searchList: {
                                'Y': __('Enable'),
                                'N': __('Disable'),
                            }, formatter: function (value, row, index) {
                                return value == 'Y' ? __('Enable') : __('Disable');
                            }},
                        {field: 'agent.nickname', title: __('Agent username'), operate: 'LIKE %...%', placeholder: __('Like search'), style: 'width:200px'},
                        {field: 'consume', title: __('Consume') + '('+ __('Personal') +')', operate: false,visible:false},
                        {field: 'total_consume', title: __('Consume'), operate: false},
                        {field: 'recharge_money', title: __('Recharge coin') + '('+ __('Personal') +')', operate: false,visible:false},
                        {field: 'total_recharge_money', title: __('Recharge coin'), operate: false},
                        {field: 'vip_money', title: __('购买VIP') + '('+ __('Personal') +')', operate: false,visible:false},
                        {field: 'total_vip_money', title: __('购买VIP'), operate: false},
                        {field: 'register_count', title: __('Register count') + '('+ __('Personal') +')', operate: false,visible:false},
                        {field: 'total_register_count', title: __('Register count'), operate: false},
                        {field: 'affect_register_count', title: __('Register count')+ '('+ __('de-duplication') +')' + '('+ __('Personal') +')', operate: false,visible:false},
                        {field: 'total_affect_register_count', title: __('Register count')+ '('+ __('de-duplication') +')', operate: false},
                        {field: 'affect_ios_register_count', title: __('iOS注册人数')+ '('+ __('de-duplication') +')' + '('+ __('Personal') +')', operate: false,visible:false},
                        {field: 'total_ios_affect_register_count', title: __('iOS注册人数')+ '('+ __('de-duplication') +')', operate: false},
                        {field: 'affect_and_register_count', title: __('安卓注册人数')+ '('+ __('de-duplication') +')' + '('+ __('Personal') +')', operate: false,visible:false},
                        {field: 'total_and_affect_register_count', title: __('安卓注册人数')+ '('+ __('de-duplication') +')', operate: false},
                        {field: 'recharge_user_count', title: __('充值成功人数') + '('+ __('Personal') +')', operate: false,visible:false},
                        {field: 'total_recharge_user_count', title: __('充值成功人数'), operate: false},
                        {field: 'income', title: __('Income') + '('+ __('Personal') +')', operate: false,visible:false},
                        {field: 'total_income', title: __('Income'), operate: false},
                        {field: 'operate', title: __('Operate'), table: table,
                            events: Table.api.events.operate,
                            buttons: [
                                {name: 'childdata', icon: 'fa fa-yen', classname: 'btn btn-xs btn-primary btn-detail btn-dialog', url:'agent/stat/child', title:__('Child stat data'), text:__('Child stat data')},
                                {name: 'income', icon: 'fa fa-yen', classname: 'btn btn-xs btn-primary btn-detail btn-dialog', url:'agent/stat/income', title:__('Income log'), text:__('Income log')},
                            ],
                            formatter: function (value, row, index) {
                                this.table.data('operate-dragsort', false);
                                row[Table.config.dragsortfield] = 0;
                                return Table.api.formatter.operate.call(this, value, row, index);
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
                pageSize: 15,
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
        child: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'agent/stat/child/ids/' + parent_id,
                    detail_url: '',
                    add_url: '',
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
                        {field: 'state', checkbox: true, },
                        {field: 'stat_time', title: __('Stat time'), sortable:true, operate: 'BETWEEN', type: 'datetime', addclass: 'datetimepicker', data: 'data-date-format="YYYY-MM-DD"', formatter:function (value, row, index) {
                                return Table.api.formatter.datetime.call(this, value, row, index).slice(0,11);
                            }},
                        {field: 'agent_id', title: __('Agent ID') },
                        {field: 'agent.nickname', title: __('Agent username'), operate: 'LIKE %...%', placeholder: __('Like search'), style: 'width:200px'},
                        {field: 'consume', title: __('Consume') + '('+ __('Personal') +')', operate: false},
                        {field: 'total_consume', title: __('Consume'), operate: false},
                        {field: 'recharge_money', title: __('Recharge coin') + '('+ __('Personal') +')', operate: false},
                        {field: 'total_recharge_money', title: __('Recharge coin'), operate: false},
                        {field: 'vip_money', title: __('购买VIP') + '('+ __('Personal') +')', operate: false},
                        {field: 'total_vip_money', title: __('购买VIP'), operate: false},
                        {field: 'register_count', title: __('Register count') + '('+ __('Personal') +')', operate: false},
                        {field: 'total_register_count', title: __('Register count'), operate: false},
                        {field: 'affect_register_count', title: __('Register count')+ '('+ __('de-duplication') +')' + '('+ __('Personal') +')', operate: false},
                        {field: 'total_affect_register_count', title: __('Register count')+ '('+ __('de-duplication') +')', operate: false},
                        {field: 'income', title: __('Income') + '('+ __('Personal') +')', operate: false},
                        {field: 'total_income', title: __('Income'), operate: false},
                        {field: 'operate', title: __('Operate'), table: table,
                            events: Table.api.events.operate,
                            buttons: [
                                {name: 'childdata', icon: 'fa fa-yen', classname: 'btn btn-xs btn-primary btn-detail btn-dialog', url:'agent/stat/child', title:__('Child stat data'), text:__('Child stat data')},
                                {name: 'income', icon: 'fa fa-yen', classname: 'btn btn-xs btn-primary btn-detail btn-dialog', url:'agent/stat/income', title:__('Income log'), text:__('Income log')},
                            ],
                            formatter: function (value, row, index) {
                                this.table.data('operate-dragsort', false);
                                row[Table.config.dragsortfield] = 0;
                                if(row.agent.second_leader == 0){
                                    this.table.data('operate-childdata', true);
                                }else{
                                    this.table.data('operate-childdata', false);
                                }
                                return Table.api.formatter.operate.call(this, value, row, index);
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
                sortName: 'stat_time',
                sortOrder: 'desc',
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },

        income: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'agent/stat/income/ids/' + parent_id,
                    detail_url: '',
                    add_url: '',
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
                        {field: 'state', checkbox: true, },
                        {field: 'source_agent.id', title: __('Source agent ID')},
                        {field: 'source_agent.nickname', title: __('Source agent username'), operate: 'LIKE %...%', placeholder: __('Like search'), style: 'width:200px'},
                        {field: 'user.user_id', title: __('Source user id')},
                        {field: 'user.user_nickname', title: __('Source user nickname'), operate: 'LIKE ...%', placeholder: __('Like search'), style: 'width:200px'},
                        {field: 'source_type', title: __('Source')+__('Type'), searchList:{
                                'recharge':__('Recharge'),
                                'vip':__('VIP'),
                            }, formatter:function (value, row, index) {
                                switch (value) {
                                    case 'vip' :
                                        return __('VIP');
                                        break;
                                    case 'recharge' :
                                    default :
                                        return __('Recharge');
                                        break;
                                }
                            }},
                        {field: 'distribution_source_value', title: __('金额（元）'),operate:false},
                        {field: 'distribution_profits', title: __('Divid precent'),operate:false},
                        {field: 'income', title: __('Income'),operate:false}
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
                sortName: 'id',
                sortOrder: 'desc',
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        api: {
            formatter: {
            },
        }
    };
    return Controller;
});