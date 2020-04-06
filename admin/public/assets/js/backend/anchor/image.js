define(['jquery', 'bootstrap', 'backend', 'table', 'form', 'template'], function ($, undefined, Backend, Table, Form,Template) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'anchor/image/index/ids/'+user_id,
                    add_url: 'anchor/image/add/ids/'+user_id,
                    del_url: 'anchor/image/delete',
                }
            });

            var table = $("#table");

            Template.helper("Moment", Moment);
            $(document).on("click", ".btn-toggle-view", function () {
                var options = table.bootstrapTable('getOptions');
                table.bootstrapTable('refreshOptions', {templateView: !options.templateView});
            });
            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                templateView: true,
                columns: [
                    [
                        {field: 'state', checkbox: true, },
                        {field: 'id', title: 'ID', operate: false},
                        {field: 'img_src', title: __('User avatar'), operate: false, formatter: function (value, row, index, custom) {
                                return '<a href="'+ Fast.api.cdnurl(value? value : "/assets/img/avatar.png") +'" target="_blank"><img class="img-sm img-center" src="' + Fast.api.cdnurl(value) + '" onerror="this.src=\'/assets/img/avatar.png\'" /></a>';
                            }},
                        {field: 'position', title: __('显示位置'), searchList:{
                                'cover' : __('封面'),
                                'examine' : __('审核'),
                                'normal' : __('普通'),
                            },
                            formatter:function (value, row, index) {
                                switch (value){
                                    case 'cover':
                                        return __('封面');
                                    case 'examine':
                                        return __('审核');
                                    case 'normal':
                                        return __('普通');
                                }
                            }},
                        {field: 'visible_type', title: __('显示类型'), searchList:{
                                'vip' : __('VIP'),
                                'normal' : __('普通'),
                            },
                            formatter:function (value, row, index) {
                                switch (value){
                                    case 'vip':
                                        return __('封面');
                                    case 'normal':
                                        return __('普通');
                                }
                            }},
                        {field: 'create_time', title: __('添加时间'), sortable:true,operate: 'BETWEEN', type: 'datetime', addclass: 'datetimepicker', data: 'data-date-format="YYYY-MM-DD HH:mm:ss"', formatter:function (value, row, index) {
                                return Table.api.formatter.datetime.call(this, value, row, index);
                            }},
                        {field: 'update_time', title: __('操作时间'), sortable:true,operate: 'BETWEEN', type: 'datetime', addclass: 'datetimepicker', data: 'data-date-format="YYYY-MM-DD HH:mm:ss"', formatter:function (value, row, index) {
                                return Table.api.formatter.datetime.call(this, value, row, index);
                            }},
                        {field: 'operate', title: __('Operate'), table: table,
                            events: Table.api.events.operate,
                            buttons: [],
                            formatter: function (value, row, index) {
                                this.table.data('operate-dragsort', false);
                                row[Table.config.dragsortfield] = 0;
                                return Table.api.formatter.operate.call(this, value, row, index);
                            }}
                    ],
                ],
                //禁用默认搜索
                search: false,
                //启用普通表单搜索
                commonSearch: false,
                //可以控制是否默认显示搜索单表,false则隐藏,默认为false
                searchFormVisible: false,
                //分页大小
                pageSize: 12,
                pk: 'id',
                sortName: 'id',
                sortOrder: 'asc',
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        add: function () {
            Form.api.bindevent($("form[role=form]"));
            $("#upload_cover_url").data("upload-success", function (data) {
                var url = Backend.api.cdnurl(data.url);
                $(".cover_img").prop("src", url);
                Toastr.success(__('Upload success'));
            });
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