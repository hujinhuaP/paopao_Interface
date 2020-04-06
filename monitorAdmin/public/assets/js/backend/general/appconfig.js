define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        
        edit: function () {
            Controller.api.bindevent();
            Form.api.bindevent($("form[role=form]"));
        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
                // 给上传按钮添加上传成功事件
                $("#plupload-avatar").data("upload-success", function (data) {
                    var url = Backend.api.cdnurl(data.url);
                    $(".share-img").prop("src", url);
                    Toastr.success(__('Upload success'));
                });
            }
        }
    };
    return Controller;
});