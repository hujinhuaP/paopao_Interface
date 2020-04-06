define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'video/guide/index',
                    detail_url: '',
                    add_url: 'video/guide/add',
                    del_url: 'video/guide/delete',
                    edit_url: 'video/guide/edit',
                    multi_url: '',
                    play_url: 'video/guide/play',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,

                columns: [
                    [
                        {field: 'state', checkbox: true, },
                        {field: 'id', title: 'ID'},
                        {field: 'anchor_user_id', title: __('User id'),},
                        {field: 'user.user_nickname', title: __('Nickname'),operate:false},
                        {field: 'video_url', title: __('Down url'), operate: false, formatter:function(value, row, index, custom){
                            return Table.api.formatter.url.call(this, value, row, index);
                        }},
                        {field: 'create_time', title: __('Create time'), operate: 'BETWEEN', type: 'datetime', addclass: 'datetimepicker', data: 'data-date-format="YYYY-MM-DD HH:mm:ss"', formatter:function (value, row, index) {
                            return Table.api.formatter.datetime.call(this, value, row, index);
                        }},
                        {field: 'operate', title: __('Operate'), table: table,
                            events: Table.api.events.operate,
                            buttons: [
                                {name: 'play', icon: 'fa fa-play-circle', classname: 'btn btn-xs btn-info btn-detail btn-dialog', url:$.fn.bootstrapTable.defaults.extend.play_url, title:__('Video')+__('Play'), text:__('Play')}
                            ],
                            formatter: function (value, row, index) {
                                this.table.data('operate-dragsort', false);
                                row[Table.config.dragsortfield] = 0;
                                return Table.api.formatter.operate.call(this, value, row, index)
                            }}

                    ]
                ],
                commonSearch: true,
                searchFormVisible: false,
                pageSize: 12,
                pk: 'id',
                sortName: 'id',
                sortOrder: 'desc',
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        edit: function () {
            Form.api.bindevent($("form[role=form]"));
            $("#plupload-down_url").data("upload-success", function (data) {
                var url = Backend.api.cdnurl(data.url);
                $("#c-down_url2").val(url);
                Toastr.success(__('Upload success'));
            });
        },
        add: function () {
            Form.api.bindevent($("form[role=form]"));
            $("#plupload-down_url").data("upload-success", function (data) {
                var url = Backend.api.cdnurl(data.url);
                $("#c-down_url2").val(url);
                Toastr.success(__('Upload success'));
            });

        },
        api: {
            formatter: {
            },
        }
    };
    return Controller;
});