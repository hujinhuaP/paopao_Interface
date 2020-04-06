define(['jquery','bootstrap','backend','table','form'],function ($,undefined,Backend,Table,Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'matchcenter/guide/index',
                    detail_url: '',
                    play_url: 'video/guide/play'
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,

                columns: [
                    [
                        {field: 'state',checkbox: true},
                        {field: 'id',title: 'ID'},
                        {field: 'user.user_id',title: __('User ID')},
                        {field: 'user.user_nickname', title: __('Nickname'), operate: 'LIKE %...%', placeholder: __('Like search'), style: 'width:200px'},
                        {field: 'user_guide_video.id',title: __('匹配视频ID')},
                        {field: 'user_guide_video.video_url', title: __('视频播放地址'), operate: false, formatter:function(value, row, index, custom){
                                return Table.api.formatter.url.call(this, value, row, index);
                            }},
                        {field: 'create_time',title: __('Create time'),operate: 'BETWEEN',type: 'datetime',addclass: 'datetimepicker',data: 'data-date-format="YYYY-MM-DD HH:mm:ss"',formatter:function (value,row,index) {
                                return Table.api.formatter.datetime.call(this,value,row,index);
                            }},
                        {field: 'operate', title: __('Operate'), table: table,
                            events: Table.api.events.operate,
                            buttons: [
                                {name: 'play', icon: 'fa fa-play-circle', classname: 'btn btn-xs btn-info btn-detail btn-dialog', url:$.fn.bootstrapTable.defaults.extend.play_url, title:__('Video')+__('Play'), text:__('Play')}
                            ],
                            formatter: function (value, row, index) {
                                this.table.data('operate-dragsort', false);
                                row[Table.config.dragsortfield] = 0;
                                return Table.api.formatter.operate.call(this, value, row, index)
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