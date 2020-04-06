define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'user/budan/index',
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
                        {field: 'state', checkbox: true, },
                        {field: 'user_budan_id', title: 'ID'},
                        {field: 'user_id', title: __('User id')},
                        {field: 'user_nickname', title: __('Username'), operate: 'LIKE %...%', placeholder: __('Like search'), style: 'width:200px'},
                        {field: 'user_avatar', title: __('User avatar'), operate: false, formatter: function (value, row, index, custom) {
                            return '<a href="'+ Fast.api.cdnurl(value? value : "/assets/img/avatar.png") +'" target="_blank"><img class="img-sm img-center" src="' + Fast.api.cdnurl(value) + '" onerror="this.src=\'/assets/img/avatar.png\'" /></a>'; 
                            // return Table.api.formatter.image.call(this, value, row, index, custom);
                        }},
                        {field: 'user_budan_type', title: __('Type'), searchList:{
                                'coin': __('充值金币'),
                                'dot': __('Dot'),
                                'free_coin': __('赠送金币'),
                                'vip': __('VIP')
                            },
                            formatter: function (value, row, index) {
                                switch(value){
                                    case 'coin':
                                        return __('充值金币');
                                    case 'dot':
                                        return __('Dot');
                                    case 'free_coin':
                                        return  __('赠送金币');
                                    case 'vip':
                                        return  __('VIP');
                                }
                                return '';
                            }
                        },
                        {field: 'user_budan_amount', title: __('Number'), operate: 'BETWEEN',formatter: function (value, row, index) {
                                switch(row.user_budan_type){
                                    case 'coin':
                                        return value;
                                    case 'dot':
                                        return value;
                                    case 'free_coin':
                                        return  value;
                                    case 'vip':
                                        return  parseInt(value) + '天';
                                }
                                return value;
                            }},
                        {field: 'user_budan_create_time', title: __('Operate time'), operate: 'BETWEEN', type: 'datetime', addclass: 'datetimepicker', data: 'data-date-format="YYYY-MM-DD HH:mm:ss"', formatter:function (value, row, index) {
                            return Table.api.formatter.datetime.call(this, value, row, index);
                        }},
                        {field: 'operator', title: __('Operator'), operate:false}


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
                pk: 'user_budan_id',
                sortName: 'user_budan_id',
                sortOrder: 'desc',
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        add: function () {
            Form.api.bindevent($("form[role=form]"));
        },
    };
    return Controller;
});