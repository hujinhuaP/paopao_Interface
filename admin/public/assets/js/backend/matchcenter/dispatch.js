define(['jquery', 'bootstrap', 'backend', 'table', 'form','layer', 'upload'], function ($, undefined, Backend, Table, Form,Layer,Upload) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'matchcenter/dispatch/index',
                    add_url: '',
                    detail_url: 'matchcenter/dispatch/detail',
                    del_url: '',
                    multi_url: '',
                }
            });

            var table = $("#table");


            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,

                columns: [
                    [
                        {field: 'state', checkbox: true},
                        {field: 'dispatch_chat_user_id', title: __('用户ID')},
                        {field: 'user.user_nickname', title: __('Username'), operate: 'LIKE %...%', placeholder: __('Like search'), style: 'width:200px'},
                        {field: 'dispatch_chat_wait_duration', title: __('等待时长(s)'),operate: false},
                        {field: 'dispatch_chat_status', title: __('派单状态'), searchList:{
                                '0' : __('等待'),
                                '1' : __('接通'),
                                '-1' : __('用户取消'),
                                '-2' : __('主播取消'),
                                '-3' : __('主播超时'),
                            },
                            formatter:function (value, row, index) {
                                switch (value){
                                    case 0:
                                        return __('等待');
                                    case 1:
                                        return __('接通');
                                    case -1:
                                        return __('用户取消');
                                    case -2:
                                        return __('主播取消');
                                    case -3:
                                        return __('主播超时');
                                }
                            }},
                        {field: 'dispatch_chat_price', title: __('派单价格'),operate: false},
                        {field: 'dispatch_chat_anchor_user_id', title: __('主播用户ID')},
                        {field: 'anchor_user.user_nickname', title: '主播'+__('Username'), operate: false, placeholder: __('Like search'), style: 'width:200px'},
                        {field: 'dispatch_chat_create_time',title: __('Create time'),operate: 'BETWEEN',type: 'datetime',addclass: 'datetimepicker',data: 'data-date-format="YYYY-MM-DD HH:mm:ss"',formatter:function (value,row,index) {
                                return Table.api.formatter.datetime.call(this,value,row,index);
                            }},
                        {field: 'dispatch_chat_update_time',title: __('Update time'),operate: 'BETWEEN',type: 'datetime',addclass: 'datetimepicker',data: 'data-date-format="YYYY-MM-DD HH:mm:ss"',formatter:function (value,row,index) {
                                return Table.api.formatter.datetime.call(this,value,row,index);
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

                // templateView: true,
                search: false,
                // showColumns: false,
                // showToggle: false,
                // showExport: false,
                commonSearch: true,
                searchFormVisible: false,
                pageSize: 12,
                pk: 'dispatch_chat_id',
                sortName: 'dispatch_chat_id',
                sortOrder: 'desc',
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        detail:function(){
        },
        api: {
            formatter: {
                status: function (value, row, index, field) {
                    var btn = 'success';
                    var title = __('Enable');
                    var param = 'Y';
                    var url = Fast.api.fixurl($.fn.bootstrapTable.defaults.extend.status_url);
                    var icon = 'fa fa-long-arrow-up';
                    switch (value) {
                        case 'N':
                            btn   = 'warning';
                            title = __('No');
                            param  = 'Y';
                            icon = 'fa';
                            break;
                        case 'Y':
                        default :
                            btn   = 'danger';
                            title = __('Yes');
                            param  = 'N';
                            icon = 'fa';
                            break;
                    }
                    return " <a href='javascript:;' class='btn btn-" + btn + " btn-xs btn-change btn-tipsone' data-id='" +
                        row.user_id + "' data-url='" + url + "' data-params='" + field + "=" + param +"'> "
                        + title + "</a>";
                },
                formatSeconds: function (value) {
                    let secondTime = parseInt(value);// 秒
                    let minuteTime = 0;// 分
                    let hourTime = 0;// 小时
                    if (secondTime > 60) {//如果秒数大于60，将秒数转换成整数
                        //获取分钟，除以60取整数，得到整数分钟
                        minuteTime = parseInt(secondTime / 60);
                        //获取秒数，秒数取佘，得到整数秒数
                        secondTime = parseInt(secondTime % 60);
                        //如果分钟大于60，将分钟转换成小时
                        if (minuteTime > 60) {
                            //获取小时，获取分钟除以60，得到整数小时
                            hourTime = parseInt(minuteTime / 60);
                            //获取小时后取佘的分，获取分钟除以60取佘的分
                            minuteTime = parseInt(minuteTime % 60);
                        }
                    }
                    let result = "" + parseInt(secondTime) + "秒";

                    if (minuteTime > 0) {
                        result = "" + parseInt(minuteTime) + "分" + result;
                    }
                    if (hourTime > 0) {
                        result = "" + parseInt(hourTime) + "小时" + result;
                    }
                    return result;
                }
            },
        }
    };
    return Controller;
});