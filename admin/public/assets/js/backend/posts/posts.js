define(['jquery', 'bootstrap', 'backend', 'table', 'form','layer'], function ($, undefined, Backend, Table, Form, Layer) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'posts/posts/index',
                    detail_url: 'posts/posts/detail',
                    edit_url: 'posts/posts/edit',
                    add_url: 'posts/posts/add',
                    comment_url: 'posts/comment/index',
                    gift_url: 'posts/gift/index',
                    hot_url: 'posts/posts/hot',
                    del_url: 'posts/posts/delete',
                    istop_url: 'posts/posts/istop',
                    report_url: 'posts/report/index',
                    examine_url: 'posts/posts/examine'
                }
            });
            Table.api.events.operate['click .btn-delone'] = function (e, value, row, index) {
                e.stopPropagation();
                var that = this;
                var top = $(that).offset().top - $(window).scrollTop();
                var left = $(that).offset().left - $(window).scrollLeft() - 260;
                if (top + 154 > $(window).height()) {
                    top = top - 154;
                }
                if ($(window).width() < 480) {
                    top = left = undefined;
                }
                var layerdi = Layer.prompt({title: '删除备注', formType: 2}, function(pass, index){
                    Layer.close(index);
                    if(!pass){
                        Layer.msg('请输入备注');
                    }
                    $(that).attr('data-remark',pass);
                    var table = $(that).closest('table');
                    var options = table.bootstrapTable('getOptions');
                    Table.api.multi("del", row[options.pk], table, that);
                    Layer.close(layerdi);
                });
            };

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,

                columns: [
                    [
                        {field: 'state', checkbox: true, },
                        {field: 'short_posts_id', title: 'ID'},
                        {field: 'short_posts_user_id', title: __('User id'),},
                        {field: 'short_posts_get_user_id', title: __('收益人（没填写为发布人）'),},
                        {field: 'short_posts_word', title: __('文字内容'),operate:false},
                        {field: 'short_posts_pay_type', title: __('收费类型'),sortable:true, searchList:{
                                'free': __('免费'),
                                'part_free': __('图片前两张免费'),
                                'pay': __('付费'),
                            },formatter:function (value, row, index) {
                                if(value == 'free'){
                                    return '免费';
                                }else if(value == 'part_free'){
                                    return '图片前两张免费；付费价格：' + row.short_posts_price;
                                }else if(value == 'pay'){
                                    return '付费；付费价格：' + row.short_posts_price;
                                }
                            }},
                        {field: 'short_posts_watch_num', title: __('Watch num'),sortable:true, operate: false},
                        {field: 'short_posts_like_num', title: __('Like num'),sortable:true, operate: false},
                        {field: 'short_posts_comment_num', title: __('Reply num'),sortable:true, operate: false,formatter:function (value, row, index) {
                                return Controller.api.formatter.commentList(value, row, index);
                            }},
                        {field: 'short_posts_buy_num', title: __('购买次数'),sortable:true, operate: false},
                        {field: 'short_posts_gift_num', title: __('收礼次数'),sortable:true, operate: false},
                        {field: 'short_posts_report_num', title: __('举报数'),sortable:true, operate: false,formatter:function (value, row, index) {
                                return Controller.api.formatter.reportList(value, row, index);
                            }},
                        {field: 'short_posts_collect_num', title: __('收藏数'),sortable:true, operate: false},
                        {field: 'short_posts_selection_time', title: __('精选时间'),sortable:true,  operate: 'BETWEEN', type: 'datetime', addclass: 'datetimepicker', data: 'data-date-format="YYYY-MM-DD HH:mm:ss"', formatter:function (value, row, index) {
                                return Table.api.formatter.datetime.call(this, value, row, index);
                            }},
                        {field: 'short_posts_is_selection', title: __('精选'),searchList:{
                                'Y': __('Yes'),
                                'N': __('No'),
                            },
                            formatter:function (value, row, index) {
                                return Controller.api.formatter.hotposts(value, row, index);
                            }},
                        {field: 'short_posts_top_time', title: __('置顶时间'),sortable:true,  operate: 'BETWEEN', type: 'datetime', addclass: 'datetimepicker', data: 'data-date-format="YYYY-MM-DD HH:mm:ss"', formatter:function (value, row, index) {
                                return Table.api.formatter.datetime.call(this, value, row, index);
                            }},
                        {field: 'short_posts_is_top', title: __('置顶'), searchList:{
                                'Y': __('Yes'),
                                'N': __('No'),
                            },
                            formatter:function (value, row, index) {
                                return Controller.api.formatter.istop(value, row, index);
                            }},
                        {field: 'short_posts_type', title: __('类型'), searchList:{
                                'word' : __('纯文字'),
                                'image' : __('图文'),
                                'video' : __('视频文字'),
                                'exhibition' : __('作品'),
                            },
                            formatter:function (value, row, index) {
                                switch (value) {
                                    case 'word':
                                        return __('纯文字');
                                    case 'image':
                                        return __('图文');
                                    case 'video':
                                        return __('视频文字');
                                    case 'exhibition':
                                        return __('作品');
                                }
                            }},
                        {field: 'short_posts_examine', title: __('审核显示'), searchList:examine_list,
                            formatter:function (value, row, index) {
                                return Controller.api.formatter.showexamine(value, row, index,'short_posts_examine');
                            }},
                        {field: 'short_posts_status', title: __('Check'), searchList:{
                                'C': __('Checking'),
                                'Y': __('Pass'),
                                'N': __('Refuse'),
                            }, formatter:function (value, row, index) {
                                switch (value) {
                                    case 'C':
                                        return __('Checking');
                                        break;
                                    case 'Y':
                                        return __('Pass');
                                        break;
                                    case 'N':
                                    default :
                                        return __('Refuse');
                                        break;
                                }
                            }},
                        {field: 'short_posts_check_remark', title: __('审核原因'), operate: false},
                        {field: 'short_posts_check_time', title: __('审核时间'), operate: 'BETWEEN', type: 'datetime', addclass: 'datetimepicker', data: 'data-date-format="YYYY-MM-DD HH:mm:ss"', formatter:function (value, row, index) {
                                return Table.api.formatter.datetime.call(this, value, row, index);
                            }},
                        {field: 'short_posts_create_time', title: __('Create time'), operate: 'BETWEEN', type: 'datetime', addclass: 'datetimepicker', data: 'data-date-format="YYYY-MM-DD HH:mm:ss"', formatter:function (value, row, index) {
                            return Table.api.formatter.datetime.call(this, value, row, index);
                        }},
                        {field: 'short_posts_update_time', title: __('更新时间'), operate: 'BETWEEN', type: 'datetime', addclass: 'datetimepicker', data: 'data-date-format="YYYY-MM-DD HH:mm:ss"', formatter:function (value, row, index) {
                                return Table.api.formatter.datetime.call(this, value, row, index);
                            }},

                        {field: 'operate', title: __('Operate'), table: table,
                            events: Table.api.events.operate,
                            formatter: function (value, row, index) {
                                this.table.data('operate-dragsort', false);
                                row[Table.config.dragsortfield] = 0;
                                return Table.api.formatter.operate.call(this, value, row, index);
                            }}

                    ]
                ],
                search: false,
                commonSearch: true,
                searchFormVisible: false,
                pageSize: 12,
                pk: 'short_posts_id',
                sortName: 'short_posts_id',
                sortOrder: 'desc',
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        detail: function () {
            Form.api.bindevent($("form[role=form]"));
            Layer.photos({
                photos: '.postsImage'
                ,anim: 5 //0-6的选择，指定弹出图片动画类型，默认随机（请注意，3.0之前的版本用shift参数）
            });
        },
        edit: function () {
            Form.api.bindevent($("form[role=form]"));
        },
        examine: function () {
            Form.api.bindevent($("form[role=form]"));
        },
        add: function () {
            Form.api.bindevent($("form[role=form]"));
        //  给上传视频封面按钮添加上传成功事件
            $("#upload_cover_url").data("upload-success", function (data) {
                var url = Backend.api.cdnurl(data.url);
                $(".cover_img").prop("src", url);
                Toastr.success(__('Upload success'));
            });
            //  给上传视频按钮添加上传成功事件
            $("#plupload-down_url").data("upload-success", function (data) {
                var url = Backend.api.cdnurl(data.url);
                $("#c-down_url2").val(url);
                var player = new TcPlayer('video', {
                    "mp4": url,
                    "m3u8": "", //请替换成实际可用的播放地址
                    "flv": "", //请替换成实际可用的播放地址
                    "autoplay" : false,      //iOS下safari浏览器，以及大部分移动端浏览器是不开放视频自动播放这个能力的
                    "coverpic" : "",
                    "width" :  '100%',//视频的显示宽度，请尽量使用视频分辨率宽度
                    "height" : '500'//视频的显示高度，请尽量使用视频分辨率高度
                });
                Toastr.success(__('Upload success'));
            });

            $("input[name='row[short_posts_type]']").change(function () {
                let type = $(this).val();
                $('.type-div').hide();
                switch (type) {
                    case 'image':
                        $(".type-div-image").show();
                        break;
                    case 'video':
                        $(".type-div-video").show();
                        break;
                    case 'exhibition':
                        $(".type-div-exhibition").show();
                        break;
                }
            });

        },
        api: {
            formatter: {
                showexamine: function (value, row, index,field) {
                    var url = Fast.api.fixurl($.fn.bootstrapTable.defaults.extend.examine_url) + '/ids/'+ row.short_posts_id;
                    let title = examine_list[value];
                    return " <a href='"+ url + "' title='"+ title +"' class='btn btn-xs btn-"+ (row.short_posts_examine == '0' ? "warning" : "danger") +"  btn-dialog'><i class='fa fa-cogs'></i> "
                        + title + "</a>";
                },
                hotposts: function (value, row, index) {
                    var url = Fast.api.fixurl($.fn.bootstrapTable.defaults.extend.hot_url);
                    return "<a href='javascript:;' class='btn btn-" + (row.short_posts_selection_time == 0 ? "warning" : "danger") + " btn-xs btn-change btn-disable' data-id='" +
                        row.short_posts_id + "' data-url='" + url + "' data-params='" +
                        (row.short_posts_selection_time == 0 ? 'Y' : 'N') + "'><i class='fa fa-dot'></i>"
                        + (row.short_posts_selection_time > 0 ? __('Yes') : __('No')) + "</a> ";
                },
                istop: function (value, row, index) {
                    var url = Fast.api.fixurl($.fn.bootstrapTable.defaults.extend.istop_url);
                    return "<a href='javascript:;' class='btn btn-" + (row.short_posts_is_top == 'N' ? "warning" : "danger") + " btn-xs btn-change btn-disable' data-id='" +
                        row.short_posts_id + "' data-url='" + url + "' data-params='" +
                        (row.short_posts_is_top == 'N' ? 'Y' : 'N') + "'><i class='fa fa-dot'></i>"
                        + (row.short_posts_is_top == 'Y' ? __('Yes') : __('No')) + "</a> ";
                },
                commentList: function (value, row, index, field) {
                    title = '评论列表';
                    var commentUrl = Fast.api.fixurl($.fn.bootstrapTable.defaults.extend.comment_url) + '/ids/'+ row.short_posts_id;
                    return " <a href='"+ commentUrl + "' title='"+ title +"' class='btn btn-xs btn-info  btn-dialog'>"
                        + value + "</a>";
                },
                giftList: function (value, row, index, field) {
                    title = '打赏礼物列表';
                    var giftListUrl = Fast.api.fixurl($.fn.bootstrapTable.defaults.extend.gift_url) + '/ids/'+ row.short_posts_id;
                    if(value == 0){
                        return " <a href='javascript:void(0)' title='"+ title +"' class='btn btn-xs btn-info'>"
                            + value + "</a>";
                    }else{
                        return " <a href='"+ giftListUrl + "' title='"+ title +"' class='btn btn-xs btn-info  btn-dialog'>"
                            + value + "</a>";
                    }
                },
                reportList: function (value, row, index, field) {
                    title = '打赏礼物列表';
                    var reportListUrl = Fast.api.fixurl($.fn.bootstrapTable.defaults.extend.report_url) + '/ids/'+ row.short_posts_id;
                    if(value == 0){
                        return " <a href='javascript:void(0)' title='"+ title +"' class='btn btn-xs btn-info'>"
                            + value + "</a>";
                    }else{
                        return " <a href='"+ reportListUrl + "' title='"+ title +"' class='btn btn-xs btn-info  btn-dialog'>"
                            + value + "</a>";
                    }
                }
            },
        }
    };
    return Controller;
});