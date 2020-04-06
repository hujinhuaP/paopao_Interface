define(['jquery','bootstrap','backend','table','form'],function ($,undefined,Backend,Table,Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'user/online/index',
                    detail_url: '',
                }
            });

            var table = $("#table");

            $(function(){
                setInterval(function(){
                    let last_second = parseInt($("#refreshSecondB").html());
                    if(last_second == 0){
                        $(".btn-refresh").trigger("click");
                        $next_second = 15;
                    }else{
                        $next_second = last_second - 1;
                    }
                    $("#refreshSecondB").html($next_second);
                },1000);
            });

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,

                columns: [
                    [
                        {field: 'state',checkbox: true,},
                        {field: 'stat_time',title: __('Stat time'),sortable:true,operate: 'BETWEEN',type: 'datetime',addclass: 'datetimepicker',data: 'data-date-format="YYYY-MM-DD HH"',formatter:function (value,row,index) {
                                return Table.api.formatter.datetime.call(this,value,row,index) + '--' +  Table.api.formatter.datetime.call(this,parseInt(value) + 3600,row,index,row,index);
                            }},

                        {field: 'user_count', title: __('用户在线人数'),operate: 'BETWEEN'},
                        {field: 'anchor_count', title: __('主播在线人数'),operate: 'BETWEEN'},
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
                pk: 'stat_time',
                sortName: 'stat_time',
                sortOrder: 'desc',
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
            table.on('refresh.bs.table', function (e, settings, data) {
                $("#refreshSecondB").html(15);
            });
            table.on('load-success.bs.table', function (e, json) {
                $("#userCountB").html(json.online.user);
                $("#anchorCountB").html(json.online.anchor);
                $("#timeB").html(json.online.time);
            });
        },
        add: function () {
            Form.api.bindevent($("form[role=form]"));

        },api: {
            formatter: {
            },
        }
    };
    return Controller;
});