define(['jquery','bootstrap','backend','table','form'],function ($,undefined,Backend,Table,Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'datastat/settlement/index'
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,

                columns: [
                    [
                        {field: 'state',checkbox: true},
                        {field: 'stat_time',title: __('Stat time'),sortable:true,operate: 'BETWEEN',defaultValue: $defalutStart+'|'+$defalutEnd,type: 'datetime',addclass: 'datetimepicker',data: 'data-date-format="YYYY-MM-DD"',formatter:function (value,row,index) {
                                return Table.api.formatter.datetime.call(this,value,row,index).slice(0,11);
                            }},
                        {field: 'recharge_money_success_count',title: __('今日充值金额'), operate:false},
                        {field: 'vip_success_money_count',title: __('今日VIP充值'), operate:false},
                        {field: 'vip_success_money_count',title: __('总进账'), operate:false,formatter: function (value, row, index) {
                                return parseFloat(value) + parseFloat(row.recharge_money_success_count);
                            }},
                        {field: 'agent_total_income',title: __('代理商总收益'), operate:false},
                        {field: 'group_total_income',title: __('公会+主播总收益'), operate:false},
                        {field: 'user_total_cash_income',title: __('用户获取现金总收益'), operate:false},
                        {field: 'recharge_money_success_count',title: __('账单'), operate:false,formatter: function (value, row, index) {
                                return (parseFloat(value) + parseFloat(row.vip_success_money_count) - parseFloat(row.agent_total_income) - parseFloat(row.group_total_income) - parseFloat(row.user_total_cash_income)).toFixed(2);
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
                pageSize: 12,
                pk: 'id',
                sortName: 'stat_time',
                sortOrder: 'desc',
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