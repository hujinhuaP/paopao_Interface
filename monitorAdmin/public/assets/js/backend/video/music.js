define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'video/music/index',
                    detail_url: '',
                    add_url: 'video/music/add',
                    del_url: 'video/music/delete',
                    edit_url: 'video/music/edit',
                    multi_url: '',
                    dragsort_url: '',
                    status_url: ''
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
                        {field: 'name', title: __('Name'), operate: 'LIKE %...%', placeholder: __('Like search'), style: 'width:200px'},
                        {field: 'duration', title: __('Duration'),operate: 'BETWEEN'},
                        {field: 'author', title: __('Author')},
                        {field: 'cover', title: __('Cover'), operate: false, formatter:function(value, row, index, custom){
                            return '<a href="'+ Fast.api.cdnurl(value? value : "/assets/img/avatar.png") +'" target="_blank"><img class="img-sm img-center" src="' + Fast.api.cdnurl(value) + '" onerror="this.src=\'/assets/img/avatar.png\'" /></a>';
                        }},
                        {field: 'url', title: __('Down url'), operate: false, formatter:function(value, row, index, custom){
                            return Table.api.formatter.url.call(this, value, row, index);
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
                commonSearch: true,
                searchFormVisible: false,
                pageSize: 12,
                pk: 'id',
                sortName:'id',
                sortOrder:'desc'
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        edit: function () {
            Form.api.bindevent($("form[role=form]"));
            // 给上传按钮添加上传成功事件
            $("#upload_cover_url").data("upload-success", function (data) {
                var url = Backend.api.cdnurl(data.url);
                $(".cover_img").prop("src", url);
                Toastr.success(__('Upload success'));
            });
            $("#plupload-down_url").data("upload-success", function (data) {
                var url = Backend.api.cdnurl(data.url);
                $("#c-down_url2").val(url);
                Toastr.success(__('Upload success'));
            });
        },
        add: function () {
            Form.api.bindevent($("form[role=form]"));
            // 给上传按钮添加上传成功事件
            $("#upload_cover_url").data("upload-success", function (data) {
                var url = Backend.api.cdnurl(data.url);
                $(".cover_img").prop("src", url);
                Toastr.success(__('Upload success'));
            });
            $("#plupload-down_url").data("upload-success", function (data) {
                var url = Backend.api.cdnurl(data.url);
                $("#c-down_url2").val(url);
                Toastr.success(__('Upload success'));
            });

        }
    };
    return Controller;
});