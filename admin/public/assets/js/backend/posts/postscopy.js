define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'posts/postscopy/index',
                    detail_url: 'posts/postscopy/detail',
                    add_url: '',
                    del_url: 'posts/postscopy/delete',
                    rollback_url: 'posts/postscopy/rollback'
                }
            });


            var table = $("#table");
            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,

                columns: [
                    [
                        {field: 'state', checkbox: true, },
                        {field: 'short_posts_id', title: 'ID'},
                        {field: 'short_posts_user_id', title: __('User id'),},
                        {field: 'short_posts_word', title: __('文字内容'),operate:false},
                        {field: 'short_posts_watch_num', title: __('Watch num'), operate: false},
                        {field: 'short_posts_like_num', title: __('Like num'), operate: false},
                        {field: 'short_posts_comment_num', title: __('Reply num'), operate: false},
                        {field: 'short_posts_gift_num', title: __('打赏礼物次数'), operate: false},
                        {field: 'short_posts_collect_num', title: __('收藏数'), operate: false},
                        {field: 'short_posts_report_num', title: __('举报数'), operate: false},
                        {field: 'short_posts_type', title: __('类型'), searchList:{
                                'word' : __('纯文字'),
                                'image' : __('图文'),
                                'video' : __('视频文字'),
                            },
                            formatter:function (value, row, index) {
                                switch (value) {
                                    case 'word':
                                        return __('纯文字');
                                    case 'image':
                                        return __('图文');
                                    case 'video':
                                        return __('视频文字');
                                }
                            }},
                        {field: 'short_posts_status', title: __('Check'), searchList:{
                                'C': __('Checking'),
                                'Y': __('Pass'),
                                'N': __('Refuse'),
                            }, formatter:function (value, row, index) {
                                switch (value) {
                                    case 'C':
                                        return __('Checking');
                                        break;
                                    case 'Y':
                                        return __('Pass');
                                        break;
                                    case 'N':
                                    default :
                                        return __('Refuse');
                                        break;
                                }
                            }},
                        {field: 'short_posts_check_remark', title: __('删除原因'), operate: false},
                        {field: 'short_posts_check_time', title: __('删除时间'), operate: 'BETWEEN', type: 'datetime', addclass: 'datetimepicker', data: 'data-date-format="YYYY-MM-DD HH:mm:ss"', formatter:function (value, row, index) {
                                return Table.api.formatter.datetime.call(this, value, row, index);
                            }},
                        {field: 'short_posts_create_time', title: __('Create time'), operate: 'BETWEEN', type: 'datetime', addclass: 'datetimepicker', data: 'data-date-format="YYYY-MM-DD HH:mm:ss"', formatter:function (value, row, index) {
                            return Table.api.formatter.datetime.call(this, value, row, index);
                        }},

                        {field: 'operate', title: __('Operate'), table: table,
                            events: Table.api.events.operate,
                            formatter: function (value, row, index) {
                                this.table.data('operate-dragsort', false);
                                row[Table.config.dragsortfield] = 0;
                                return Table.api.formatter.operate.call(this, value, row, index);
                            }}

                    ]
                ],
                search: false,
                commonSearch: true,
                searchFormVisible: false,
                pageSize: 12,
                pk: 'short_posts_id',
                sortName: 'short_posts_id',
                sortOrder: 'desc',
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        detail: function () {
            Form.api.bindevent($("form[role=form]"));
        },
        edit: function () {
            Form.api.bindevent($("form[role=form]"));
        },
        api: {
            formatter: {
            },
            events: {
                operate: {
                    'click .btn-delone': function (e, value, row, index) {
                        alert(123);
                        e.stopPropagation();
                        var that = this;
                        var top = $(that).offset().top - $(window).scrollTop();
                        var left = $(that).offset().left - $(window).scrollLeft() - 260;
                        if (top + 154 > $(window).height()) {
                            top = top - 154;
                        }
                        if ($(window).width() < 480) {
                            top = left = undefined;
                        }
                        var index = Layer.confirm(
                            __('Are you sure you want to delete this item?'),
                            {icon: 3, title: __('Warning'), offset: [top, left], shadeClose: true},
                            function () {
                                var table = $(that).closest('table');
                                var options = table.bootstrapTable('getOptions');
                                Table.api.multi("del", row[options.pk], table, that);
                                Layer.close(index);
                            }
                        );
                    },
                }
            },
        }
    };
    return Controller;
});