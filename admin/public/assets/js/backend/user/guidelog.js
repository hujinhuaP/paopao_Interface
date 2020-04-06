define(['jquery','bootstrap','backend','table','form'],function ($,undefined,Backend,Table,Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'user/guidelog/index',
                    detail_url: '',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                showExport: false,
                columns: [
                    [
                        {field: 'state',checkbox: true},
                        {field: 'id',title: 'ID'},
                        {field: 'guide_user_id',title: '诱导' + __('User ID')},
                        {field: 'guide_anchor_user_id',title: '诱导主播ID'},
                        {field: 'guide_type', title: __('诱导类型'), searchList:{
                                'video': __('视频'),
                                'stay_index': __('首页停留'),
                                'stay_profile': __('个人信息停留'),
                            },
                            formatter: function (value, row, index) {
                                switch (value) {
                                    case 'video':
                                        return __('视频');
                                        break;
                                    case 'stay_index':
                                        return __('首页停留');
                                        break;
                                    case 'stay_profile':
                                        return __('个人信息停留');
                                }
                            }
                        },
                        {field: 'guide_config_id',title: '诱导话术库id'},
                        {field: 'guide_create_time',title: __('Create time'),sortable:true,operate: 'BETWEEN',defaultValue: $default_start_datetime+'|'+$default_end_datetime,type: 'datetime',addclass: 'datetimepicker',data: 'data-date-format="YYYY-MM-DD HH:mm:ss"',formatter:function (value,row,index) {
                                return Table.api.formatter.datetime.call(this,value,row,index);
                            }},
                    ]
                ],

                // templateView: true,
                search: false,
                // showColumns: false,
                // showToggle: false,
                // showExport: false,
                commonSearch: true,
                searchFormVisible: false,
                pagination: true,
                pageSize: 12,
                pk: 'id',
                sortName: 'id',
                sortOrder: 'desc',
            });

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
        }
    };
    return Controller;
});