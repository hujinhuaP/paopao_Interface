define(['jquery', 'bootstrap', 'backend', 'table', 'form','layer'], function ($, undefined, Backend, Table, Form,Layer) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'user/identifylog/index',
                    detail_url: '',
                    add_url: '',
                    del_url: '',
                    edit_url: '',
                    multi_url: '',
                }
            });

            var table = $("#table");
            $(document).on("click", ".layer_images", function () {
                let imageArr = [];
                $(this).closest('tr').find('img').each(function(i){
                    imageArr[i] = '{"src":"'+ $(this).attr('src') +'"}';
                });
                imageJson = '{"data":['+ imageArr.join(',') +']}';
                Layer.photos({ photos: JSON.parse(imageJson) });
            });

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,

                columns: [
                    [
                        {field: 'state', checkbox: true, },
                        {field: 'user_identify_id', title: 'ID'},
                        {field: 'user.user_id',title: __('警告用户ID')},
                        {field: 'user.user_nickname', title: __('警告用户昵称'),operate: false},
                        {field: 'user.user_is_anchor', title: __('是否为主播'),searchList:{
                                'Y' : __('Yes'),
                                'N' : __('No'),
                            },
                            events: Table.api.events.operate,
                            formatter:function (value, row, index) {
                                return value == 'Y' ? __('Yes') : __('No');
                            }},
                        {field: 'user_identify_confidence', title: __('黄图置信度'), operate: false },
                        {field: 'user_identify_chat_log_id', title: __('聊天记录id'), operate: false},
                        {field: 'user_identify_times', title: __('触发次数')},
                        {field: 'user_identify_image_url', title: __('触发图片'), operate: false, formatter:function(value, row, index, custom){
                                return '<a class="layer_images" href="javascript:void(0)" target="_blank"><img class="img-sm img-center" src="' + Fast.api.cdnurl(value) + '" onerror="this.src=\'/assets/img/avatar.png\'" /></a>';
                            }},
                        {field: 'user_identify_create_time', title: __('Operate time'), defaultValue:$default_start_datetime + '|' + $default_end_datetime, operate: 'BETWEEN', type: 'datetime', addclass: 'datetimepicker', data: 'data-date-format="YYYY-MM-DD HH:mm:ss"', formatter:function (value, row, index) {
                                return Table.api.formatter.datetime.call(this, value, row, index);
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
                pk: 'user_identify_id',
                sortName: 'user_identify_create_time',
                sortOrder: 'desc',
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        edit: function () {
        },
        add: function () {
        },
        api: {
            formatter: {
                
            },
        }
    };
    return Controller;
});