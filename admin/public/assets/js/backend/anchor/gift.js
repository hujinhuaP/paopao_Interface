define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'anchor/gift/index',
                    detail_url: '',
                    add_url: 'anchor/gift/add',
                    del_url: '',
                    edit_url: 'anchor/gift/edit',
                    multi_url: '',
                    dragsort_url: 'anchor/gift/sort',
                    status_url: 'anchor/gift/status',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,

                columns: [
                    [
                        {field: 'state', checkbox: true, },
                        {field: 'live_gift_id', title: 'ID'},
                        {field: 'live_gift_name', title: __('Gift name'), operate: 'LIKE %...%', placeholder: __('Like search'), style: 'width:200px'},
                        {field: 'live_gift_logo', title: __('Image'), operate: false, formatter: function (value, row, index, custom) {
                            return '<a href="'+ Fast.api.cdnurl(value? value : "/assets/img/loading100x100.png") +'" target="_blank"><img class="img-sm img-center" src="' + Fast.api.cdnurl(value) + '" onerror="this.src=\'/assets/img/loading100x100.png\'" /></a>'; 
                        }},
                        {field: 'live_gift_source', title: __('SVGA'), formatter: Table.api.formatter.url},
                        {field: 'live_gift_coin', title: __('Gift coin'), operate: 'BETWEEN'},
                        {field: 'live_gift_category_id', title: __('Gift type'), searchList: giftCategory, formatter:function (value, row, index) {
                                return giftCategory[value];
                            }},
                        {field: 'live_gift_status', title: __('Status'), searchList: {
                                '1': __('Go online'),
                                '2': __('Go offline'),
                            }, formatter: function (value, row, index) {
                                return value == '1' ? __('Go online') : __('Go offline');
                            }},
                        {field: 'live_gift_type', title: __('权限'), searchList: {
                                '0': __('普通'),
                                '1': __('打赏'),
                                '2': __('VIP'),
                            }, formatter: function (value, row, index) {
                                switch (value) {
                                    case 0:
                                        return __('普通');
                                    case 1:
                                        return __('打赏');
                                    case 2:
                                        return __('VIP');
                                }
                            }},
                        {field: 'live_gift_notice_flg', title: __('送礼飘屏'), searchList: {
                                'Y': __('Yes'),
                                'N': __('No'),
                            }, formatter: function (value, row, index) {
                                return value == 'Y' ? __('Yes') : __('No');
                            }},
                        {field: 'live_gift_sort', title: __('Sort'), operate: 'BETWEEN'},
                        {field: 'live_gift_update_time', title: __('Operate time'), operate: 'BETWEEN', type: 'datetime', addclass: 'datetimepicker', data: 'data-date-format="YYYY-MM-DD HH:mm:ss"', formatter:function (value, row, index) {
                                return Table.api.formatter.datetime.call(this, value, row, index);
                            }},
                        {field: 'operate', title: __('Operate'), table: table,
                            events: Table.api.events.operate,
                            buttons: [
                            ],
                            formatter: function (value, row, index) {
                                return Table.api.formatter.operate.call(this, value, row, index)
                                +Controller.api.formatter.status(row.live_gift_status, row, index, 'live_gift_status')
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
                pagination: false,
                pageSize: 12,
                pk: 'live_gift_id',
                sortName: 'live_gift_id',
                sortOrder: 'asc',
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        edit: function () {
            Form.api.bindevent($("form[role=form]"));
        },
        add: function () {
            Form.api.bindevent($("form[role=form]"));
        },
        api: {
            formatter: {
                status: function (value, row, index, field) {
                    var btn = 'success';
                    var title = __('Go online');
                    var param = 1;
                    var url = Fast.api.fixurl($.fn.bootstrapTable.defaults.extend.status_url);
                    var icon = 'fa fa-long-arrow-up';
                    switch (value) {
                        case 2:
                            btn   = 'success';
                            title = __('Go online');
                            param  = '1';
                            icon = 'fa fa-long-arrow-up';
                            break;

                        case 1:
                        default :
                            btn   = 'danger';
                            title = __('Go offline');
                            param  = '2';
                            icon = 'fa fa-long-arrow-down';
                            break;
                    }

                    return " <a href='javascript:;' class='btn btn-" + btn + " btn-xs btn-change btn-tipsone' data-id='" + 
                            row.question_id + "' data-url='" + url + "' data-params='" + field + "=" + param +"'><i class='" + icon + "'></i> "
                            + title + "</a>";
                },
                rewardGift: function (value, row, index, field) {
                    var btn = 'success';
                    var title = __('Go online');
                    var param = 1;
                    var url = Fast.api.fixurl($.fn.bootstrapTable.defaults.extend.status_url);
                    var icon = 'fa fa-long-arrow-up';
                    switch (value) {
                        case 1:
                            btn   = 'danger';
                            title = __('Reward gift');
                            param  = '0';
                            icon = 'fa fa-long-arrow-down';
                            break;

                        case 0:
                        default :
                            btn   = 'success';
                            title = __('Reward gift');
                            param  = '1';
                            icon = 'fa fa-long-arrow-up';
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