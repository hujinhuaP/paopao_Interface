define(['jquery', 'bootstrap', 'backend', 'table', 'form', 'template'], function ($, undefined, Backend, Table, Form, Template) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'dashboard/index',
                    add_url: '',
                    warning_url: 'dashboard/waringlive',
                    forbid_url: 'dashboard/disablelive',
                    multi_url: ''
                }
            });

            var table = $("#table");

            Template.helper("Moment", Moment);
            table.on('post-body.bs.table', function () {
                $.each($('.playDiv'), function (i, j) {
                    var divId = $(j).attr('id');
                    var dataFlg = $(j).data('flg');
                    var dataId = $(j).data('id');
                    var userId = $(j).data('user');
                    var anchorId = $(j).data('anchor');
                    // var streamId = anchorId + '_' + dataId + '_2';
                    // if(dataFlg == '2'){
                    //     streamId = userId + '_' + dataId + '_2';
                    // }
                    // streamId = '18640_lebo_' + streamId;
                    var streamId = $(j).data('stream-id');
                    streamId = '34300_' + streamId;
                    console.log("anchor_id: "+ anchorId +" http://play.sxypaopao.com/live/" + streamId);
                    var player = new TcPlayer(divId, {
                        "rtmp": "rtmp://play.sxypaopao.com/live/" + streamId, //请替换成实际可用的播放地址
                        "m3u8": "http://play.sxypaopao.com/live/" + streamId + ".m3u8", //请替换成实际可用的播放地址
                        "flv": "http://play.sxypaopao.com/live/" + streamId + ".flv", //请替换成实际可用的播放地址
                        // "rtmp": "rtmp://34574.liveplay.myqcloud.com/live/" + streamId, //请替换成实际可用的播放地址
                        // "m3u8": "http://34574.liveplay.myqcloud.com/live/" + streamId + ".m3u8", //请替换成实际可用的播放地址
                        // "flv": "http://34574.liveplay.myqcloud.com/live/" + streamId + ".flv", //请替换成实际可用的播放地址
                        'live': true,
                        'x5_player': true,
                        'flashUrl' : '//imgcache.qq.com/open/qcloud/video/player/release/QCPlayer.swf',
                        'volume': 0,
                        "autoplay": true,      //iOS下safari浏览器，以及大部分移动端浏览器是不开放视频自动播放这个能力的
                        "coverpic": {
                            style: 'cover',
                            src: '//vodplayerinfo-10005041.file.myqcloud.com/3035579109/vod_paster_pause/paster_pause1469013308.jpg'
                        },
                        "wight": 180,
                        "height": 320//视频的显示高度，请尽量使用视频分辨率高度
                    });
                });
            })

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                templateView: true,
                columns: [
                    [],
                ],
                //禁用默认搜索
                search: false,
                //启用普通表单搜索
                commonSearch: false,
                //可以控制是否默认显示搜索单表,false则隐藏,默认为false
                searchFormVisible: false,
                //分页大小
                pageSize: 4
            });

            // 为表格绑定事件
            Table.api.bindevent(table);


            //点击详情
            $(document).on("click", ".btn-sendnotify[data-id]", function () {
                let that = this;
                var url = Fast.api.fixurl($.fn.bootstrapTable.defaults.extend.warning_url) + '/ids/'+ $(that).data('id');
                Fast.api.open(url, __('发送系统消息'));
            });

            $(document).on("click", ".btn-forbid[data-id]", function () {
                let that = this;
                var table = $(that).closest('table');
                var url = Fast.api.fixurl($.fn.bootstrapTable.defaults.extend.forbid_url);
                $(that).attr('data-url', url);
                Layer.confirm(
                    __('确定禁播吗？禁播为主播挂断'),
                    {icon: 3, title: __('Warning'), shadeClose: true},
                    function (index) {
                        Table.api.multi(undefined, $(that).data('id'), table, that);
                        Layer.close(index);
                    }
                );
            });

            //获取选中项
            $(document).on("click", ".btn-selected", function () {
                //在templateView的模式下不能调用table.bootstrapTable('getSelections')来获取选中的ID,只能通过下面的Table.api.selectedids来获取
                Layer.alert(JSON.stringify(Table.api.selectedids(table)));
            });
        },
        add: function () {
            Controller.api.bindevent();
        },
        edit: function () {
            Controller.api.bindevent();
        },
        waringlive: function () {
            Controller.api.bindevent();
        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            },
            formatter: {
                url: function (value, row, index) {
                    return '<div class="input-group input-group-sm" style="width:250px;"><input type="text" class="form-control input-sm" value="' + value + '"><span class="input-group-btn input-group-sm"><a href="' + value + '" target="_blank" class="btn btn-default btn-sm"><i class="fa fa-link"></i></a></span></div>';
                },
                ip: function (value, row, index) {
                    return '<a class="btn btn-xs btn-ip bg-success"><i class="fa fa-map-marker"></i> ' + value + '</a>';
                },
                browser: function (value, row, index) {
                    //这里我们直接使用row的数据
                    return '<a class="btn btn-xs btn-browser">' + row.useragent.split(" ")[0] + '</a>';
                }
            },
            events: {
                ip: {
                    'click .btn-ip': function (e, value, row, index) {
                        var options = $("#table").bootstrapTable('getOptions');
                        //这里我们手动将数据填充到表单然后提交
                        $("#commonSearchContent_" + options.idTable + " form [name='ip']").val(value);
                        $("#commonSearchContent_" + options.idTable + " form").trigger('submit');
                        Toastr.info("执行了自定义搜索操作");
                    }
                },
                browser: {
                    'click .btn-browser': function (e, value, row, index) {
                        Layer.alert("该行数据为: <code>" + JSON.stringify(row) + "</code>");
                    }
                }
            }
        }
    };
    return Controller;
});