require([
    'jquery', 'highcharts/highstock', 'highcharts/highstock/modules/data'
], function ($, Highstock) {
    $(function() {
        $('[data-highchart]').each(function() {
            var chartConfig = $(this).data('highchart');
            Highstock.stockChart(this, chartConfig);
        });
    });
});
