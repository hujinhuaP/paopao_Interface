define(['jquery','bootstrap','backend','table','form'],function ($,undefined,Backend,Table,Form) {

    var Controller = {
        matching: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'matchcenter/user/matching',
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
                        {field: 'user_id',title: 'ID'},
                        {field: 'user_nickname', title: __('Username'), operate: 'LIKE %...%', placeholder: __('Like search'), style: 'width:200px'},
                        {field: 'user_avatar', title: __('User avatar'), operate: false, formatter: function (value, row, index, custom) {
                                return '<a href="'+ Fast.api.cdnurl(value? value : "/assets/img/avatar.png") +'" target="_blank"><img class="img-md img-center" src="' + Fast.api.cdnurl(value) + '" onerror="this.src=\'/assets/img/avatar.png\'" /></a>';
                            }},
                        {field: 'duration', title: __('Matching duration') +'(s)'},
                        {field: 'user_has_consume', title: __('User attr'),
                            events: Table.api.events.operate,
                            formatter:function (value, row, index) {
                                return value == 'Y' ? __('Old user') : __('New user');
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
                pagination: true,
                pageSize: 20,
                pk: 'user_id',
                sortName: 'user_id',
                sortOrder: 'asc',
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
            table.on('refresh.bs.table', function (e, settings, data) {
                $("#refreshSecondB").html(15);
            });
            table.on('load-success.bs.table', function (e, json) {
                $("#freeAnchorCountB").html(json.free_count);
                $("#chatAnchorCountB").html(json.chat_count);
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