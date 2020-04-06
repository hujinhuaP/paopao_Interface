define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'general/levelconfig/index',
                    add_url: 'general/levelconfig/add',
                    edit_url: 'general/levelconfig/edit',
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
                        {field: 'state', checkbox: true,},
                        {
                            field: 'level_type', title: __('等级类型'),defaultValue:'guard', searchList: {
                                '': __('全部'),
                                'guard': __('守护'),
                                'intimate': __('亲密'),
                                'user': __('用户'),
                                'anchor': __('主播'),
                            }, formatter: function (value, row, index) {
                               switch (value) {
                                   case 'guard':
                                       return '守护';
                                   case 'intimate':
                                       return '亲密';
                                   case 'user':
                                       return '用户';
                                   case 'anchor':
                                       return '主播';
                               }
                            }
                        },
                        {field: 'level_value', title: __('等级'),sortable:true},
                        {field: 'level_name', title: __('等级名称')},
                        {field: 'level_exp', title: __('所需经验')},
                        {field: 'level_extra', title: __('额外信息'),operate:false,formatter:function (value, row, index) {
                            switch (row.level_type) {
                                case 'user':
                                    return '[颜色]' + Controller.api.formatter.back_color(value,row);
                                case 'guard':
                                    return '[每日免费时长:]  ' + value;
                                case 'anchor':
                                    return '[颜色]' + Controller.api.formatter.back_color(row.anchor_color,row) + ': 视频聊天设置最大值：' + row.max_chat_price;
                                case 'intimate':
                                    if(value == 'Y'){
                                        return '[是否能免费聊天:] 是 ';
                                    }else{
                                        return '[是否能免费聊天:] 否 ';
                                    }
                                default:
                                    return '';
                            }

                            }},
                        {field: 'reward_coin', title: __('奖励金币'),operate:false,formatter:function (value, row, index) {
                                if(row.level_type == 'user'){
                                    return value;
                                }else{
                                    return '无';
                                }

                            }},
                        {
                            field: 'update_time',
                            title: __('Operate time'),
                            operate: false,
                            type: 'datetime',
                            addclass: 'datetimepicker',
                            data: 'data-date-format="YYYY-MM-DD HH:mm:ss"',
                            formatter: function (value, row, index) {
                                return Table.api.formatter.datetime.call(this, value, row, index);
                            }
                        },
                        {
                            field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate,
                            formatter: function (value, row, index) {
                                this.table.data('operate-dragsort', false);
                                row[Table.config.dragsortfield] = 0;
                                return Table.api.formatter.operate.call(this, value, row, index);
                            }
                        }
                    ]
                ],

                // templateView: true,
                search: false,
                // showColumns: false,
                // showToggle: false,
                // showExport: false,
                commonSearch: true,
                searchFormVisible: true,
                pageSize: 12,
                pk: 'id',
                sortName: 'level_value',
                sortOrder: 'asc',
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        add: function () {
            $("#c-level_type").change(function(){
                let that = $(this).find('option:selected').eq(0);
                let thisValue = parseInt(that.data('last-value')) + 1;
                let thisExp = parseInt(that.data('last-exp')) + 1;
                $("#c-level_value").val(thisValue);
                $("#c-level_exp").attr('min',thisExp);
                $(".type-change").hide();
                switch ($(this).val()) {
                    case 'user':
                        $(".userDiv").show();
                        break;
                    case 'guard':
                        $(".guardDiv").show();
                        break;
                    case 'anchor':
                        $(".anchorDiv").show();
                        break;
                    case 'intimate':
                        $(".intimateDiv").show();
                        break;
                    default:
                }
            });
            Form.api.bindevent($("form[role=form]"));
        },
        edit: function () {
            Form.api.bindevent($("form[role=form]"));
            $("#c-level_type").change(function(){
                let that = $(this).find('option:selected').eq(0);
                let thisValue = parseInt(that.data('last-value')) + 1;
                let thisExp = parseInt(that.data('last-exp')) + 1;
                $("#c-level_value").val(thisValue);
                $("#c-level_exp").attr('min',thisExp);
                if($(this).val() == 'user'){
                    $("#backColorDiv").show();
                }else{
                    $("#backColorDiv").hide();
                }
                if($(this).val() == 'guard'){
                    $("#guardDiv").show();
                }else{
                    $("#guardDiv").hide();
                }
                if($(this).val() == 'anchor'){
                    $(".anchorDiv").show();
                }else{
                    $(".anchorDiv").hide();
                }
            });
        },api: {
            formatter: {
                back_color:function(value,row){
                    if(row.level_type == 'user' || row.level_type == 'anchor' ){
                        return '<div style="width: 20px;height:20px;float:left;background-color: rgb('+ value +')"></div>';
                    }else{
                        return '-';
                    }
                }
            },
        }
    };
    return Controller;
});