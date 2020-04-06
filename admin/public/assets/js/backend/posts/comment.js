define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'posts/comment/index/ids/' + shortPostsId,
                    detail_url: 'posts/comment/detail',
                    edit_url: 'posts/comment/edit',
                    add_url: '',
                    reply_url: 'posts/reply/index',
                    del_url: 'posts/comment/delete',
                }
            });

            var table = $("#table");
            Table.api.events.operate['click .btn-delone'] = function (e, value, row, index) {
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
                var layerdi = Layer.prompt({title: '删除备注', formType: 2}, function(pass, index){
                    Layer.close(index);
                    if(!pass){
                        Layer.msg('请输入备注');
                    }
                    $(that).attr('data-remark',pass);
                    var table = $(that).closest('table');
                    var options = table.bootstrapTable('getOptions');
                    Table.api.multi("del", row[options.pk], table, that);
                    Layer.close(layerdi);
                });
            };
            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,

                columns: [
                    [
                        {field: 'state', checkbox: true, },
                        {field: 'comment_id', title: 'ID'},
                        {field: 'short_posts_id', title: '动态ID'},
                        {field: 'user_id', title: __('User id'),},
                        {field: 'at_user_id', title: __('@用户id'),operate:false},
                        {field: 'at_user_nickname', title: __('@用户昵称'),operate:false},
                        {field: 'comment_content', title: __('评论内容'), operate: false},
                        {field: 'comment_like_num', title: __('Like num'), operate: false},
                        {field: 'reply_num', title: __('Reply num'), operate: false,formatter:function (value, row, index) {
                                return Controller.api.formatter.replyList(value, row, index);
                            }},
                        {field: 'comment_status', title: __('Check'), searchList:{
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
                        {field: 'comment_check_remark', title: __('审核原因'), operate: false},
                        {field: 'comment_check_time', title: __('审核时间'), operate: 'BETWEEN', type: 'datetime', addclass: 'datetimepicker', data: 'data-date-format="YYYY-MM-DD HH:mm:ss"', formatter:function (value, row, index) {
                                return Table.api.formatter.datetime.call(this, value, row, index);
                            }},
                        {field: 'create_time', title: __('Create time'), operate: 'BETWEEN', type: 'datetime', addclass: 'datetimepicker', data: 'data-date-format="YYYY-MM-DD HH:mm:ss"', formatter:function (value, row, index) {
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
                pk: 'comment_id',
                sortName: 'comment_id',
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
                replyList: function (value, row, index, field) {
                    title = '回复列表';
                    var replyUrl = Fast.api.fixurl($.fn.bootstrapTable.defaults.extend.reply_url) + '/ids/'+ row.comment_id;
                    return " <a href='"+ replyUrl + "' title='"+ title +"' class='btn btn-xs btn-info  btn-dialog'>"
                        + value + "</a>";
                },
            },
        }
    };
    return Controller;
});