define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'anchor/playtime/index',
                    add_url: '',
                    del_url: '',
                    multi_url: ''
                }
            });

            var table = $("#table");
            table.on('post-common-search.bs.table', function (event, table) {
                var form = $("form", table.$commonsearch);
                $("input[name='user.user_group_id']", form).addClass("selectpage").data("source", "group/group/selectpage").data("primaryKey", "id").data("field", "group_name").data("orderBy", "status desc,id desc");
            });
            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                columns: [
                    [
                        {field: 'state', checkbox: true},
                        {field: 'user_id', title: 'User id'},
                        {
                            field: 'user.user_group_id',
                            title: __('Group name'),
                            formatter: function (value, row, index) {
                                return group[value];
                            }
                        },
                        {
                            field: 'user.user_nickname',
                            title: __('Username'),
                            operate: 'LIKE %...%',
                            placeholder: __('Like search'),
                            style: 'width:200px'
                        },
                        {
                            field: 'user.user_avatar',
                            title: __('User avatar'),
                            operate: false,
                            formatter: function (value, row, index, custom) {
                                return '<a href="' + Fast.api.cdnurl(value ? value : "/assets/img/avatar.png") + '" target="_blank"><img class="img-sm img-center" src="' + Fast.api.cdnurl(value) + '" onerror="this.src=\'/assets/img/avatar.png\'" /></a>';
                                // return Table.api.formatter.image.call(this, value, row, index, custom);
                            }
                        },
                        {
                            field: 'user.user_fans_total',
                            title: __('累计总收益'),
                            operate: false,
                            formatter: function (value, row, index) {
                                return (parseFloat(row.user.user_collect_total) + parseFloat(row.user.user_collect_free_total) + parseFloat(row.user.user_invite_dot_total)).toFixed(4);
                            }
                        },
                        {field: 'anchor_today_income', title: __('今日收益'), operate: false},
                        {
                            field: 'anchor_today_match_duration',
                            title: __('今日匹配时长'),
                            operate: 'BETWEEN',
                            sortable: true,
                            formatter: function (value, row, index) {
                                return Controller.api.formatter.formatSeconds(value);
                            }
                        },
                        {field: 'anchor_today_match_times', title: __('今日匹配次数'), operate: 'BETWEEN', sortable: true},
                        {
                            field: 'anchor_today_new_match_times',
                            title: __('今日匹配转换率'),
                            operate: false,
                            sortable: true,
                            formatter: function (value, row, index) {
                                if (parseInt(value) == 0) {
                                    return 0;
                                } else {
                                    return ((row.anchor_today_new_recharge_count) / value * 100).toFixed(2);
                                }
                            }
                        },
                        {
                            field: 'anchor_today_normal_duration',
                            title: __('今日点播时长'),
                            operate: 'BETWEEN',
                            sortable: true,
                            formatter: function (value, row, index) {
                                return Controller.api.formatter.formatSeconds(value);
                            }
                        },
                        {field: 'anchor_today_called_times', title: __('今日有效点播呼叫次数'), operate: 'BETWEEN', sortable: true},
                        {field: 'anchor_today_normal_times', title: __('今日点播次数'), operate: 'BETWEEN', sortable: true},
                        {
                            field: 'user.user_logout_time',
                            title: __('最后下线时间'),
                            sortable: true,
                            operate: 'BETWEEN',
                            type: 'datetime',
                            addclass: 'datetimepicker',
                            data: 'data-date-format="YYYY-MM-DD HH:mm:ss"',
                            formatter: function (value, row, index) {
                                return Table.api.formatter.datetime.call(this, value, row, index);
                            }
                        }

                    ]
                ],
                search: false,
                // showColumns: false,
                // showToggle: false,
                // showExport: false,
                showExport: true,
                commonSearch: true,
                pageSize: 12,
                pk: 'user_id',
                sortName: 'anchor_today_match_times',
                sortOrder: 'desc',
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        api: {
            formatter: {
                formatSeconds:function(value){
                    let secondTime = parseInt(value);// 秒
                    let minuteTime = 0;// 分
                    let hourTime = 0;// 小时
                    if(secondTime > 60) {//如果秒数大于60，将秒数转换成整数
                        //获取分钟，除以60取整数，得到整数分钟
                        minuteTime = parseInt(secondTime / 60);
                        //获取秒数，秒数取佘，得到整数秒数
                        secondTime = parseInt(secondTime % 60);
                        //如果分钟大于60，将分钟转换成小时
                        if(minuteTime > 60) {
                            //获取小时，获取分钟除以60，得到整数小时
                            hourTime = parseInt(minuteTime / 60);
                            //获取小时后取佘的分，获取分钟除以60取佘的分
                            minuteTime = parseInt(minuteTime % 60);
                        }
                    }
                    let result = "" + parseInt(secondTime) + "秒";

                    if(minuteTime > 0) {
                        result = "" + parseInt(minuteTime) + "分" + result;
                    }
                    if(hourTime > 0) {
                        result = "" + parseInt(hourTime) + "小时" + result;
                    }
                    return result;
                }
            },
        },
    };
    return Controller;
});