define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'video/charge/index',
                    detail_url: '',
                    add_url: '',
                    del_url: 'video/app/delete',
                    edit_url: '',
                    multi_url: '',
                    play_url: 'video/app/play',
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
                        {field: 'user_id', title: __('User id'),},
                        {field: 'user.user_nickname', title: __('Nickname'),operate:false},
                        {field: 'user.user_avatar', title: __('User avatar'), operate: false, formatter:function(value, row, index, custom){
                            return '<a href="'+ Fast.api.cdnurl(value? value : "/assets/img/avatar.png") +'" target="_blank"><img class="img-sm img-center" src="' + Fast.api.cdnurl(value) + '" onerror="this.src=\'/assets/img/avatar.png\'" /></a>';
                        }},
                        {field: 'category.name', title: __('Category name'),operate:false},
                        {field: 'title', title: __('Title'), operate: 'LIKE %...%', placeholder: __('Like search'), style: 'width:200px'},
                        {field: 'cover', title: __('Cover'), operate: false, formatter:function(value, row, index, custom){
                            return '<a href="'+ Fast.api.cdnurl(value? value : "/assets/img/avatar.png") +'" target="_blank"><img class="img-sm img-center" src="' + Fast.api.cdnurl(value) + '" onerror="this.src=\'/assets/img/avatar.png\'" /></a>';
                        }},
                        {field: 'play_url', title: __('Down url'), operate: false, formatter:function(value, row, index, custom){
                            return Table.api.formatter.url.call(this, value, row, index);
                        }},
                        {field: 'watch_num', title: __('Watch num'), operate: false},
                        {field: 'like_num', title: __('Like num'), operate: false},
                        {field: 'reply_num', title: __('Reply num'), operate: false},
                        {field: 'watch_price', title: __('视频价格（金币）'),operate:false},
                        {field: 'total_pay_times', title: __('付费次数'), operate: false},
                        {field: 'total_income', title: __('私密视频收入（佣金）'), operate: false},
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

        api: {
            formatter: {
            },
        }
    };
    return Controller;
});