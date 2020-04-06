define(['jquery', 'bootstrap', 'backend', 'addtabs', 'table', 'echarts', 'echarts-theme'], function ($, undefined, Backend, Datatable, Table, Echarts) {

    var Controller = {
        index: function () {
            // 基于准备好的dom，初始化echarts实例
            var myChart = Echarts.init(document.getElementById('echart'), 'walden');

            // 指定图表的配置项和数据
            var option = {
                title: {
                    text: '',
                    subtext: ''
                },
                tooltip: {
                    trigger: 'axis'
                },
                legend: {
                    data: [__('Place an order')]
                },
                toolbox: {
                    show: false,
                    feature: {
                        magicType: {show: true, type: ['stack', 'tiled']},
                        saveAsImage: {show: true}
                    }
                },
                xAxis: {
                    type: 'category',
                    boundaryGap: false,
                    data: Orderdata.column
                },
                yAxis: {

                },
                grid: [{
                        left: 'left',
                        top: 'top',
                        right: '10',
                        bottom: 30
                    }],
                series: [{
                        name: __('Place an order'),
                        type: 'line',
                        smooth: true,
                        areaStyle: {
                            normal: {
                            }
                        },
                        lineStyle: {
                            normal: {
                                width: 1.5
                            }
                        },
                        data: Orderdata.createdata
                    }]
            };

            // 使用刚指定的配置项和数据显示图表。
            myChart.setOption(option);

            
            //动态添加数据，可以通过Ajax获取数据然后填充
            // setInterval(function () {
                // var sUrl = 'dashboard/index';
                // var nTs1 = (Date.parse(new Date()))/1000;
                // var nTs2 = nTs1-3;

                // var options = {url: sUrl, data: {pagesize:100}, type: "GET"};

                // Backend.api.ajax(options, function (ret) {
                //     $.each(ret, function (i,j) {
                //         if (i == 'createorderlist') {
                //             $.each(j, function (i, j) {
                //                 Orderdata.column.push(i);
                //                 Orderdata.createdata.push(j);
                //             })
                //         }
                //         $('#'+i).html(j);
                //     });
                // });

                // //按自己需求可以取消这个限制
                // if (Orderdata.column.length >= 100) {
                //     //移除最开始的一条数据
                //     Orderdata.column.shift();
                //     Orderdata.createdata.shift();
                // }
                // myChart.setOption({
                //     xAxis: {
                //         data: Orderdata.column
                //     },
                //     series: [{
                //             name: __('Place an order'),
                //             data: Orderdata.createdata
                //         }]
                // });

            // }, 3000);
            $(window).resize(function () {
                myChart.resize();
            });
        },
    };

    return Controller;
});