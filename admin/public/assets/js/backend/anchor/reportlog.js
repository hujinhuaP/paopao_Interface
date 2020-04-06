define(['jquery', 'bootstrap', 'backend', 'table', 'form','layer'], function ($, undefined, Backend, Table, Form,Layer) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'anchor/reportlog/index',
                    detail_url: 'anchor/reportlog/detail',
                    add_url: '',
                    del_url: '',
                    edit_url: '',
                    multi_url: ''
                }
            });

            var table = $("#table");
            $(document).on("click", ".report_images", function () {
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
                        {field: 'anchor_report_log_id', title: 'ID'},
                        {field: 'anchor_user_id', title: __('Anchor id')},
                        {field: 'user_id', title: __('User id')},
                        {field: 'anchor_report_title', title: __('类型')},
                        {field: 'anchor_report_log_content', title: __('Content'), operate: 'LIKE %...%', placeholder: __('Like search'), style: 'width:200px'},
                        {field: 'anchor_report_images', title: __('图片'), operate: false, formatter:function(value, row, index, custom){
                                if(value == ''){
                                    return '无';
                                }else{
                                    let images = value.split(',');
                                    let returnStr = '';
                                    for(j = 0,len=images.length; j < len; j++) {
                                        returnStr += '<a class="report_images" href="javascript:void(0)" target="_blank"><img class="img-sm img-center" style="margin-right: 2px;" src="' + Fast.api.cdnurl(images[j]) + '" onerror="this.src=\'/assets/img/avatar.png\'" /></a>';
                                    }
                                    return returnStr;
                                }
                            }},
                        {field: 'anchor_report_log_create_time', title: __('Operate time'), operate: 'BETWEEN', type: 'datetime', addclass: 'datetimepicker', data: 'data-date-format="YYYY-MM-DD HH:mm:ss"', formatter:function (value, row, index) {
                                return Table.api.formatter.datetime.call(this, value, row, index);
                            }},
                        {field: 'operate', title: __('Operate'), table: table,
                            events: Table.api.events.operate,
                            buttons: [],
                            formatter: function (value, row, index) {
                                this.table.data('operate-dragsort', false);
                                row[Table.config.dragsortfield] = 0;
                                return Table.api.formatter.operate.call(this, value, row, index);
                            }}

                    ]
                ],

                // templateView: true,
                // search: false,
                // showColumns: false,
                // showToggle: false,
                // showExport: false,
                commonSearch: true,
                searchFormVisible: false,
                pageSize: 12,
                pk: 'anchor_report_log_id',
                sortName: 'anchor_report_log_id',
                sortOrder: 'desc',
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        detail: function () {
            Layer.photos({
                photos: '#reportImage'
                ,anim: 5 //0-6的选择，指定弹出图片动画类型，默认随机（请注意，3.0之前的版本用shift参数）
            });
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