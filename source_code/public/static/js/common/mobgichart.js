///<reference path="jquery.d.ts" />
///<reference path="diy.d.ts" />
'use strict';
var CHECK_FALSE = false;
var CHECK_TRUE = true;
// 磨基报表
var Mg;
(function (Mg) {
    // 报表
    var Report = /** @class */ (function () {
        function Report() {
            this.ready = 1;
            this.version = '20171213.1400';
            this.compare = false;
            this.myDim = []; //查询维度
            this.dimBar = null;
            this.defaultDim = null;
            this.defaultPki = 'ad_income';
            this.sortBy = '';
            this.sortArrow = 1;
            this.dims = {}; //维度定义
            this.api = null; //
            this.default_dim_fields = null; //默认查询维度
            this.data = null; //查询数据结果
            this.containerChart = '#data_charts'; //图表选择器ID
            this.containerChartHeader = '#chart_header'; //图表头选择器ID
            this.containerTable = '#mg_talbe'; //图表选择器ID
            this.tableHeader = []; //表格头定义
            this.options = {
                box: {},
                api: {},
                conf: {},
                default: {
                    dimDom: '#dim',
                    dimValue: ['app_key']
                },
                kpi: {},
                dim: {
                    default_dim_dom: '#dim',
                    default_dim_fields: {},
                    default_dim_value: ['app_key'],
                    dims: {}
                },
                chart: {
                    show: 1,
                    showTabs: 1,
                    pieItem: [
                        'app_key',
                        'platform',
                        'ad_type',
                        'channel_gid',
                        'ads_id',
                        'ssp_id',
                        'pos_key',
                        'country',
                        'province',
                        'app_version',
                        'sdk_version'
                    ]
                },
                table: {
                    show: 1,
                    header: '',
                    sortBy: ''
                },
                dateRange: ''
            };
            this.params = {
                sdate: '',
                edate: '',
                dims: {},
                kpis: {}
            };
        }
        Report.prototype.setDateRange = function (sdate, edate) {
            this.params.sdate = sdate;
            this.params.edate = edate;
            return this;
        };
        Report.prototype.setDateRangePlugin = function (pluginDom) {
            this.options.dateRange = '#date_range';
            return this;
        };
        Report.prototype.setTableShow = function (val) {
            this.options.table.show = val;
            return this;
        };
        Report.prototype.setChartShow = function (val) {
            this.options.chart.show = val;
            return this;
        };
        Report.prototype.setDefaultDims = function (val) {
            this.options.dim.default_dim_value = val;
            return this;
        };
        Report.prototype.setDefaultKpis = function (val) {
            this.options.kpi = val;
            return this;
        };
        Report.prototype.setCompare = function (val) {
            this.compare = val;
            if (this.compare) {
                this.addDim('days');
                this.addDim('hours');
            }
            return this;
        };
        Report.prototype.init = function (option) {
            if (option === void 0) { option = {}; }
            this.bingConfig(option);
            this.run();
        };
        Report.prototype.bingConfig = function (option) {
            if (option === void 0) { option = {}; }
            var default_option = this.options;
            this.options = jQuery.extend({}, default_option, option);
            return this;
        };
        Report.prototype.run = function () {
            if (typeof this.options.api != 'undefined') {
                this.api = this.options.api;
            }
            if (typeof this.options.kpi != 'undefined') {
                this.initKpi(this.options.kpi);
            }
            if (typeof this.options.dim != 'undefined') {
                this.initDim(this.options.dim);
            }
            if (typeof this.options.dim.default_dim_dom != 'undefined') {
                this.dimBar = $(this.options.dim.default_dim_dom);
            }
            if (typeof this.options.dim.dims != 'undefined') {
                // this.dims = this.options.dim.dims;
            }
            this.afterInit();
            this.bingParams();
            if (typeof this.options.table.sortBy != 'undefined' &&
                this.options.table.sortBy != '') {
                this.sortBy = this.options.table.sortBy;
            }
            else {
                this.sortBy = this.getDims().pop();
            }
            this.doAction();
        };
        Report.prototype.chkKpi = function () {
            // kpi empty
            if (this.tableHeader.length > 0 &&
                this.tableHeader.indexOf(this.defaultPki) == -1) {
                this.defaultPki = this.tableHeader[0];
            }
        };
        Report.prototype.initKpi = function (kpi) {
            var conf = this.options.conf;
            var tableHeader = [];
            $.each(kpi, function (i, item) {
                var dlDOM = $('#kpi_' + i);
                var chkbox;
                dlDOM.empty().append($('<dt>').append(dlDOM.attr('title')));
                $.each(item, function (key, val) {
                    if (dlDOM.length > 0) {
                        chkbox = $('<input>').attr({
                            type: 'checkbox',
                            class: 'kpi',
                            value: key,
                            checked: val > 0
                        });
                        if (conf.hasOwnProperty(key)) {
                            dlDOM.append($('<dd>').append($('<label>').append(chkbox, conf[key]['name'])));
                        }
                        else {
                            console.log(key);
                        }
                    }
                    if (val > 0) {
                        tableHeader.push(key);
                    }
                });
            });
            this.tableHeader = this.sortKpi(tableHeader);
        };
        Report.prototype.sortKpi = function (header) {
            var sortedHeader = [];
            $.each(this.options.conf, function (key, name) {
                if (key.in_array(header)) {
                    sortedHeader.push(key);
                }
            });
            return sortedHeader;
        };
        Report.prototype.initDim = function (dim_options) {
            var myblk;
            if ($('#blk')) {
                myblk = $('#blk');
            }
            else {
                myblk = $('<div>').attr('id', 'blk');
            }
            var myblkDefault = $('<ul>')
                .attr({ id: 'blk_default', class: 'blk_view' })
                .css({ width: '140px', 'margin-left': '5px', left: '60px' });
            myblk.append(myblkDefault);
            $('body').append(myblk);
            if (typeof dim_options != 'undefined') {
                if (typeof dim_options.default_dim_dom != 'undefined') {
                    this.dimBar = $(dim_options.default_dim_dom);
                }
                if (typeof dim_options.dims != 'undefined') {
                    this.dims = dim_options.dims;
                }
                if (typeof dim_options.default_dim_fields != 'undefined') {
                    this.default_dim_fields = dim_options.default_dim_fields;
                }
                $.each(this.options.box, function (key, item) {
                    var mydom = $('<div>')
                        .attr({ id: 'blk_' + key, type: key })
                        .addClass('blk_view');
                    var boxMod = item.hasOwnProperty('mod')
                        ? item.mod
                        : 'select_box,search_box,bottom_box';
                    var boxStyle = item.hasOwnProperty('style')
                        ? item.style
                        : 'max-height:200px;';
                    if (boxMod) {
                        mydom
                            .attr({ mod: boxMod })
                            .append('<ul class="check_box" style="' + boxStyle + '"></ul>');
                    }
                    myblk.append(mydom);
                });
                if (this.dimBar.length > 0) {
                    this.defaultDim = $('<dd class="dim_default_btn"><a>+添加 ▼</a></dd>');
                    this.dimBar
                        .html('<dt class="dim-label" > 维度：</dt>')
                        .append(this.defaultDim)
                        .append('<dd class="dim_execute_btn"><a>执行</a></dd>');
                    var html_default = '';
                    //默认添加按钮
                    if (dim_options.default_dim_fields) {
                        html_default = '';
                        for (var key in dim_options.default_dim_fields) {
                            html_default +=
                                dim_options.default_dim_fields[key] == '-'
                                    ? '<hr>'
                                    : '<li id="' +
                                        key +
                                        '" class="add_dim">' +
                                        dim_options.default_dim_fields[key] +
                                        '</li>';
                        }
                        myblkDefault.html(html_default).css({ top: this.defaultDim.height });
                    }
                    this.dimBar.append(myblkDefault);
                }
            }
        };
        Report.prototype.ajaxData = function (callback) {
            var that = this;
            if (this.api.date !== null) {
                for (var key in this.params) {
                    if (this.params[key] == '') {
                        delete this.params[key];
                    }
                }
                $.ajax({
                    type: 'GET',
                    url: this.api.data,
                    data: this.params,
                    dataType: 'json',
                    success: function (response) {
                        if (response == null) {
                            alert('超时登出');
                        }
                        else {
                            callback(response);
                        }
                    },
                    beforeSend: function () {
                        that.ready = 0;
                        $('.mask')
                            .text('正在加载。。。')
                            .fadeIn(500);
                    },
                    complete: function (a, b) {
                        if (a.status == 401) {
                            alert('由于您很长时间未在线使用，请您重新登录！');
                        }
                        else if (b == 'error') {
                            alert('请求数据失败！');
                        }
                        $('.mask').fadeOut(300, function () {
                            that.ready = 1;
                            console.log('ready');
                        });
                    }
                });
            }
        };
        Report.prototype.ajaxConf = function (filed, callback) {
            if (filed === void 0) { filed = null; }
            if (callback === void 0) { callback = function () { }; }
            if (this.api.conf !== null) {
                $.ajax({
                    type: 'POST',
                    url: this.api.conf,
                    data: { kpis: filed.join('|'), token: token },
                    dataType: 'json',
                    success: function (response) {
                        callback(response);
                    }
                });
            }
        };
        Report.prototype.getConfStr = function (callback) {
            var confarr = [];
            if (this.api.date !== null) {
                var dims = this.getDims();
                this.params.dims = dims.join(',');
                for (var key in this.params) {
                    if (this.params[key] == '') {
                        delete this.params[key];
                    }
                }
                $.each(this.params, function (key, name) {
                    confarr.push(key + '=' + name);
                });
            }
            return confarr.join('&');
        };
        Report.prototype.buildLineChart = function (dims, field) {
            var dayDim = 'days';
            var hourDim = 'hours';
            var conf = this.options.conf;
            var chart = {};
            var chartName = conf[field]['name'];
            chart.data = {};
            chart.serialdata = [];
            chart.categories = [];
            chart.tickInterval = 1;
            var items = this.data.table;
            var serialname = '';
            var dimName = [];
            var xxDims = [];
            var xxDimName = [];
            var xxName = '';
            // if (items.length == 0) {
            //     return;
            // }
            var count = items.length;
            if (dayDim.in_array(dims)) {
                if (!this.compare) {
                    xxDims.push(dayDim);
                    dims.splice($.inArray(dayDim, dims), 1);
                }
            }
            if (hourDim.in_array(dims)) {
                xxDims.push(hourDim);
                dims.splice($.inArray(hourDim, dims), 1);
            }
            for (var _i = 0, items_1 = items; _i < items_1.length; _i++) {
                var item = items_1[_i];
                serialname = '';
                dimName = [];
                xxDimName = [];
                xxName = '';
                for (var _a = 0, xxDims_1 = xxDims; _a < xxDims_1.length; _a++) {
                    var xxDim = xxDims_1[_a];
                    xxDimName.push(item[xxDim]);
                }
                xxName = xxDimName.join(' ');
                if (!xxName.in_array(chart.categories)) {
                    chart.categories.push(xxName);
                }
                if (dims.length > 0) {
                    $.each(dims, function (i, dim) {
                        dimName.push(item[dim]);
                    });
                    serialname = dimName.join(',');
                }
                else {
                    serialname = conf[field]['name'];
                }
                if (!chart.data.hasOwnProperty(serialname)) {
                    chart.data[serialname] = {};
                }
                chart.data[serialname][xxName] = parseFloat(item[field]);
            }
            chart.categories.sort();
            $.each(chart.data, function (name, data) {
                var serialdata = [];
                $.each(chart.categories, function (i, val) {
                    if (data.hasOwnProperty(val)) {
                        serialdata.push(data[val]);
                    }
                    else {
                        serialdata.push(0);
                    }
                });
                if (Math.max.apply(Math, serialdata) > 0)
                    chart.serialdata.push({
                        name: name,
                        data: serialdata
                    });
            });
            chart.serialdata.sort(function (a, b) {
                return Math.max.apply(Math, b.data) - Math.max.apply(Math, a.data);
            });
            // if (chart.categories.length <= 24) {
            //   chart.tickInterval = 1
            // } else {
            //   chart.tickInterval = Math.floor(count / 12)
            // }
            var params = {
                title: { text: '', x: -20 },
                xAxis: {
                    // tickInterval: chart.tickInterval,
                    categories: chart.categories
                },
                yAxis: {
                    title: { text: '' },
                    min: 0,
                    plotLines: [
                        {
                            value: 0,
                            width: 1,
                            color: '#808080'
                        }
                    ],
                    showFirstLabel: true
                },
                legend: {},
                tooltip: {
                    crosshairs: true
                    // shared: true
                },
                series: chart.serialdata
            };
            if (chart.serialdata.length > 1) {
                params.legend = {
                    layout: 'vertical',
                    align: 'right',
                    verticalAlign: 'middle',
                    borderWidth: 0
                };
            }
            $(this.containerChart).highcharts(params);
        };
        Report.prototype.buildPieChart = function (dim, field) {
            if (field === void 0) { field = 'third_views'; }
            var conf = this.options.conf;
            var chart = {};
            chart.name = conf[field]['name'];
            chart.data = [];
            var data = {};
            $.each(this.data.table, function (i, item) {
                if (!data.hasOwnProperty(item[dim])) {
                    data[item[dim]] = 0;
                }
                data[item[dim]] += parseFloat(item[field]);
            });
            var total = 0;
            if (typeof data === 'undefined') {
                return;
            }
            $.each(data, function (i, item) {
                // chart.data.push([item[dim], parseFloat(item[field])]);
                chart.data.push([i, item]);
            });
            chart.data.sort(function (x, y) {
                return y[1] - x[1];
            });
            $(this.containerChart).highcharts({
                title: {
                    text: null
                },
                tooltip: {
                    pointFormat: '{series.name}: <b>{point.y:.2f}</b>'
                },
                plotOptions: {
                    pie: {
                        allowPointSelect: true,
                        cursor: 'pointer',
                        dataLabels: {
                            enabled: true,
                            format: '<b>{point.name}</b>: {point.percentage:.1f} %'
                        },
                        showInLegend: true
                    }
                },
                series: [
                    {
                        type: 'pie',
                        name: chart.name,
                        data: chart.data
                    }
                ]
            });
        };
        Report.prototype.buildChart = function (field) {
            if (field === void 0) { field = this.defaultPki; }
            if (this.options.chart.show == 0) {
                return false;
            }
            var dims = this.getDims();
            var dayDim = 'days';
            var hourDim = 'hours';
            $('#chart_header')
                .find('.index')
                .each(function (i, key) {
                $(this).removeClass('sign-selected');
                if ($(this).attr('field') == field) {
                    $(this).addClass('sign-selected');
                }
            });
            if (dims.length > 0) {
                if (dayDim.in_array(dims) || hourDim.in_array(dims)) {
                    this.buildLineChart(dims, field);
                }
                else if (dims[dims.length - 1].in_array(this.options.chart.pieItem)) {
                    this.buildPieChart(dims[dims.length - 1], field);
                }
            }
            $(this.containerChart)
                .children('div')
                .children('svg')
                .children('text:last-child')
                .hide();
        };
        Report.prototype.getTableHeader = function () {
            var tableHeader = [];
            var table = this.options.table;
            var dims = this.getDims();
            var kpis = this.tableHeader;
            tableHeader = dims.concat(kpis);
            if (table.hasOwnProperty('header') && table.header != '') {
                tableHeader = tableHeader.concat(table.header.split(','));
            }
            return tableHeader;
        };
        Report.prototype.buildTable = function (data) {
            if (data === void 0) { data = null; }
            if (this.options.table.show == 0) {
                return false;
            }
            var val = '';
            var conf = this.options.conf;
            var divDom = $(this.containerTable);
            var tableDom = $('<table>');
            var tbodyDom = $('<tbody>');
            tableDom.addClass('mui-data-table');
            var tableHeader = this.getTableHeader();
            var trDom = {};
            $.each(data['table'], function (i, item) {
                trDom = $('<tr>');
                $.each(tableHeader, function (j, name) {
                    val = item[name];
                    trDom.append($('<td>').append(val));
                });
                if (tableHeader[0] == 'days') {
                    tbodyDom.prepend(trDom);
                }
                else {
                    tbodyDom.append(trDom);
                }
            });
            tableDom.prepend(tbodyDom);
            trDom = $('<tr>');
            $.each(tableHeader, function (j, key) {
                var headerName = conf[key] ? conf[key]['name'] : null;
                trDom.append($('<th>')
                    .append(headerName)
                    .addClass('sortby')
                    .attr('field', key));
                if (headerName == null) {
                    console.log(key, headerName, 'is null');
                }
            });
            tableDom.prepend($('<thead>').prepend(trDom));
            if (data['total'] instanceof Object) {
                trDom = $('<tr>');
                $.each(tableHeader, function (j, name) {
                    val = data['total'][name];
                    trDom.append($('<td>').append(val));
                });
                tableDom.append($('<tfoot>').append(trDom));
            }
            divDom.empty().append(tableDom);
        };
        Report.prototype.sortTable = function (field, order) {
            if (order === void 0) { order = 0; }
            var data = this.data['table'];
            var newJson = $.extend(true, {}, this.data);
            var tbodyDom = $(this.containerTable).find('tbody');
            var trDom = {};
            var tableHeader = this.getTableHeader();
            var val;
            tbodyDom.empty();
            data.sort(function (a, b) {
                if (a.hasOwnProperty(field) && b.hasOwnProperty(field)) {
                    return isNaN(a[field])
                        ? a[field]
                            .replace(/<[^>]+>/g, '')
                            .localeCompare(b[field].replace(/<[^>]+>/g, ''))
                        : a[field] - b[field];
                }
                else {
                    return 0;
                }
            });
            for (var _i = 0, data_1 = data; _i < data_1.length; _i++) {
                var item = data_1[_i];
                trDom = $('<tr>');
                for (var _a = 0, tableHeader_1 = tableHeader; _a < tableHeader_1.length; _a++) {
                    var key = tableHeader_1[_a];
                    trDom.append($('<td>').append(item[key]));
                }
                if (order) {
                    tbodyDom.prepend(trDom);
                }
                else {
                    tbodyDom.append(trDom);
                }
            }
        };
        Report.prototype.bingParams = function () {
            if (this.options.dim.default_dim_value) {
                var defVal = (this.dims = this.options.dim.default_dim_value);
                for (var key in defVal) {
                    var val = [];
                    this.addDim(key, defVal[key], false);
                    if (key == 'app_key') {
                        var posblk = $('#blk_pos_key').find('.check_box');
                        if (defVal[key].length > 0) {
                            posblk.find('.parent_box').hide();
                            $.each(defVal[key], function (i, appkey) {
                                posblk.find('.parent_key_' + appkey).show();
                            });
                        }
                        else {
                            posblk.find('.parent_box').show();
                        }
                    }
                    if (defVal[key].length > 0) {
                        var myblk = $('#blk_' + key);
                        myblk
                            .find('.check_box')
                            .find('input.' + key)
                            .each(function () {
                            var myval = $(this).val();
                            if (myval.in_array(defVal[key])) {
                                $(this).attr('checked', CHECK_TRUE);
                                val.push(myval);
                            }
                        });
                    }
                }
            }
        };
        Report.prototype.chgPlatform = function (sys) {
            if (sys === void 0) { sys = []; }
            if (sys.length > 0) {
                this.addDim('platform', sys.join(','), true);
            }
            else {
                this.removeDim('platform', true);
            }
        };
        Report.prototype.doAction = function () {
            var that = this;
            this.params.dims = {};
            if (this.options.dateRange) {
                this.params.sdate = $(this.options.dateRange).attr('sdate');
                this.params.edate = $(this.options.dateRange).attr('edate');
            }
            else if (this.params.sdate == '' || this.params.edate == '') {
                return false;
            }
            this.chkKpi();
            var dims = this.getDims(); //维度
            this.params.compare = this.compare;
            if (this.compare) {
                var index = dims.indexOf('hours');
                if (index == -1) {
                    this.addDim('hours');
                    dims.push('hours');
                }
                index = dims.indexOf('days');
                if (index == -1) {
                    this.addDim('days');
                    dims.push('days');
                }
            }
            for (var key in this.dims) {
                if (typeof this.dims[key] === 'string') {
                    this.params[key] = this.dims[key];
                }
                else {
                    this.params[key] = this.dims[key].join(',');
                }
            }
            this.params.dims = dims.join(',');
            this.params.theader = this.getTableHeader().join(',');
            this.ajaxData(function (data) {
                if (data.data) {
                    that.data = data.data;
                    that.buildTable(data.data);
                    that.updateChartHeader();
                    that.buildChart();
                }
                else {
                    console.log(data.table);
                }
            });
        };
        Report.prototype.updateKpi = function (dimName, val) {
            this.params[dimName] = val.join('|');
            this.tableHeader = this.sortKpi(val);
            this.ajaxConf(this.tableHeader);
            // this.doAction()
        };
        Report.prototype.updateChartHeader = function () {
            if (this.options.chart.showTabs) {
                var tableHeader = this.tableHeader;
                var conf = this.options.conf;
                var table = $('#chart_header');
                table.empty();
                $.each(tableHeader, function (i, key) {
                    var th = $('<th>')
                        .attr({ field: key, class: 'index', title: conf[key]['title'] })
                        .append(conf[key]['name']);
                    table.append(th);
                });
            }
        };
        Report.prototype.updayDim = function (dimName, val, doit) {
            if (doit === void 0) { doit = false; }
            this.dims[dimName] = val.join(',');
            if (doit) {
                this.doAction();
            }
        };
        Report.prototype.export = function () {
            var csvContent = '';
            var trTxt = [];
            $(this.containerTable)
                .find('tr')
                .each(function (index, el) {
                var tdTxt = [];
                $(this)
                    .find('td,th')
                    .each(function () {
                    tdTxt.push($(this).text());
                });
                trTxt.push(tdTxt.join(','));
            });
            csvContent = trTxt.join('\n');
            var link = window.document.createElement('a');
            link.setAttribute('href', 'data:text/csv;charset=utf-8,%EF%BB%BF' + encodeURI(csvContent));
            var date = new Date();
            var year = date.getFullYear();
            var month = date.getMonth() + 1;
            var day = date.getDate();
            if (month <= 9) {
                month = '0' + month;
            }
            if (day <= 9) {
                day = '0' + day;
            }
            link.setAttribute('download', 'mobgi' + year + month + day + '.csv');
            link.click();
        };
        Report.prototype.getDims = function () {
            var dims = [];
            $('.dim').each(function () {
                dims.push($(this).data('type'));
            });
            if (dims.length == 0) {
                for (var key in this.dims) {
                    dims.push(key);
                }
            }
            return dims;
        };
        Report.prototype.getKpis = function () {
            var kpis = [];
            var mykpi = this.options.kpi;
            for (var pet in mykpi) {
                for (var ta in mykpi[pet]) {
                    if (mykpi[pet][ta]) {
                        kpis.push(ta);
                    }
                }
            }
            return kpis;
        };
        Report.prototype.addDim = function (dimType, dimValue, doit) {
            if (dimValue === void 0) { dimValue = ''; }
            if (doit === void 0) { doit = false; }
            if ($('#dim_' + dimType).length > 0) {
                console.log($('#dim_' + dimType).data('type'));
            }
            else {
                this.dims[dimType] = dimValue;
                var dimName = this.default_dim_fields[dimType];
                var html = '<dd><a class="dim" id="dim_' +
                    dimType +
                    '" data-type="' +
                    dimType +
                    '">' +
                    dimName +
                    '</a><a class="dim_remove" data-type="' +
                    dimType +
                    '">⊕' +
                    '</a></dd>';
                if (this.defaultDim) {
                    this.defaultDim.before(html);
                }
                var that = this;
                $('#dim').sortable({
                    stop: function () {
                        that.doAction();
                    }
                });
                if (doit) {
                    this.doAction();
                }
            }
        };
        Report.prototype.removeDim = function (dimType, doit) {
            if (doit === void 0) { doit = false; }
            // $("#blk_" + dimType).find("input:checked").prop("checked", false);
            $('#blk_' + dimType)
                .find('input:checked')
                .removeAttr('checked');
            if (this.dims.hasOwnProperty(dimType)) {
                delete this.dims[dimType];
            }
            if (this.params.hasOwnProperty(dimType)) {
                delete this.params[dimType];
            }
            if (doit) {
                this.doAction();
            }
        };
        Report.prototype.afterInit = function () {
            if (this.options.table.show == 0) {
                return false;
            }
            var that = this;
            var options_dims = this.options.dim.dims;
            if ($('.mask')) {
                $('body').append('<div class="mask"></div>');
            }
            if (this.dimBar.length > 0) {
                var html_default = '';
                var childMap = {
                    pos_key: 'app_key',
                    ad_sub_type: 'ad_type',
                    province: 'country',
                    ad_id: 'unit_id',
                    orig_id: 'unit_id'
                };
                //序列化
                $.each(options_dims, function (key, mydim) {
                    if (key.in_array(['days', 'hours'])) {
                        html_default = '';
                        $.each(mydim, function (i, item) {
                            html_default +=
                                '<li><a class="days useBtn" type="' +
                                    i +
                                    '">' +
                                    item +
                                    '</a></li>';
                        });
                        $('#blk_' + key)
                            .find('.check_box')
                            .html(html_default);
                    }
                    else if (key.in_array([
                        'app_key',
                        'orig_id',
                        'platform',
                        'channel_gid',
                        'ssp_id',
                        'ad_type',
                        'account_id',
                        'unit_id',
                        'province',
                        'flow_id',
                        'conf_id'
                    ])) {
                        html_default = '';
                        $.each(mydim, function (i, item) {
                            html_default +=
                                '<li data-name="' +
                                    item +
                                    '"><label><input type="checkbox" class="' +
                                    key +
                                    '" value="' +
                                    i +
                                    '">' +
                                    item +
                                    '</label></li>';
                        });
                        $('#blk_' + key)
                            .find('.check_box')
                            .html(html_default);
                    }
                    else if (key.in_array([
                        'pos_key',
                        'ad_sub_type',
                        'ad_id',
                        'ads_id',
                        'orig_id',
                        'country'
                    ])) {
                        html_default = '';
                        var parentName = '';
                        $.each(mydim, function (parentKey, childs) {
                            if (childMap.hasOwnProperty(key)) {
                                parentName = options_dims[childMap[key]][parentKey];
                            }
                            else {
                                parentName = parentKey;
                            }
                            html_default +=
                                '<ul class="parent_box parent_key_' +
                                    parentKey +
                                    '" data-showchild=true >';
                            html_default +=
                                '<li><label><input type="checkbox" class="li_checkbox" value="' +
                                    parentKey +
                                    '">' +
                                    parentName +
                                    '</label><a class="show_children">▼</a>';
                            html_default += '<ul class=" childen_box">';
                            $.each(childs, function (childKey, childName) {
                                html_default +=
                                    '<li  data-name="' +
                                        childName +
                                        '"><label><input type="checkbox"  class="' +
                                        key +
                                        ' li_checkbox"  value="' +
                                        childKey +
                                        '">' +
                                        childName +
                                        '</label></li>';
                            });
                            html_default += '</ul></li></ul>';
                        });
                        $('#blk_' + key)
                            .find('.check_box')
                            .html(html_default);
                    }
                });
                //二级操作
                $('.show_children').click(function () {
                    $(this)
                        .next('ul')
                        .toggle();
                    if ($(this)
                        .next('ul')
                        .css('display') == 'none') {
                        $(this).html('▲');
                    }
                    else {
                        $(this).html('▼');
                    }
                });
                $('.li_checkbox').click(function () {
                    if ($(this)
                        .parents('ul')
                        .hasClass('childen_box')) {
                        if ($(this).attr('checked')) {
                            $(this)
                                .parents('ul.childen_box')
                                .siblings('label')
                                .children('.li_checkbox')
                                .attr('checked', CHECK_TRUE);
                        }
                        else {
                            if ($(this)
                                .parents('.childen_box')
                                .find('.li_checkbox:checked').length == 0) {
                                $(this)
                                    .parents('.childen_box')
                                    .siblings('label')
                                    .children('.li_checkbox')
                                    .attr('checked', CHECK_FALSE);
                            }
                        }
                    }
                    else {
                        //父节点
                        if ($(this).attr('checked')) {
                            var myUlParents = $(this).parents('ul.parent_box');
                            var can_show = myUlParents.data('showchild');
                            var mytrigger = myUlParents.data('trigger');
                            var has_search = $(this)
                                .parents('.blk_view')
                                .find('.search_input')
                                .val() != '';
                            $(this)
                                .parent()
                                .siblings('ul.childen_box')
                                .find('.li_checkbox')
                                .each(function () {
                                var is_visible = $(this).is(':visible');
                                if (has_search) {
                                    $(this).attr('checked', is_visible);
                                }
                                else {
                                    $(this).attr('checked', is_visible || can_show);
                                    if (can_show) {
                                        $(mytrigger).trigger('change');
                                    }
                                }
                            });
                        }
                        else {
                            $(this)
                                .parent()
                                .siblings('ul.childen_box')
                                .find('.li_checkbox')
                                .attr('checked', CHECK_FALSE);
                        }
                    }
                });
                // 添加维度
                var that = this;
                $('.add_dim').click(function (event) {
                    var ithis = $(this);
                    that.addDim(ithis.attr('id'));
                    $('#blk_default').hide();
                });
                // 删除维度
                $('.dim_remove').live('click', function (e) {
                    $(this)
                        .parent()
                        .remove();
                    that.removeDim($(this).data('type'));
                    return false;
                });
                // 执行动作
                $('.dim_execute_btn').live('click', function (e) {
                    if (that.ready) {
                        that.doAction();
                    }
                    else {
                        console.log('wait...');
                    }
                    return false;
                });
                //默认添加维度
                this.defaultDim.click(function () {
                    $('#blk_default')
                        .show()
                        .css({ left: $(this).position().left });
                });
            }
            $('#kpi').click(function (e) {
                var ddom = $(this);
                if ($(document).width() >
                    $('#blk_kpi').width() + $(this).offset().left) {
                    $('#blk_kpi')
                        .css({
                        left: $(this).position().left,
                        top: ddom.offset().top + ddom.height() + 5
                    })
                        .show();
                }
                else {
                    var offset = $(this).width() - $('#blk_kpi').width();
                    $('#blk_kpi')
                        .css({
                        left: $(this).position().left + offset,
                        top: ddom.offset().top + ddom.height() + 5
                    })
                        .show();
                }
            });
            $('#blk_kpi').delegate('dt', 'click', function () {
                $(this)
                    .parent()
                    .find('dd')
                    .toggle('fast', function () {
                    var is = $(this).css('display');
                    if (is == 'block') {
                        $(this)
                            .parent()
                            .find('dt')
                            .addClass('dtafter');
                    }
                    else {
                        $(this)
                            .parent()
                            .find('dt')
                            .removeClass('dtafter');
                    }
                });
            });
            $('#dim').delegate('.dim', 'click', function (e) {
                var dimType = $(this).data('type');
                var ddom = $(this).parent();
                $('#blk_' + dimType)
                    .css({
                    left: ddom.offset().left,
                    top: ddom.offset().top + ddom.height() + 5
                })
                    .show();
            });
            $('.blk_view').each(function () {
                var viewDom = $(this);
                if (typeof viewDom.attr('mod') != 'undefined') {
                    $.each(viewDom.attr('mod').split(','), function (i, item) {
                        if (item == 'search_box') {
                            viewDom.prepend('<div class="search_box"><input class="search_input" maxlength="10"><img width="10" height="10" class="cls_input" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAoAAAAKCAYAAACNMs+9AAAAQklEQVR42mMoLS1NAOL3QGzAgAZAYlC5BAYo4z+6YiRFYDkMASjfAKsBWCSw2oJN8X9c7iZOIVFWE+0ZUoKHqAAHAN50nAWGasN5AAAAAElFTkSuQmCC"></div>');
                        }
                        else if (item == 'select_box') {
                            var selectBox = $('<div class="select_box"><a class="allselect">全选</a><a class="noselect">全不选</a></div>');
                            viewDom.prepend(selectBox);
                        }
                        else if (item == 'bottom_box') {
                            viewDom.append('<div class="bottom_box"><a class="btn useBtn">应用</a><a class="btn closeBtn">取消</a></div>');
                        }
                    });
                }
            });
            $('.blk_view')
                .delegate('.cls_input', 'click', function (e) {
                $(this)
                    .siblings('input')
                    .val('')
                    .trigger('keyup')
                    .focus();
            })
                .delegate('.search_input', 'keyup', function (e) {
                var keyword = $(this)
                    .val()
                    .toString()
                    .toLowerCase();
                var check_boxDom = $(this)
                    .parents('.blk_view')
                    .find('.check_box');
                if (check_boxDom.children('li').length > 0) {
                    check_boxDom.children('li').each(function () {
                        var name = $(this)
                            .data('name')
                            .toString()
                            .toLowerCase();
                        if (name.match(keyword) == null) {
                            $(this).hide();
                        }
                        else {
                            $(this).show();
                        }
                    });
                }
                else {
                    check_boxDom.children('ul.parent_box').each(function () {
                        var showchild = $(this).data('showchild');
                        $(this)
                            .find('.childen_box')
                            .children('li')
                            .each(function () {
                            var name = $(this)
                                .data('name')
                                .toString()
                                .toLowerCase();
                            if (showchild && name.match(keyword) == null) {
                                $(this).hide();
                            }
                            else {
                                $(this).show();
                            }
                        });
                    });
                }
            })
                .delegate('.allselect', 'click', function (e) {
                var myblk = $(this)
                    .parents('.blk_view')
                    .find('.check_box');
                myblk.find('input:checkbox').each(function () {
                    var myUlParents = $(this).parents('ul.parent_box');
                    var can_show = myUlParents.data('showchild');
                    var mytrigger = myUlParents.data('trigger');
                    var has_search = $(this)
                        .parents('.blk_view')
                        .find('.search_input')
                        .val() != '';
                    var is_visible = $(this).is(':visible');
                    if (has_search) {
                        $(this).attr('checked', is_visible);
                    }
                    else {
                        $(this).attr('checked', is_visible || can_show);
                        if (can_show) {
                            $(mytrigger).trigger('change');
                        }
                    }
                });
            })
                .delegate('.noselect', 'click', function (e) {
                $(this)
                    .parents('.blk_view')
                    .find('.check_box')
                    .find('input:checkbox')
                    .each(function () {
                    $(this).attr('checked', CHECK_FALSE);
                });
            })
                .delegate('.closeBtn', 'click', function (e) {
                $(this)
                    .parents('.blk_view')
                    .hide();
                return false;
            })
                .delegate('.useBtn', 'click', function (e) {
                var myblk = $(this).parents('.blk_view');
                var mytype = myblk.attr('type');
                var val = [];
                if (mytype == 'kpi') {
                    myblk
                        .find('.check_box')
                        .find('input.' + mytype + ':checked')
                        .each(function () {
                        val.push($(this).val());
                    });
                }
                else {
                    myblk
                        .find('.check_box')
                        .find('input.' + mytype + ':checked:visible')
                        .each(function () {
                        val.push($(this).val());
                    });
                }
                var parentMap = {
                    app_key: ['pos_key'],
                    ad_type: ['ad_sub_type'],
                    country: ['province'],
                    unit_id: ['ad_id', 'orig_id']
                };
                if (parentMap.hasOwnProperty(mytype)) {
                    var subType = parentMap[mytype];
                    $.each(subType, function (k, subval) {
                        var posblk = $('#blk_' + subval).find('.check_box');
                        posblk.find('.parent_box').hide();
                        $.each(val, function (i, appkey) {
                            posblk.find('.parent_key_' + appkey).show();
                        });
                    });
                }
                if (mytype == 'kpi') {
                    that.updateKpi('kpi', val);
                }
                else if (mytype != '') {
                    that.updayDim(mytype, val);
                }
                $(this)
                    .parents('.blk_view')
                    .hide();
                return false;
            });
            $(this.containerChartHeader).delegate('.index', 'click', function (e) {
                that.defaultPki = $(this).attr('field');
                that.buildChart($(this).attr('field'));
            });
            $(this.containerTable).delegate('.sortby', 'click', function (e) {
                var ithis = $(this);
                ithis
                    .siblings('th')
                    .removeClass('desc')
                    .removeClass('asc');
                if (ithis.hasClass('desc')) {
                    ithis.addClass('asc').removeClass('desc');
                    that.sortTable(ithis.attr('field'), 0);
                }
                else {
                    ithis.addClass('desc').removeClass('asc');
                    that.sortTable(ithis.attr('field'), 1);
                }
            });
            // 点击空白处隐藏
            $(document).bind('mousedown', function (e) {
                var etarget = e.target || e.srcElement;
                $('.blk_view').each(function (index, val) {
                    var target = etarget;
                    while (target != document && target != this) {
                        target = target.parentNode;
                    }
                    if (target == document) {
                        $(this).hide();
                    }
                });
            });
        };
        return Report;
    }());
    Mg.Report = Report;
})(Mg || (Mg = {}));
String.prototype.in_array = function (arr) {
    var isArr = Object.prototype.toString.call(arr) === '[object Array]';
    if (isArr) {
        for (var index = 0, k = arr.length; index < k; index++) {
            if (this == arr[index]) {
                return true;
            }
        }
    }
    return false;
};
