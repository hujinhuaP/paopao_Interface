define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'document/systemmessage/index',
                    detail_url: 'document/systemmessage/detail',
                    add_url: 'document/systemmessage/add',
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
                        {field: 'system_message_id', title: 'ID'},
                        {field: 'system_message_title', title: __('Content'), operate: 'LIKE %...%', placeholder: __('Like search'), style: 'width:200px', formatter:function (value, row, index) {
                            return value.length < 20 ? value : (value.substr(0, 20) + " ... ");
                        }},
                        {field: 'system_message_type', title: __('Message type'), searchList:{
                                'general': __('General'),
                                'follow': __('Follow'),
                                'withdraw': __('Withdraw'),
                                'certification': __('Certification'),
                            },formatter:function (value, row, index) {
                                switch (value) {
                                    case 'certification' :
                                        return __('Certification');
                                        break;
                                    case 'follow' :
                                        return __('Follow');
                                        break;
                                    case 'withdraw' :
                                        return __('Withdraw');
                                        break;
                                    case 'general' :
                                    default :
                                        return __('General');
                                        break;
                                }
                            }},         
                        {field: 'system_message_push_type', title: __('Push type'), searchList:{
                                0: __('All user'),
                                2: __('All anchor'),
                                1: __('Select user'),
                            },formatter:function (value, row, index) {
                                switch (value) {
                                    case 1 :
                                        return __('Select user');
                                        break;
                                    case 2 :
                                        return __('All anchor');
                                        break;
                                    case 0 :
                                    default :
                                        return __('All user');
                                        break;
                                }
                            }},         
                        {field: 'system_message_status', title: __('Status'),  searchList:{
                                'N': __('Wait'),
                                'S': __('Sending'),
                                'Y': __('Finish'),
                            },formatter:function (value, row, index) {
                                switch (value) {
                                    case 'N' :
                                        return __('Wait');
                                        break;
                                    case 'S' :
                                        return __('Sending');
                                        break;
                                    case 'Y' :
                                    default :
                                        return __('Finish');
                                        break;
                                }
                            }},         
                        {field: 'system_message_create_time', title: __('Operate time'), operate: 'BETWEEN', type: 'datetime', addclass: 'datetimepicker', data: 'data-date-format="YYYY-MM-DD HH:mm:ss"', formatter:function (value, row, index) {
                                return Table.api.formatter.datetime.call(this, value, row, index);
                            }},         
                        {field: 'operate', title: __('Operate'), table: table,
                            events: Table.api.events.operate,
                            buttons: [
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
                pageSize: 12,
                pk: 'system_message_id',
                sortName: 'system_message_id',
                sortOrder: 'desc',
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        edit: function () {
            
        },
        add: function () {
            Form.api.bindevent($("form[role=form]"));
            $("#c-system_message_push_type").on('change', function () {
                if ($(this).val() == 1) {
                    $("#c-user_id").attr('disabled', false);

                } else {
                    $("#c-user_id").attr('disabled', true).val('').nextAll().remove();
                    $("#c-user_id").parent().parent().removeClass('has-success').removeClass('has-error');
                }
            });

            $("[type=reset]").on('click',function () {
                $("#c-user_id").attr('disabled', true).val('').nextAll().remove();
                $("#c-user_id").parent().parent().removeClass('has-success').removeClass('has-error');
            });
        },
        multi: function () {

        },
        api: {
            formatter: {
               
            },
        }
    };
    return Controller;
});