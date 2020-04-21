$(document).ready(function() {
    function getHighchartsData(url, csrf) {
        var data = [];

        // dummy data from API
        switch (url) {
            case 'revenue':
                data = [{
                    type: 'area',
                    name: 'Revenue',
                    // day: 7 units on xAxis
                    // week: 4 units on xAxis
                    // month: 12 units on xAxis
                    data: [1200, 13513, 16848, 123151, 846846, 1213879, 4875695],
                    pointStart: moment().startOf('week').valueOf('x'),
                    pointInterval: 24 * 3600 * 1000, // one day
                    color: 'rgba(0,128,0,0.5)',
                    marker: {
                        radius: 3,
                        lineWidth: 1,
                    },
                }];
                break;
            case 'client-count':
                data = [{
                    type: 'area',
                    name: 'Client Count',
                    // day: 7 units on xAxis
                    // week: 4 units on xAxis
                    // month: 12 units on xAxis
                    data: [1, 2, 4, 6, 8, 12, 20],
                    pointStart: moment().startOf('week').valueOf('x'),
                    pointInterval: 24 * 3600 * 1000, // one day
                    color: 'rgba(0,128,0,0.5)',
                    marker: {
                        radius: 3,
                        lineWidth: 1,
                    },
                }];
                break;
            case 'user-count':
                data = [{
                    type: 'area',
                    name: 'Users Count',
                    // day: 7 units on xAxis
                    // week: 4 units on xAxis
                    // month: 12 units on xAxis
                    data: [6, 12, 25, 32, 54, 62, 70],
                    pointStart: moment().startOf('week').valueOf('x'),
                    pointInterval: 24 * 3600 * 1000, // one day
                    color: 'rgba(0,128,0,0.5)',
                    marker: {
                        radius: 3,
                        lineWidth: 1,
                    },
                }];
                break;
        }

        return data;
    }

    // set default options
    Highcharts.setOptions({
        title: {
            text: '',
        },
        legend: {
            enabled: false,
        },
        credits: {
            enabled: false,
        },
        chart: {
            plotBackgroundColor: '#fafcfa',
            marginTop: 20,
            marginBottom: 30,
            marginLeft: 0,
            marginRight: 0,
        },
        xAxis: {
            type: 'datetime',
            format: '{value:%a}',
        },
        yAxis: {
            title: {
                enabled: false,
            },
            labels: {
                align: 'left',
                x: 4,
                y: -4,
                ovrflow: 'justify',
            },
        },
        tooltip: {
            formatter: function() {
                return '<span style="font-size: 10px">' + moment(this.point.category).format('ddd, D MMM YYYY') + '</span><br/><span style="color:' + this.point.color + '">\u25CF</span> ' + this.series.name + ': <b>' + this.point.y + '</b><br/>';
            }
        }
    });

    if ($('#highcharts-revenue').length > 0) {
        const $chart = $('#highcharts-revenue');
        const csrf   = $chart.data('csrf');
        const data   = getHighchartsData($chart.data('url'), csrf);

        Highcharts.chart('highcharts-revenue', {
            series: data,
            yAxis: {
                labels: {
                    formatter: function() {
                        return convertToCurrency(this.value, true, '$');
                    },
                },
            },
            tooltip: {
                formatter: function() {
                    return '<span style="font-size: 10px">' + moment(this.point.category).format('ddd, D MMM YYYY') + '</span><br/><span style="color:' + this.point.color + '">\u25CF</span> ' + this.series.name + ': <b>' + convertToCurrency(this.point.y, false, '$', ',', 0) + '</b><br/>';
                }
            }
        });
    }

    if ($('#highcharts-client-count').length > 0) {
        const $chart = $('#highcharts-client-count');
        const csrf   = $chart.data('csrf');
        const data   = getHighchartsData($chart.data('url'), csrf);

        Highcharts.chart('highcharts-client-count', {
            series: data,
        });
    }

    if ($('#highcharts-user-count').length > 0) {
        const $chart = $('#highcharts-user-count');
        const csrf   = $chart.data('csrf');
        const data   = getHighchartsData($chart.data('url'), csrf);

        Highcharts.chart('highcharts-user-count', {
            series: data,
        });
    }
});
