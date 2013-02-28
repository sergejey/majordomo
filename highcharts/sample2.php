<!DOCTYPE HTML>
<html>
        <head>
                <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
                <title>Highstock Example</title>
                <script type="text/javascript" src="./js/jquery-1.7.1.min.js"></script>
                <script type="text/javascript" language="javascript">


var chart_preiod=15; //days
var chart_interval=1200; //seconds (interval);

var dateNow = new Date();
var startDate = new Date(dateNow.getTime() - chart_preiod*24*60*60*1000);
startDate.setHours(0,0,0,0);

$(function() {

url = '/pChart/?p=ws.tempOutside&op=values&start='+startDate.getFullYear()+'/'+(startDate.getMonth()+1)+'/'+(startDate.getDate())+'&interval='+chart_interval;


        $.getJSON(url, function(data) {
        
                //alert(data);
                // Create a timer
                var start = + new Date();
                var old_data=data;
                for(var i=0;i<old_data.length;i++) {
                 data[i]=parseFloat(old_data[i]);
                }
        
                // Create the chart
                var chart = new Highcharts.StockChart({
                    chart: {
                        renderTo: 'container',
                                events: {
                                        load: function(chart) {
                                                this.setTitle(null, {
                                                        text: 'Built chart at '+ (new Date() - start) +'ms'
                                                });
                                        }
                                },
                                zoomType: 'x'
                    },
        
                    rangeSelector: {
                        buttons: [{
                            type: 'hour',
                            count: 1,
                            text: '1h'
                        }, {
                            type: 'day',
                            count: 1,
                            text: '1d'
                        }, {
                            type: 'week',
                            count: 1,
                            text: '1w'
                        }, {
                                        type: 'month',
                            count: 1,
                            text: '1m'
                        }, {
                            type: 'month',
                            count: 6,
                            text: '6m'
                        }, {
                            type: 'year',
                            count: 1,
                            text: '1y'
                        }, {
                            type: 'all',
                            text: 'All'
                        }],
                        selected: 3
                    },
        
                        yAxis: {
                                title: {
                                        text: 'Temperature (°C)'
                                }
                        },
        
                    title: {
                                text: 'Температура в г. Харькове'
                        },
        
                        subtitle: {
                                text: 'Built chart at...' // dummy text to reserve space for dynamic subtitle
                        },
                        
                        series: [{
                        name: 'Temperature',
                        data: data,
                        pointStart: (startDate.getTime()- startDate.getTimezoneOffset() * 60*1000),
                        pointInterval: chart_interval * 1000,
                        tooltip: {
                                valueDecimals: 1,
                                valueSuffix: '°C'
                        }
                    }]
        
                });
        });
});

                </script>
        </head>
        <body>
<script src="./js/highstock.js"></script>
<script src="./js/modules/exporting.js"></script>

<div id="container" style="height: 500px; min-width: 500px"></div>
        </body>
</html>
