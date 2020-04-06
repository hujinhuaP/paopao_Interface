define(['jquery','bootstrap','backend','table','form', 'echarts', 'echarts-theme'],function ($,undefined,Backend,Table,Form,Echarts) {

    var Controller = {
        index: function () {
            var form = $("#one");
            Table.api.init({
                extend: {
                    index_url: 'datastat/expand/index',
                    month_index_url: 'datastat/expand/monthindex'
                }
            });
            var ranges = {};
            ranges[__('Today')] = [Moment().startOf('day'), Moment().endOf('day')];
            ranges[__('Yesterday')] = [Moment().subtract(1, 'days').startOf('day'), Moment().subtract(1, 'days').endOf('day')];
            ranges[__('Last 7 Days')] = [Moment().subtract(6, 'days').startOf('day'), Moment().endOf('day')];
            ranges[__('Last 30 Days')] = [Moment().subtract(29, 'days').startOf('day'), Moment().endOf('day')];
            ranges[__('This Month')] = [Moment().startOf('month'), Moment().endOf('month')];
            ranges[__('Last Month')] = [Moment().subtract(1, 'month').startOf('month'), Moment().subtract(1, 'month').endOf('month')];
            ranges[__('今年')] = [Moment().startOf('year'), Moment().endOf('year')];
            var options = {
                timePicker: false,
                autoUpdateInput: false,
                timePickerSeconds: true,
                timePicker24Hour: true,
                autoApply: true,
                locale: {
                    format: 'YYYY-MM-DD HH:mm:ss',
                    customRangeLabel: __("Custom Range"),
                    applyLabel: __("Apply"),
                    cancelLabel: __("Clear"),
                },
                ranges: ranges,
            };

            Form.api.bindevent($("form[role=form]"), function (data, ret) {
                console.log(data.start_date);
                console.log($("#two").find('input[name="stat_time"]'));
                $("#dayStat").find('input[name="stat_time"]').eq(0).val(data.start_date);
                $("#dayStat").find('input[name="stat_time"]').eq(1).val(data.end_date);

                $("#monthStat").find('input[name="stat_time"]').eq(0).val(data.start_date);
                $("#monthStat").find('input[name="stat_time"]').eq(1).val(data.end_date);
                Controller.api.charts(data);
            });

            $(".btn-success", form).trigger('click');
//绑定事件
            $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
                var panel = $($(this).attr("href"));
                if (panel.size() > 0) {
                    if(panel.attr("id") != 'one'){
                        Controller.table[panel.attr("id")].call(this);
                        $(this).on('click', function (e) {
                            $($(this).attr("href")).find("form").submit();
                        });
                    }
                }
                //移除绑定的事件
                $(this).unbind('shown.bs.tab');
            });

            //必须默认触发shown.bs.tab事件
            $('ul.nav-tabs li.active a[data-toggle="tab"]').trigger("shown.bs.tab");
        },
        table: {
            dayStat: function () {
                // 初始化表格参数配置
                // 表格1
                var table1 = $("#table3");
                table1.bootstrapTable({
                    url: $.fn.bootstrapTable.defaults.extend.index_url,

                    columns: [
                        [
                            {field: 'state',checkbox: true},
                            {field: 'stat_time',title: __('Stat time'),sortable:true,operate: 'BETWEEN',defaultValue: $defalutStart+'|'+$defalutEnd,type: 'datetime',addclass: 'datetimepicker',data: 'data-date-format="YYYY-MM-DD"',formatter:function (value,row,index) {
                                    return Table.api.formatter.datetime.call(this,value,row,index).slice(0,11);
                                }},
                            {field: 'register_ios_male_count',title: __('新增用户'), operate:false,formatter: function(value,row,index){
                                    return value + row.register_and_male_count;
                                }},
                            {field: 'recharge_order_count',title: __('充值订单总数'), operate:false},
                            {field: 'recharge_money_count',title: __('充值订单金额'), operate:false},
                            {field: 'recharge_order_success_count',title: __('充值成功订单数'), operate:false},
                            {field: 'recharge_money_success_count',title: __('充值成功金额'), operate:false},
                            {field: 'consume_coin_count',title: __('消费金币'), operate:false},
                            {field: 'video_chat_success_count',title: __('匹配成功'), operate:false},
                        ]
                    ],

                    // templateView: true,
                    search: false,
                    // showColumns: false,
                    // showToggle: false,
                    // showExport: false,
                    commonSearch: true,
                    searchFormVisible: true,
                    pageSize: 20,
                    pk: 'id',
                    sortName: 'stat_time',
                    sortOrder: 'desc',
                });

                // 为表格1绑定事件
                Table.api.bindevent(table1);
            },
            monthStat: function () {
                // 表格2
                var table2 = $("#table4");
                table2.bootstrapTable({
                    url: $.fn.bootstrapTable.defaults.extend.month_index_url,

                    columns: [
                        [
                            {field: 'state',checkbox: true},
                            {field: 'stat_time',title: __('Stat time'),sortable:true,operate: 'BETWEEN',defaultValue: $defalutStart+'|'+$defalutEnd,type: 'datetime',addclass: 'datetimepicker',data: 'data-date-format="YYYY-MM-DD"',formatter:function (value,row,index) {
                                    return row.stat_month;
                                }},
                            {field: 'register_count',title: __('新增用户'), operate:false},
                            {field: 'recharge_order_count',title: __('充值订单总数'), operate:false},
                            {field: 'recharge_money_count',title: __('充值订单金额'), operate:false},
                            {field: 'recharge_order_success_count',title: __('充值成功订单数'), operate:false},
                            {field: 'recharge_money_success_count',title: __('充值成功金额'), operate:false},
                            {field: 'consume_coin_count',title: __('消费金币'), operate:false},
                            {field: 'video_chat_success_count',title: __('匹配成功'), operate:false},
                        ]
                    ],

                    // templateView: true,
                    search: false,
                    // showColumns: false,
                    // showToggle: false,
                    // showExport: false,
                    commonSearch: true,
                    searchFormVisible: true,
                    pageSize: 20,
                    pk: 'id',
                    sortName: 'stat_month',
                    sortOrder: 'desc',
                });

                // 为表格1绑定事件
                Table.api.bindevent(table2);
            },
        },
        api: {
            charts: function (data) {
                var myChart1 = Echarts.init(document.getElementById('echart'), 'walden');
                // 指定图表的配置项和数据
                var option = {
                    tooltip : {
                        trigger: 'axis',
                        axisPointer : {            // 坐标轴指示器，坐标轴触发有效
                            type : 'shadow'        // 默认为直线，可选为：'line' | 'shadow'
                        }
                    },
                    legend: {
                        data:['注册人数','充值订单总数','充值订单金额','充值成功订单数','充值成功金额','消费金币','匹配成功'],
                        selected: {
                            '充值订单总数': false,
                            '充值订单金额': false,
                            '充值成功订单数': false,
                            '充值成功金额': false,
                            '消费金币': false,
                            '匹配成功': false,
                        }
                    },
                    grid: {
                        left: '3%',
                        right: '4%',
                        bottom: '3%',
                        containLabel: true
                    },
                    xAxis : [
                        {
                            type : 'category',
                            data : data.xAxis
                        }
                    ],
                    yAxis : [
                        {
                            type : 'value'
                        }
                    ],
                    series : [
                        {
                            name:'注册人数',
                            type:'bar',
                            data: data.rows.register_count
                        },
                        {
                            name:'充值订单总数',
                            type:'bar',
                            data:data.rows.recharge_order_count
                        },
                        {
                            name:'充值订单金额',
                            type:'bar',
                            data:data.rows.recharge_money_count
                        },
                        {
                            name:'充值成功订单数',
                            type:'bar',
                            data:data.rows.recharge_order_success_count
                        },
                        {
                            name:'充值成功金额',
                            type:'bar',
                            data:data.rows.recharge_money_success_count
                        },
                        {
                            name:'消费金币',
                            type:'bar',
                            data:data.rows.consume_coin_count
                        },
                        {
                            name:'匹配成功',
                            type:'bar',
                            data:data.rows.video_chat_success_count
                        }
                    ]
                };
                // 使用刚指定的配置项和数据显示图表。
                myChart1.setOption(option);


                var myChart2 = Echarts.init(document.getElementById('echart2'), 'walden');
                // 指定图表的配置项和数据
                var option2 = {
                    tooltip : {
                        trigger: 'axis',
                        axisPointer : {            // 坐标轴指示器，坐标轴触发有效
                            type : 'shadow'        // 默认为直线，可选为：'line' | 'shadow'
                        }
                    },
                    legend: {
                        data:['注册人数','充值订单总数','充值订单金额','充值成功订单数','充值成功金额','消费金币','匹配成功'],
                    },
                    grid: {
                        left: '3%',
                        right: '4%',
                        bottom: '3%',
                        containLabel: true
                    },
                    xAxis : [
                        {
                            type : 'category',
                            data : data.xAxisMonth
                        }
                    ],
                    yAxis : [
                        {
                            type : 'value'
                        }
                    ],
                    series : [
                        {
                            name:'注册人数',
                            type:'bar',
                            data: data.rowsMonth.register_count
                        },
                        {
                            name:'充值订单总数',
                            type:'bar',
                            data:data.rowsMonth.recharge_order_count
                        },
                        {
                            name:'充值订单金额',
                            type:'bar',
                            data:data.rowsMonth.recharge_money_count
                        },
                        {
                            name:'充值成功订单数',
                            type:'bar',
                            data:data.rowsMonth.recharge_order_success_count
                        },
                        {
                            name:'充值成功金额',
                            type:'bar',
                            data:data.rowsMonth.recharge_money_success_count
                        },
                        {
                            name:'消费金币',
                            type:'bar',
                            data:data.rowsMonth.consume_coin_count
                        },
                        {
                            name:'匹配成功',
                            type:'bar',
                            data:data.rowsMonth.video_chat_success_count
                        }
                    ]
                };
                // 使用刚指定的配置项和数据显示图表。
                myChart2.setOption(option2);
            }
        }
    };
    return Controller;
});