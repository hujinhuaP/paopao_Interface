define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'anchor/relaylist/index',
                    detail_url: 'anchor/relaylist/detail',
                    add_url: '',
                    del_url: '',
                    edit_url: 'anchor/relaylist/edit',
                    multi_url: '',
                    banlive_url: 'anchor/room/banlive',
                    hot_url : 'anchor/room/hot'
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,

                columns: [
                    [
                        {field: 'state', checkbox: true, },
                        {field: 'user_id', title: 'ID'},
                        {field: 'user_nickname', title: __('Username'), operate: 'LIKE %...%', placeholder: __('Like search'), style: 'width:200px'},
                        {field: 'user_avatar', title: __('User avatar'), operate: false, formatter: function (value, row, index, custom) {
                            return '<a href="'+ Fast.api.cdnurl(value? value : "/assets/img/avatar.png") +'" target="_blank"><img class="img-sm img-center" src="' + Fast.api.cdnurl(value) + '" onerror="this.src=\'/assets/img/avatar.png\'" /></a>'; 
                            // return Table.api.formatter.image.call(this, value, row, index, custom);
                        }},
                        {field: 'anchor_level', title: __('Anchor level'), operate: false},
                        {field: 'user_level', title: __('User level'), operate: false},
                        {field: 'user_coin', title: __('Coin'), operate: false},                      
                        {field: 'user_dot', title: __('Dot'), operate: false},                        
                        {field: 'user_follow_total', title: __('Follow'), operate: false},                     
                        {field: 'user_fans_total', title: __('Fans'),operate: false},                     
                        {field: 'anchor_is_live', title: __('Is living'), searchList:{
                                'Y' : __('Yes'),
                                'N' : __('No'),
                            },
                            formatter:function (value, row, index) {
                                return value == 'Y' ? __('Yes') : __('No');
                            }},                     
                        {field: 'anchor_is_forbid', title: __('Ban live'), searchList:{
                                'Y' : __('Yes'),
                                'N' : __('No'),
                            },
                            formatter:function (value, row, index) {
                                return value == 'Y' ? __('Yes') : __('No');
                        }},

                        {field: 'anchor_forbid_time', title: __('Stop live date'),operate: 'BETWEEN', type: 'datetime', addclass: 'datetimepicker', data: 'data-date-format="YYYY-MM-DD HH:mm:ss"', formatter:function (value, row, index) {
                                return Table.api.formatter.datetime.call(this, value, row, index);
                            }},
                        {field: 'anchor_type', title: __('Anchor type'), operate:false,searchList:{
                                '1' : __('Hand relay'),
                                '2' : __('Auto relay')
                            },
                            formatter:function (value, row, index) {
                                return value == '1' ? __('Hand relay') : __('Auto relay');
                            }},
                        {field: 'operate', title: __('Operate'), table: table,
                            events: Table.api.events.operate,
                            buttons: [
                            ],
                            formatter: function (value, row, index) {
                                this.table.data('operate-dragsort', false);
                                row[Table.config.dragsortfield] = 0;
                                return Table.api.formatter.operate.call(this, value, row, index)+Controller.api.formatter.banlive(value, row, index)+Controller.api.formatter.hotlive(value, row, index);
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
                sortName: 'user_id',
                sortOrder: 'desc',
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        edit: function () {
            Form.api.bindevent($("form[role=form]"));
            // 给上传按钮添加上传成功事件
            $("#plupload-avatar").data("upload-success", function (data) {
                var url = Backend.api.cdnurl(data.url);
                $(".profile-img").prop("src", url);
                Toastr.success(__('Upload success'));
            });
        },
        detail:function(){
            Table.api.init({
                search:false,
                showToggle:false,
                showColumns:false,
                showExport:false,
                extend: {
                    detail_url: 'anchor/anchor/detail'
                }
            });
            var table = $("#table");
            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.detail_url,

                columns: [
                    [
                        {field: 'live', title: __('Live time'),operate:false},
                        {field: 'chat', title: __('Chat time'), operate: false},
                        {field: 'date', title: __('Date'), type: 'datetime', addclass: 'datetimepicker', data: 'data-date-format="YYYY-MM-DD"', formatter:function (value, row, index) {
                                return Table.api.formatter.datetime.call(this, value, row, index);
                        }},
                        {field: 'type', title: __('Query type'), searchList:{
                                '1' : __('Day'),
                                '2' : __('Week'),
                                '3' : __('Month')
                            },
                            formatter:function (value, row, index) {
                                switch(value){
                                    case '1':
                                        return __('Day');
                                    case '2':
                                        return __('Week');
                                    case '3':
                                        return __('Month');
                                    default:
                                        return __('Day');
                                }
                        }}
                    ]
                ]
            });
        },
        multi: function () {
            Form.api.bindevent($("form[role=form]"));
        },
        api: {
            formatter: {
                banlive: function (value, row, index) {
                    var btn = 'danger';
                    var title = __('Ban live');
                    var anchor_is_forbid = 'Y';
                    var url = Fast.api.fixurl($.fn.bootstrapTable.defaults.extend.banlive_url);

                    switch (row.anchor_is_forbid) {
                        case 'N':
                            btn   = 'danger';
                            title = __('Ban live');
                            anchor_is_forbid  = 'Y';
                            break;

                        default :
                            btn   = 'warning';
                            title = __('Release');
                            anchor_is_forbid  = 'N';
                            break;
                    }

                    return " <a href='javascript:;' class='btn btn-" + btn + " btn-xs btn-change btn-tipsone' data-id='" + 
                            row.user_id + "' data-url='" + url + "' data-params='"+ anchor_is_forbid +"'><i class='fa fa-video-camera'></i> "
                            + title + "</a>";
                },
                hotlive: function (value, row, index) {
                    var btn = 'success';
                    var title = __('Set hot man');
                    var param = 'Y';
                    var url = Fast.api.fixurl($.fn.bootstrapTable.defaults.extend.hot_url);
                    if (row.anchor_hot_man ==  0) {
                        btn   = 'success';
                        title = __('Set hot man');
                        param  = 'Y';
                    }else{
                        btn   = 'danger';
                        title = __('Close hot man');
                        param  = 'N';
                    }
                    return " <a href='javascript:;' class='btn btn-" + btn + " btn-xs btn-change btn-tipsone' data-id='" +
                        row.user_id + "' data-url='" + url + "' data-params='"+ param +"'><i class='fa fa-fire'></i> "
                        + title + "</a>";
                },
            },
        }
    };
    return Controller;
});