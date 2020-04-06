define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'user/photographer/index',
                    detail_url: 'user/photographer/edit',
                    add_url: '',
                    del_url: '',
                    edit_url: 'user/photographer/edit',
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
                        {field: 'user.user_id', title: 'ID'},
                        {field: 'user.user_nickname', title: __('Username'), operate: 'LIKE %...%', placeholder: __('Like search'), style: 'width:200px'},
                        {field: 'user.user_avatar', title: __('User avatar'), operate: false, formatter: function (value, row, index, custom) {
                            return '<a href="'+ Fast.api.cdnurl(value? value : "/assets/img/avatar.png") +'" target="_blank"><img class="img-sm img-center" src="' + Fast.api.cdnurl(value) + '" onerror="this.src=\'/assets/img/avatar.png\'" /></a>';
                        }},
                        {field: 'user_certification_status', title: __('Status'), searchList: {
                            'C': __('Checking'),
                            'Y': __('Pass'),
                            'N': __('Refuse'),
                            'D': __('Forbid certification'),
                        }, formatter:function (value, row, index) {
                            switch (value) {
                                case "NOT" :
                                    return __('未提交');
                                    break;
                                case "Y" :
                                    return __('Pass');
                                    break;
                                case "C" :
                                    return __('Checking');
                                    break;
                                case "D" :
                                    return __('Forbid certification');
                                    break;
                                case "N" :
                                default :
                                    return __('Refuse');
                                    break;
                            }
                        }},
                        {field: 'user_certification_video_status', title: __('视频认证状态'), searchList: {
                                'NOT': __('未提交'),
                                'C': __('Checking'),
                                'Y': __('Pass'),
                                'N': __('Refuse'),
                                'D': __('Forbid certification'),
                            }, formatter:function (value, row, index) {
                                switch (value) {
                                    case "NOT" :
                                        return __('未提交');
                                        break;
                                    case "Y" :
                                        return __('Pass');
                                        break;
                                    case "C" :
                                        return __('Checking');
                                        break;
                                    case "D" :
                                        return __('Forbid certification');
                                        break;
                                    case "N" :
                                    default :
                                        return __('Refuse');
                                        break;
                                }
                            }},
                        {field: 'user_certification_image_status', title: __('图片认证状态'), searchList: {
                                'NOT': __('未提交'),
                                'C': __('Checking'),
                                'Y': __('Pass'),
                                'N': __('Refuse'),
                                'D': __('Forbid certification'),
                            }, formatter:function (value, row, index) {
                                switch (value) {
                                    case "NOT" :
                                        return __('未提交');
                                        break;
                                    case "Y" :
                                        return __('Pass');
                                        break;
                                    case "C" :
                                        return __('Checking');
                                        break;
                                    case "D" :
                                        return __('Forbid certification');
                                        break;
                                    case "N" :
                                    default :
                                        return __('Refuse');
                                        break;
                                }
                            }},
                        {field: 'operate', title: __('Operate'), table: table,
                            events: Table.api.events.operate,
                            buttons: [
                                {name: 'check', icon: 'fa fa-pencil', classname: 'btn btn-xs btn-success btn-detail btn-dialog', url:$.fn.bootstrapTable.defaults.extend.edit_url, title:__('Check'), text:__('Check')},
                                {name: 'checked', icon: 'fa fa-pencil', classname: 'btn btn-xs btn-default btn-detail btn-dialog disabled', title:__('Check'), text:__('Check')},
                            ],
                            formatter: function (value, row, index) {
                                this.table.data('operate-edit', false);
                                this.table.data('operate-dragsort', false);
                                row[Table.config.dragsortfield] = 0;
                                if(row.user_certification_status == 'C' || row.user_certification_video_status == 'C' || row.user_certification_image_status == 'C'){
                                    this.table.data('operate-check', true);
                                    this.table.data('operate-checked', false);
                                }else{
                                    this.table.data('operate-check', false);
                                    this.table.data('operate-checked', true);
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
                pageSize: 12,
                pk: 'user_id',
                sortName: 'user_certification_id',
                sortOrder: 'desc',
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        edit: function () {
            Form.api.bindevent($("form[role=form]"));
        },
        multi: function () {
            Form.api.bindevent($("form[role=form]"));
        },
        api: {
            formatter: {
            },
        }
    };
    return Controller;
});