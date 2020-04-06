define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'video/app/index',
                    detail_url: '',
                    add_url: '',
                    del_url: 'video/app/delete',
                    edit_url: 'video/check/edit',
                    multi_url: '',
                    play_url: 'video/app/play',
                    hot_url : 'video/app/hot',
                    show_url : 'video/app/show'
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
                        {field: 'user_nickname', title: __('Nickname'),operate:false},
                        {field: 'user_avatar', title: __('User avatar'), operate: false, formatter:function(value, row, index, custom){
                            return '<a href="'+ Fast.api.cdnurl(value? value : "/assets/img/avatar.png") +'" target="_blank"><img class="img-sm img-center" src="' + Fast.api.cdnurl(value) + '" onerror="this.src=\'/assets/img/avatar.png\'" /></a>';
                        }},
                        {field: 'category_name', title: __('Category name'),operate:false},
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
                        {field: 'share_num', title: __('Share num'), operate: false},
                        {field: 'create_time', title: __('Create time'), operate: 'BETWEEN', type: 'datetime', addclass: 'datetimepicker', data: 'data-date-format="YYYY-MM-DD HH:mm:ss"', formatter:function (value, row, index) {
                            return Table.api.formatter.datetime.call(this, value, row, index);
                        }},
                        {field: 'is_show', title: __('列表显示'), searchList: {
                            1: __('Show '),
                            0: __('Not Show')
                        }, formatter:function (value, row, index) {
                            switch (value) {
                                case 1:
                                    return __('Show');
                                    break;
                                case 0 :
                                    return __('Not Show');
                                    break;
                                default :
                                    return __('Show');
                                    break;
                            }
                        }},
                        {field: 'check_status', title: __('Check'), searchList:{
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
                        {field: 'operate', title: __('Operate'), table: table,
                            events: Table.api.events.operate,
                            buttons: [
                                {name: 'play', icon: 'fa fa-play-circle', classname: 'btn btn-xs btn-info btn-detail btn-dialog', url:$.fn.bootstrapTable.defaults.extend.play_url, title:__('Video')+__('Play'), text:__('Play')}
                            ],
                            formatter: function (value, row, index) {
                                this.table.data('operate-dragsort', false);
                                row[Table.config.dragsortfield] = 0;
                                if(row.check_status == 'C'){
                                    this.table.data('operate-edit', true);
                                }else{
                                    this.table.data('operate-edit', false);
                                }
                                return Table.api.formatter.operate.call(this, value, row, index)
                                    +Controller.api.formatter.hot_time(row.hot_time, row, index, 'hot_time')
                                    +Controller.api.formatter.show(row.is_show, row, index, 'is_show')
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
                hot_time: function (value, row, index, field) {
                    var btn = 'success';
                    var title = __('Set hot');
                    var param = 'Y';
                    var url = Fast.api.fixurl($.fn.bootstrapTable.defaults.extend.hot_url);
                    if (row.hot_time >  0) {
                        btn   = 'danger';
                        title = __('Close hot');
                        param  = 'N';
                    }
                    return " <a href='javascript:;' class='btn btn-" + btn + " btn-xs btn-change btn-tipsone' data-id='" +
                        row.id + "' data-url='" + url + "' data-params='"+ param +"'><i class='fa fa-fire'></i> "
                        + title + "</a>";
                },

                show: function (value, row, index, field) {
                    var btn = 'success';
                    var title = __('列表显示');
                    var param = 'Y';
                    var url = Fast.api.fixurl($.fn.bootstrapTable.defaults.extend.show_url);
                    if (row.is_show >  0) {
                        btn   = 'danger';
                        title = __('列表不显示');
                        param  = 'N';
                    }
                    return " <a href='javascript:;' class='btn btn-" + btn + " btn-xs btn-change btn-tipsone' data-id='" +
                        row.id + "' data-url='" + url + "' data-params='"+ param +"'><i class='fa fa-fire'></i> "
                        + title + "</a>";
                }
            },
        }
    };
    return Controller;
});