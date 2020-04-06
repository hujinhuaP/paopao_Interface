define(['jquery', 'bootstrap', 'backend', 'addtabs', 'table', 'layer'], function ($, undefined, Backend, Datatable, Table, Echarts, undefined, Template) {

    var Controller = {
        index: function () {
            function copyUrl(obj){

            }
            $(document).on("click", ".btn-copyinviteurl", function(){
                var copyobject=document.getElementById("copy-content");
                copyobject.select();
                document.execCommand("Copy");
                Layer.msg("复制链接成功！");
            });
        }
    };

    return Controller;
});