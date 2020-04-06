define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'datastat/anchorstat/index',
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
                        {
                            field: 'stat_time',
                            title: __('Stat time'),
                            sortable: true,
                            operate: 'BETWEEN',
                            type: 'datetime',
                            addclass: 'datetimepicker',
                            data: 'data-date-format="YYYY-MM-DD"',
                            formatter: function (value, row, index) {
                                return Table.api.formatter.datetime.call(this, value, row, index).slice(0, 11);
                            }
                        },
                        {field: 'user_id', title: __('User id')},
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
                            field: 'time_income',
                            title: __('今日收益'),
                            operate: false,
                            formatter: function (value, row, index) {
                                return (parseFloat(value) + parseFloat(row.gift_income) + parseFloat(row.video_income) + parseFloat(row.word_income)).toFixed(4);
                            }
                        },
                        {field: 'invite_recharge_income', title: __('邀请充值收益'), operate: false},
                        {field: 'time_income', title: __('时长收益'), operate: false, visible: false},
                        {field: 'gift_income', title: __('礼物收益'), operate: false, visible: false},
                        {field: 'video_income', title: __('小视频收益'), operate: false, visible: false},
                        {field: 'word_income', title: __('文字收益'), operate: false, visible: false},
                        {
                            field: 'match_duration',
                            title: __('匹配时长'),
                            operate: 'BETWEEN',
                            sortable: true,
                            formatter: function (value, row, index) {
                                return Controller.api.formatter.formatSeconds(value);
                            }
                        },
                        {field: 'match_times', title: __('匹配次数'), operate: 'BETWEEN', sortable: true},
                        {
                            field: 'match_times',
                            title: __('匹配转换率'),
                            operate: false,
                            sortable: true,
                            formatter: function (value, row, index) {
                                if (parseInt(value) == 0) {
                                    return 0;
                                } else {
                                    return ((row.match_recharge_count) / value * 100).toFixed(2);
                                }
                            }
                        },
                        {
                            field: 'normal_chat_duration',
                            title: __('点播时长'),
                            operate: 'BETWEEN',
                            sortable: true,
                            formatter: function (value, row, index) {
                                return Controller.api.formatter.formatSeconds(value);
                            }
                        },
                        {
                            field: 'normal_chat_duration',
                            title: __('总时长'),
                            operate: false,
                            formatter: function (value, row, index) {
                                return Controller.api.formatter.formatSeconds(parseInt(value) + parseInt(row.match_duration));
                            }
                        },
                        {field: 'normal_chat_call_times', title: __('今日有效点播呼叫次数'), operate: 'BETWEEN', sortable: true},
                        {field: 'normal_chat_times', title: __('今日点播次数'), operate: 'BETWEEN', sortable: true},
                        {
                            field: 'anchor.anchor_hot_time', title: __('热门'), searchList: {
                                '1': __('Yes'),
                                '0': __('No'),
                            },
                            formatter: function (value, row, index) {
                                return value == '1' ? __('Yes') : __('No');
                            }
                        },
                        {
                            field: 'anchor.anchor_is_newhot', title: __('新人热推'), searchList: {
                                'Y': __('Yes'),
                                'N': __('No'),
                            },
                            formatter: function (value, row, index) {
                                return value == 'Y' ? __('Yes') : __('No');
                            }
                        },
                        {
                            field: 'anchor.anchor_create_time', title: __('新人'), operate:false,
                            formatter: function (value, row, index) {
                                let current_time = parseInt((new Date().getTime())/1000);
                                if( parseInt(value) > parseInt(current_time) - 7 * 86400  ){
                                    return __('Yes');
                                }else{
                                    return __('No');
                                }
                            }
                        },
                        {field: 'online_duration', title: __('总共在线时长'),operate: false,sortable:true,
                            formatter:function (value, row, index) {
                                return Controller.api.formatter.formatSeconds(value);
                            }},
                        {field: 'guide_msg_times', title: __('诱导消息发送次数'), operate: false},
                        {field: 'guide_user_count', title: __('诱导用户数'), operate: false},

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
                sortName: 'gift_income',
                sortOrder: 'desc',
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        sumindex: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'datastat/anchorstat/sumindex',
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
                        {field: 'user_id', title: __('User id')},
                        {
                            field: 'user_nickname',
                            title: __('Username'),
                            operate: 'LIKE %...%',
                            placeholder: __('Like search'),
                            style: 'width:200px'
                        },
                        {
                            field: 'user_avatar',
                            title: __('User avatar'),
                            operate: false,
                            formatter: function (value, row, index, custom) {
                                return '<a href="' + Fast.api.cdnurl(value ? value : "/assets/img/avatar.png") + '" target="_blank"><img class="img-sm img-center" src="' + Fast.api.cdnurl(value) + '" onerror="this.src=\'/assets/img/avatar.png\'" /></a>';
                                // return Table.api.formatter.image.call(this, value, row, index, custom);
                            }
                        },
                        {
                            field: 'user_group_id',
                            title: __('Group name'),
                            formatter: function (value, row, index) {
                                return group[value];
                            }
                        },
                        {field: 'normal_chat_call_times', title: __('有效点播呼叫次数'), operate: false},
                        {field: 'normal_chat_times', title: __('接通次数'), operate: false},
                        {
                            field: 'normal_chat_duration',
                            title: __('点播通话时长'),
                            operate: false,
                            formatter: function (value, row, index) {
                                return Controller.api.formatter.formatSeconds(value);
                            }
                        },
                        {
                            field: 'normal_chat_duration',
                            title: __('平均通话时长'),
                            operate: false,
                            formatter: function (value, row, index) {
                                return Controller.api.formatter.formatSeconds(parseInt(value/row.normal_chat_times));
                            }
                        },
                        {
                            field: 'normal_chat_call_times',
                            title: __('接通率'),
                            operate: false,
                            formatter: function (value, row, index) {
                                if (parseInt(value) == 0) {
                                    return 0;
                                } else {
                                    return ((row.normal_chat_times) / value * 100).toFixed(2);
                                }
                            }
                        },
                        {field: 'gift_income', title: __('礼物收益'), operate: false},
                        {field: 'time_income', title: __('时间收益'), operate: false},
                        {field: 'invite_recharge_income', title: __('邀请收益'), operate: false},
                        {field: 'total_income', title: __('总收益'), operate: false},
                        {field: 'match_times', title: __('匹配次数'), operate: false},
                        {
                            field: 'match_duration',
                            title: __('匹配时长'),
                            operate: false,
                            formatter: function (value, row, index) {
                                return Controller.api.formatter.formatSeconds(value);
                            }
                        },
                        {
                            field: 'anchor_hot_time', title: __('热门'),operate: false,
                            formatter: function (value, row, index) {
                                return value == '1' ? __('Yes') : __('No');
                            }
                        },
                        {
                            field: 'anchor_is_newhot', title: __('新人热推'), operate: false,
                            formatter: function (value, row, index) {
                                return value == 'Y' ? __('Yes') : __('No');
                            }
                        },
                        {
                            field: 'anchor_create_time', title: __('新人'), operate:false,
                            formatter: function (value, row, index) {
                                let current_time = parseInt((new Date().getTime())/1000);
                                if( parseInt(value) > parseInt(current_time) - 7 * 86400  ){
                                    return __('Yes');
                                }else{
                                    return __('No');
                                }
                            }
                        },
                        {field: 'online_duration', title: __('总共在线时长'),operate: false,sortable:true,
                            formatter:function (value, row, index) {
                                return Controller.api.formatter.formatSeconds(value);
                            }},
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
                sortName: 'gift_income',
                sortOrder: 'desc',
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        api: {
            formatter: {
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
        },
    };
    return Controller;
});