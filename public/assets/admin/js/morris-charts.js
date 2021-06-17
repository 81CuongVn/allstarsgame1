!function($) {
    "use strict";

    var MorrisCharts = function() {};

    // creates Stacked chart
    MorrisCharts.prototype.createStackedChart  = function(element, data, xkey, ykeys, labels, lineColors) {
        Morris.Bar({
            element: element,
            data: data,
            xkey: xkey,
            ykeys: ykeys,
            stacked: true,
            labels: labels,
            hideHover: 'auto',
            resize: true, //defaulted to true
            gridLineColor: '#eeeeee',
            barColors: lineColors
        });
    },

    // creates area chart
    MorrisCharts.prototype.createAreaChart = function(element, pointSize, lineWidth, data, xkey, ykeys, labels, opacity,lineColors) {
        Morris.Area({
            element: element,
            pointSize: pointSize,
            lineWidth: lineWidth,
            data: data,
            xkey: xkey,
            ykeys: ykeys,
            labels: labels,
            fillOpacity: opacity,
            hideHover: 'auto',
            resize: true,
            gridLineColor: '#eef0f2',
            lineColors: lineColors
        });
    },

    // creates line chart
    MorrisCharts.prototype.createLineChart = function(element, data, xkey, ykeys, labels, preUnits, opacity, Pfillcolor, Pstockcolor, lineColors) {
        Morris.Line({
			element: element,
			data: data,
			xkey: xkey,
			ykeys: ykeys,
			labels: labels,
			fillOpacity: opacity,
			pointFillColors: Pfillcolor,
			pointStrokeColors: Pstockcolor,
			behaveLikeLine: true,
			gridLineColor: '#eef0f2',
			hideHover: 'auto',
			lineWidth: '3px',
			pointSize: 0,
			preUnits: preUnits,
			resize: true, // defaulted to true
			lineColors: lineColors,
			xLabels: 'month'
        });
    },

    // creates Bar chart
    MorrisCharts.prototype.createBarChart  = function(element, data, xkey, ykeys, labels, lineColors) {
        Morris.Bar({
            element: element,
            data: data,
            xkey: xkey,
            ykeys: ykeys,
            labels: labels,
            hideHover: 'auto',
            resize: true, //defaulted to true
            gridLineColor: '#eeeeee',
            barSizeRatio: 0.4,
            xLabelAngle: 35,
            barColors: lineColors
        });
    },

    // creates area chart with dotted
    MorrisCharts.prototype.createAreaChartDotted = function(element, pointSize, lineWidth, data, xkey, ykeys, labels, Pfillcolor, Pstockcolor, lineColors) {
        Morris.Area({
            element: element,
            pointSize: 3,
            lineWidth: 1,
            data: data,
            xkey: xkey,
            ykeys: ykeys,
            labels: labels,
            hideHover: 'auto',
            pointFillColors: Pfillcolor,
            pointStrokeColors: Pstockcolor,
            resize: true,
            smooth: false,
            behaveLikeLine: true,
            fillOpacity: 0.4,
            gridLineColor: '#eef0f2',
            lineColors: lineColors
        });
    },

    // creates Donut chart
    MorrisCharts.prototype.createDonutChart = function(element, data, colors) {
        Morris.Donut({
            element: element,
            data: data,
            barSize: 0.2,
            resize: true, //defaulted to true
            colors: colors
        });
    },

    $.MorrisCharts = new MorrisCharts;
	$.MorrisCharts.Constructor = MorrisCharts
}(window.jQuery);
