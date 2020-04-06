define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'general/carousel/index',
                    add_url: 'general/carousel/add',
                    edit_url: 'general/carousel/edit',
                    del_url: 'general/carousel/delete',
                    dragsort_url: 'general/carousel/sort',
                    status_url: 'general/carousel/status',
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
                        {field: 'carousel_id', title: 'ID'},
                        {field: 'carousel_url', title: __('Image'), operate: false, formatter:function (value, row, index, custom) {
                            return '<a href="'+ Fast.api.cdnurl(value? value : "/assets/img/loading100x100.png") +'" target="_blank"><img class="img-sm img-center" src="' + Fast.api.cdnurl(value) + '" onerror="this.src=\'/assets/img/loading100x100.png\'" /></a>';
                            // return Table.api.formatter.image.call(this, value, row, index, custom);
                        }},
                        {field: 'carousel_category_id', title: __('Image position'), searchList:carouselCategory, formatter:function (value, row, index) {
                            return carouselCategory[value];
                        }},
                        {field: 'carousel_href', title: __('Link'), operate: false, formatter:function (value, row, index) {
                            return Table.api.formatter.url.call(this, value, row, index);
                        }},
                        {field: 'carousel_sort', title: __('Sort')},
                        {field: 'carousel_status', title: __('Status'), searchList:{
                            'Y': __('Go online'),
                            'N': __('Go offline'),
                        }, formatter:function (value, row, index) {
                            return value == 'Y' ? __('Go online') : __('Go offline');
                        }},
                        {field: 'carousel_update_time', title: __('Operate time'), operate: 'BETWEEN', type: 'datetime', addclass: 'datetimepicker', data: 'data-date-format="YYYY-MM-DD HH:mm:ss"', formatter:function (value, row, index) {
                                return Table.api.formatter.datetime.call(this, value, row, index);
                            }},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate,
                            
                            formatter: function (value, row, index) {
                                return Table.api.formatter.operate.call(this, value, row, index)+Controller.api.formatter.status(row.carousel_status, row, index, 'carousel_status');
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
                pk: 'carousel_id',
                sortName: 'carousel_sort',
                sortOrder: 'asc',
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        add: function () {
            Form.api.bindevent($("form[role=form]"));
            // 给上传按钮添加上传成功事件
            $("#plupload-carousel_url").data("upload-success", function (data) {
                var url = Backend.api.cdnurl(data.url);
                $(".carousel_url-img").prop("src", url);
                Toastr.success(__('Upload success'));
            });
        },
        edit: function () {
            Form.api.bindevent($("form[role=form]"));
            // 给上传按钮添加上传成功事件
            $("#plupload-carousel_url").data("upload-success", function (data) {
                var url = Backend.api.cdnurl(data.url);
                $(".carousel_url-img").prop("src", url);
                Toastr.success(__('Upload success'));
            });
        },
        api: {
            formatter: {
                status: function (value, row, index, field) {
                    var btn = 'success';
                    var title = __('Go online');
                    var param = 'Y';
                    var url = Fast.api.fixurl($.fn.bootstrapTable.defaults.extend.status_url);
                    var icon = 'fa fa-long-arrow-up';
                    switch (value) {
                        case 'N':
                            btn   = 'success';
                            title = __('Go online');
                            param  = 'Y';
                            icon = 'fa fa-long-arrow-up';
                            break;

                        case 'Y':
                        default :
                            btn   = 'danger';
                            title = __('Go offline');
                            param  = 'N';
                            icon = 'fa fa-long-arrow-down';
                            break;
                    }

                    return " <a href='javascript:;' class='btn btn-" + btn + " btn-xs btn-change btn-tipsone' data-id='" +
                        row.question_id + "' data-url='" + url + "' data-params='" + field + "=" + param +"'><i class='" + icon + "'></i> "
                        + title + "</a>";
                }
            },
        }
    };
    return Controller;
});