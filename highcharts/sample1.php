<!DOCTYPE HTML>
<html>
        <head>
                <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
                <title></title>

                <script type="text/javascript" src="./js/jquery-1.7.1.min.js"></script>
                <script type="text/javascript">
$(function () {
        var chart = new Highcharts.Chart({
        
            chart: {
                renderTo: 'container',
                type: 'gauge',
                alignTicks: false,
                plotBackgroundColor: null,
                plotBackgroundImage: null,
                plotBorderWidth: 0,
                plotShadow: false
            },
        
            title: {
                text: 'Температура на улице'
            },
            
            pane: {
                startAngle: -90,
                endAngle: 90
            },          
        
            yAxis: [{
                min: -40,
                max: 40,
                lineColor: '#339',
                tickColor: '#339',
                minorTickColor: '#339',
                offset: -25,
                lineWidth: 3,
                labels: {
                    distance: 5,
                    rotation: 'auto'
                },
                tickLength: 10,
                minorTickLength: 5,
                endOnTick: false
            }, {
                
            }],
        
            series: [{
                name: 'Temp_U',
                data: [0],
                dataLabels: {
                    formatter: function () {
                        var Tu = this.y;
                        return '<span style="color:#339">'+'Температура '+ Tu + ' °C</span><br/>';
                    },
                    backgroundColor: {
                        linearGradient: {
                            x1: 0,
                            y1: 3,
                            x2: 0,
                            y2: 1
                        },
                        stops: [
                            [0, '#77D'],
                            [1, '#FFF']
                        ]
                    }
                },
                tooltip: {
                    valueSuffix: ' °C'
                }
            }]
        
        },
        // Add some life
        function(chart) {
            setInterval(function() {
            var url="/pChart/?op=value&p=ws.tempOutside";
            $.ajax({
             url: url,
             }).done(function(data) { 
              if (data!='') {
               //alert(data);
               var point = chart.series[0].points[0];
               point.update(parseFloat(data));
              }
             });

            }, 1000);
        });
});

                </script>
        </head>
        <body>
<script src="./js/highcharts.js"></script>
<script src="./js/highcharts-more.js"></script>
<script src="./js/modules/exporting.js"></script>

<div id="container" style="width: 500px; height: 400px; margin: 100 auto"></div>

        </body>
</html>
