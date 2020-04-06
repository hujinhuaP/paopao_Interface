define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'general/guidelist/index',
                    add_url: 'general/guidelist/add',
                    edit_url: 'general/guidelist/edit',
                    del_url: 'general/guidelist/delete',
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
                        {field: 'id', title: 'ID'},
                        {
                            field: 'location_type', title: __('诱导位置'), searchList: {
                                'video': __('视频诱导'),
                                'index': __('首页'),
                                'profile': __('个人资料'),
                            }, formatter: function (value, row, index) {
                                switch (value) {
                                    case 'video':
                                        return __('视频诱导');
                                    case 'index':
                                        return __('首页');
                                    case 'profile':
                                        return __('个人资料');
                                }
                            }
                        },
                        {
                            field: 'first_msg_type', title: __('第一句话诱导类型'), searchList: {
                                'word': __('文字'),
                                'image': __('图片'),
                                'video': __('视频'),
                                'voice': __('语音'),
                            }, formatter: function (value, row, index) {
                                switch (value) {
                                    case 'word':
                                        return __('文字');
                                    case 'image':
                                        return __('图片');
                                    case 'video':
                                        return __('视频');
                                    case 'voice':
                                        return __('语音');
                                }
                            }
                        },
                        {
                            field: 'first_content',
                            title: __('第一句话（文字内容）'),
                            operate: 'LIKE %...%',
                            placeholder: __('Like search'),
                            style: 'width:200px'
                        },
                        {
                            field: 'second_msg_type', title: __('第二句话诱导类型'), searchList: {
                                'empty': __('不发'),
                                'word': __('文字'),
                                'image': __('图片'),
                                'video': __('视频'),
                                'voice': __('语音'),
                            }, formatter: function (value, row, index) {
                                switch (value) {
                                    case 'empty':
                                        return __('不发');
                                    case 'word':
                                        return __('文字');
                                    case 'image':
                                        return __('图片');
                                    case 'video':
                                        return __('视频');
                                    case 'voice':
                                        return __('语音');
                                }
                            }
                        },
                        {
                            field: 'second_content',
                            title: __('第二句话（文字内容）'),
                            operate: 'LIKE %...%',
                            placeholder: __('Like search'),
                            style: 'width:200px'
                        },
                        {
                            field: 'third_msg_type', title: __('第三句话诱导类型'), searchList: {
                                'empty': __('不发'),
                                'word': __('文字'),
                                'image': __('图片'),
                                'video': __('视频'),
                                'voice': __('语音'),
                            }, formatter: function (value, row, index) {
                                switch (value) {
                                    case 'empty':
                                        return __('不发');
                                    case 'word':
                                        return __('文字');
                                    case 'image':
                                        return __('图片');
                                    case 'video':
                                        return __('视频');
                                    case 'voice':
                                        return __('语音');
                                }
                            }
                        },
                        {
                            field: 'third_content',
                            title: __('第三句话（文字内容）'),
                            operate: 'LIKE %...%',
                            placeholder: __('Like search'),
                            style: 'width:200px'
                        },
                        {
                            field: 'update_time',
                            title: __('Operate time'),
                            operate: 'BETWEEN',
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
                searchFormVisible: false,
                pageSize: 12,
                pk: 'id',
                sortName: 'id',
                sortOrder: 'asc',
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        add: function () {
            Form.api.bindevent($("form[role=form]"));
            $("#contentVoiceUploadBtn1").data("upload-success", function (data) {
                var url = Backend.api.cdnurl(data.url);
                $("#contentVoiceInput1").text(url);
                Toastr.success(__('Upload success'));
            });

            $("#contentVoiceUploadBtn2").data("upload-success", function (data) {
                var url = Backend.api.cdnurl(data.url);
                $("#contentVoiceInput2").text(url);
                Toastr.success(__('Upload success'));
            });

            $("#contentVoiceUploadBtn3").data("upload-success", function (data) {
                var url = Backend.api.cdnurl(data.url);
                $("#contentVoiceInput3").text(url);
                Toastr.success(__('Upload success'));
            });
            $(".msgTypeSelect").change(function(){
                let voiceDiv = $(this).data('voicediv');
                if($(this).val() == 'voice'){
                    $("#"+voiceDiv).show();
                }else{
                    $("#"+voiceDiv).hide();
                }
            });
        },
        edit: function () {
            Form.api.bindevent($("form[role=form]"));
            $("#contentVoiceUploadBtn1").data("upload-success", function (data) {
                var url = Backend.api.cdnurl(data.url);
                $("#contentVoiceInput1").text(url);
                Toastr.success(__('Upload success'));
            });
            $("#contentVoiceUploadBtn2").data("upload-success", function (data) {
                var url = Backend.api.cdnurl(data.url);
                $("#contentVoiceInput2").text(url);
                Toastr.success(__('Upload success'));
            });

            $("#contentVoiceUploadBtn3").data("upload-success", function (data) {
                var url = Backend.api.cdnurl(data.url);
                $("#contentVoiceInput3").text(url);
                Toastr.success(__('Upload success'));
            });
            $(".msgTypeSelect").change(function(){
                let voiceDiv = $(this).data('voicediv');
                if($(this).val() == 'voice'){
                    $("#"+voiceDiv).show();
                }else{
                    $("#"+voiceDiv).hide();
                }
            });
        }
    };
    return Controller;
});