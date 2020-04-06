define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'user/user/index',
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
                        {field: 'state',checkbox: true,},
                        {field: 'user_id',title: 'ID'},
                        {field: 'user_video_cover', title: __('User avatar'), operate: false, formatter: function (value, row, index, custom) {
                            if(row.user_is_certification == 'Y'){
                                return '<a href="'+ Fast.api.cdnurl(value? value : "/assets/img/avatar.png") +'" target="_blank"><img class="img-lg img-center" src="' + Fast.api.cdnurl(value) + '" onerror="this.src=\'/assets/img/avatar.png\'" /></a>';
                            }
                            }},
                        {field: 'user_nickname', title: __('Username'), operate: 'LIKE %...%', placeholder: __('Like search'), style: 'width:200px'},
                        {field: 'user_account.user_phone', title: __('Phone'), operate: 'LIKE %...%', placeholder: __('Like search'), style: 'width:200px'},
                        {field: 'user_is_certification', title: __('Certification status'), searchList: {
                                'Y': __('Pass'),
                                'N': __('No certification'),
                                'C': __('Checking'),
                                'D': __('Forbid certification'),
                                'R': __('Refuse'),
                            }, formatter:function (value, row, index) {
                                switch (value) {
                                    case "Y" :
                                        return __('Pass');
                                        break;
                                    case "N" :
                                        return __('No certification');
                                        break;
                                    case "C" :
                                        return __('Checking');
                                        break;
                                    case "D" :
                                        return __('Forbid certification');
                                        break;
                                    case "R" :
                                    default :
                                        return __('Refuse');
                                }
                            }},
                        {field: 'user_is_forbid', title: __('Forbid'), searchList:{
                                'Y' : __('Yes'),
                                'N' : __('No'),
                            },
                            events: Table.api.events.operate,
                            formatter:function (value, row, index) {
                                if(value == 'Y'){
                                    return  __('Yes');
                                }else{
                                    return __('No');
                                }
                            }},
                        {field: 'user_is_deny_speak', title: __('Deny speak'), searchList:{
                                'Y' : __('Yes'),
                                'N' : __('No'),
                            },
                            formatter:function (value, row, index) {
                                if(value == 'Y'){
                                    return  __('Yes');
                                }else{
                                    return __('No');
                                }
                            }},
                        {field: 'user_logout_time', title: __('最后下线时间'), operate: 'BETWEEN', type: 'datetime', addclass: 'datetimepicker', data: 'data-date-format="YYYY-MM-DD HH:mm:ss"', formatter:function (value, row, index) {
                                return Table.api.formatter.datetime.call(this, value, row, index);
                            }},
                        {field: 'user_update_time',title: __('Operate time'),operate: 'BETWEEN',type: 'datetime',addclass: 'datetimepicker',data: 'data-date-format="YYYY-MM-DD HH:mm:ss"',formatter:function (value,row,index) {
                                return Table.api.formatter.datetime.call(this,value,row,index);
                            }},
                        {field: 'user_create_time',title: __('Create time'),operate: 'BETWEEN',type: 'datetime',addclass: 'datetimepicker',data: 'data-date-format="YYYY-MM-DD HH:mm:ss"',formatter:function (value,row,index) {
                                return Table.api.formatter.datetime.call(this,value,row,index);
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
                pageSize: 12,
                pk: 'user_id',
                sortName: 'user_id',
                sortOrder: 'asc',
            });
            // 为表格绑定事件
            Table.api.bindevent(table);
        }
    };
    return Controller;
});