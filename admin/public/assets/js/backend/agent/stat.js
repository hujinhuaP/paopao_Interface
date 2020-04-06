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


            // 指定搜索条件
            $(document).on("click", ".btn-singlesearch", function () {
                var options = table.bootstrapTable('getOptions');
                var queryParams = options.queryParams;
                options.pageNumber = 1;
                var data_type = $(this).attr('data-type');
                if(data_type == '1'){
                    $(this).html("<i class=\"fa fa-user\"></i> 全部");
                    $(this).attr('data-type','0');
                }else{
                    $(this).html("<i class=\"fa fa-user\"></i> 仅显示有注册");
                    $(this).attr('data-type','1');
                }
                options.queryParams = function (params) {
                    //这一行必须要存在,否则在点击下一页时会丢失搜索栏数据
                    params = queryParams(params);

                    //如果希望追加搜索条件,可使用
                    var filter = params.filter ? JSON.parse(params.filter) : {};
                    var op = params.op ? JSON.parse(params.op) : {};
                    params.filter = JSON.stringify(filter);
                    params.op = JSON.stringify(op);
                    params.only_has_register = data_type;

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
                        {field: 'state', checkbox: true, },
                        {field: 'stat_time', title: __('Stat time'), sortable:true, operate: 'BETWEEN', type: 'datetime', addclass: 'datetimepicker', data: 'data-date-format="YYYY-MM-DD"', formatter:function (value, row, index) {
                                return Table.api.formatter.datetime.call(this, value, row, index).slice(0,11);
                            }},
                        {field: 'agent_id', title: __('Agent ID') },
                        {field: 'agent.level', title: __('代理等级') ,defaultValue: 1,searchList: {
                                '1': __('1'),
                                '2': __('2'),
                                '3': __('3'),
                            }},
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
                        {field: 'total_recharge_money', title: __('留存充值'), operate: false,formatter:function (value, row, index) {
                                return value - row.total_new_user_recharge_money;
                            }},
                        {field: 'new_user_recharge_money', title: __('今日注册充值') + '('+ __('Personal') +')', operate: false,visible:false},
                        {field: 'total_new_user_recharge_money', title: __('今日注册充值'), operate: false},
                        {field: 'total_and_recharge_money', title: __('安卓充值金额'), operate: false},
                        {field: 'total_ios_recharge_money', title: __('iOS充值金额'), operate: false},
                        {field: 'vip_money', title: __('购买VIP') + '('+ __('Personal') +')', operate: false,visible:false},
                        {field: 'total_vip_money', title: __('购买VIP'), operate: false},
                        {field: 'total_vip_user_count', title: __('VIP人数'), operate: false},
                        {field: 'total_vip_click_user_count', title: __('VIP点击人数'), operate: false},
                        {field: 'register_count', title: __('Register count') + '('+ __('Personal') +')', operate: false,visible:false},
                        {field: 'total_active_device_count', title: __('激活设备数'), operate: false},
                        {field: 'total_register_count', title: __('Register count'), operate: false},
                        {field: 'affect_register_count', title: __('Register count')+ '('+ __('de-duplication') +')' + '('+ __('Personal') +')', operate: false,visible:false},
                        {field: 'total_affect_register_count', title: __('Register count')+ '('+ __('de-duplication') +')', operate: false},
                        {field: 'affect_ios_register_count', title: __('iOS注册人数')+ '('+ __('de-duplication') +')' + '('+ __('Personal') +')', operate: false,visible:false},
                        {field: 'total_ios_affect_register_count', title: __('iOS注册人数')+ '('+ __('de-duplication') +')', operate: false},
                        {field: 'affect_and_register_count', title: __('安卓注册人数')+ '('+ __('de-duplication') +')' + '('+ __('Personal') +')', operate: false,visible:false},
                        {field: 'total_and_affect_register_count', title: __('安卓注册人数')+ '('+ __('de-duplication') +')', operate: false},
                        {field: 'total_free_times_try_user_count', title: __('体验人数'), operate: false},
                        {field: 'total_free_times_success_count', title: __('体验人数（成功）'), operate: false},
                        {field: 'total_new_user_recharge_money', title: __('R值'), operate: false,formatter:function (value, row, index) {
                            if(row.total_affect_register_count == 0){
                                return 0;
                            }else{
                                return parseFloat(parseInt(value) / parseInt(row.total_affect_register_count)).toFixed(2);
                            }
                            }},
                        {field: 'total_register_recharge_count', title: __('转化率'), operate: false,formatter:function (value, row, index) {
                                if(row.total_affect_register_count == 0){
                                    return 0;
                                }else{
                                    return parseFloat(parseInt(value) / parseInt(row.total_affect_register_count)).toFixed(2);
                                }
                            }},
                        {field: 'total_recharge_money', title: __('客单价'), operate: false,formatter:function (value, row, index) {
                                if(row.total_recharge_order_count == 0){
                                    return 0;
                                }else{
                                    return parseFloat(parseInt(value) / parseInt(row.total_recharge_order_count)).toFixed(2);
                                }
                            }},

                        {field: 'total_recharge_click_user_count', title: __('充值点击人数'), operate: false,visible:false},
                        {field: 'total_register_recharge_click_count', title: __('当天注册充值点击人数'), operate: false,visible:false},
                        {field: 'total_register_recharge_click_count', title: __('新用户充值按钮点击率'), operate: false,formatter:function (value, row, index) {
                                if(row.total_affect_register_count == 0){
                                    return 0;
                                }else{
                                    return parseFloat(parseInt(value) / parseInt(row.total_affect_register_count)).toFixed(2);
                                }
                            }},
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
                                if(row.agent.level == 3){
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