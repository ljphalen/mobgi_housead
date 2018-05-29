///<reference path="jquery.d.ts" />
///<reference path="diy.d.ts" />
///<reference path="echarts.d.ts" />
'use strict';
var CHECK_FALSE = false;
var CHECK_TRUE = true;
// 磨基报表
var Spm;
(function (Spm) {
    var Report = /** @class */ (function () {
        function Report() {
            this.version = '20180119.1400';
            this.ready = 1;
            this.compare = false;
            this.dimBar = null;
            this.defaultDim = null;
            this.defaultKpi = '';
            this.default_dim_fields = null; //默认查询维度
            this.data = null; //查询数据结果
            this.containerChart = '#data_charts'; //图表选择器ID
            this.containerChartHeader = '#chart_header'; //图表头选择器ID
            this.containerTable = '#mg_talbe'; //图表选择器ID
            this.tableHeader = []; //表格头定义
            this.modifiy = 0;
            this.echart = {};
            this.callback = null;
            this.handle = null;
            this.opt = {
                sortBy: '',
                sortArrow: 1,
                groups: {},
                groupname: {},
                conf: {},
                kpi: {},
                dim: {},
                api: { data: '', conf: '' },
                sort: { by: '', arrow: 1 },
                my_dim: ['days'],
                fix_dim: [],
                my_kpi: [],
                container_chart: '#spm_chart',
                container_talbe: '#spm_talbe',
                container_dim: '#dim',
                container_kpi: '#kpis',
                container_block: '#blk',
                chart_type: '',
                chart: {
                    show: 1,
                    showTabs: 1,
                    pieItem: ['app_key', 'platform', 'channel_id', 'channel_gid']
                },
                table: {
                    show: 1,
                    header: ''
                },
                dateRange: '#date_range',
                box: {}
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
            this.opt.dateRange = pluginDom;
            return this;
        };
        Report.prototype.setParams = function (key, val) {
            this.params[key] = val;
            return this;
        };
        Report.prototype.setCallback = function (func) {
            this.callback = func;
            return this;
        };
        Report.prototype.setHandle = function (func) {
            this.handle = func;
            return this;
        };
        Report.prototype.setTableShow = function (val) {
            this.opt.table.show = val;
            return this;
        };
        Report.prototype.setChartShow = function (val) {
            this.opt.chart.show = val;
            return this;
        };
        Report.prototype.setDefaultDims = function (val) {
            this.opt.container_dim = val;
            return this;
        };
        Report.prototype.setDefaultKpis = function (val) {
            this.opt.kpi = val;
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
            return this;
        };
        Report.prototype.initHandle = function () {
            var _this = this;
            if (this.handle != null) {
                return;
            }
            switch (this.opt.chart_type) {
                case 'ltv':
                    this.handle = function (data) {
                        if (data.data) {
                            _this.data = data.data;
                            _this.buildLtvTable();
                            _this.buildChart();
                        }
                    };
                    break;
                case 'retention':
                    this.handle = function (data) {
                        if (data.data) {
                            _this.data = data.data;
                            _this.buildRetentionTable();
                        }
                    };
                    break;
                default:
                    this.handle = function (data) {
                        if (data.data) {
                            _this.data = data.data;
                            _this.buildTable();
                            _this.updateChartHeader();
                            _this.buildChart();
                        }
                    };
                    break;
            }
        };
        Report.prototype.bingConfig = function (option) {
            if (option === void 0) { option = {}; }
            this.opt = jQuery.extend({}, this.opt, option);
            return this;
        };
        Report.prototype.run = function (callback) {
            if (this.opt.kpi instanceof Object) {
                this.initKpi();
            }
            if (this.opt.dim instanceof Object) {
                this.initDim();
            }
            var mydom = document.getElementById('data_charts');
            this.echart = echarts.init(mydom);
            window.onresize = this.echart.resize;
            this.afterInit();
            this.bingParams();
            this.initHandle();
            this.doAction();
            if (typeof callback === 'function') {
                callback(this);
            }
        };
        Report.prototype.chkKpi = function () {
            if (this.tableHeader.length > 0 &&
                this.tableHeader.indexOf(this.defaultKpi) == -1) {
                this.defaultKpi = this.tableHeader[0];
            }
        };
        Report.prototype.initKpi = function () {
            var conf = this.opt.conf;
            var mykpi = this.opt.my_kpi;
            var KpiDOM = $('#blk_kpi');
            // var KpiDOM = $('<div>').attr('id', 'blk_kpi')
            $.each(this.opt.kpi, function (i, item) {
                var dlDOM = $('<div>').addClass('kpi-group');
                var chkbox;
                dlDOM.empty().append($('<dt>').append(conf[i]));
                $.each(item, function (key, val) {
                    if (dlDOM.length > 0) {
                        chkbox = $('<input>').attr({
                            type: 'checkbox',
                            class: 'kpi_item',
                            value: val,
                            checked: val.in_array(mykpi)
                        });
                        if (conf.hasOwnProperty(val)) {
                            dlDOM.append($('<dd>').append($('<label>').append(chkbox, conf[val])));
                        }
                        else {
                            console.log('cannot find kpi conf:', val);
                        }
                    }
                });
                KpiDOM.append(dlDOM);
            });
            $(this.opt.blk).append(KpiDOM);
        };
        Report.prototype.sortKpi = function (header) {
            var sortedHeader = [];
            $.each(this.opt.conf, function (key, name) {
                if (key.in_array(header)) {
                    sortedHeader.push(key);
                }
            });
            return sortedHeader;
        };
        Report.prototype.initDim = function () {
            this.dimBar = $(this.opt.container_dim);
            var myblk = $('#blk');
            var myblkDefault = $('<ul>')
                .attr({ id: 'blk_default', class: 'blk_view' })
                .css({ width: '140px', 'margin-left': '5px', left: '60px' });
            myblk.append(myblkDefault);
            $('body').append(myblk);
            if (typeof this.opt.dim != 'undefined') {
                $.each(this.opt.box, function (key, item) {
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
                            .append("<ul class=\"check_box\" style=\"" + boxStyle + "\"></ul>");
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
                    var fields = this.opt.dim_fields;
                    if (fields) {
                        html_default = '';
                        for (var key in fields) {
                            var dimname = fields[key];
                            if (fields[key] == '-') {
                                html_default += '<hr>';
                            }
                            else {
                                html_default += "<li id=\"" + key + "\" class=\"add_dim\">" + dimname + "</li>";
                            }
                        }
                        myblkDefault.html(html_default).css({ top: this.defaultDim.height });
                    }
                    this.dimBar.append(myblkDefault);
                }
            }
        };
        Report.prototype.ajaxData = function (callback) {
            var that = this;
            if (this.opt.api.data != '') {
                for (var key in this.params) {
                    if (this.params[key] == '') {
                        delete this.params[key];
                    }
                }
                this.params['token'] = token;
                $.ajax({
                    type: 'POST',
                    url: this.opt.api.data,
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
                        else if (b == 'parsererror') {
                            alert('数据异常！');
                        }
                        else if (b == 'error') {
                            alert('请求数据失败！');
                        }
                        $('.mask').fadeOut(300, function () {
                            that.ready = 1;
                        });
                    }
                });
            }
        };
        Report.prototype.ajaxConf = function (filed, callback) {
            if (filed === void 0) { filed = null; }
            if (callback === void 0) { callback = function () { }; }
            if (this.opt.api.conf !== null) {
                $.ajax({
                    type: 'POST',
                    url: this.opt.api.conf,
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
            if (this.opt.api.data !== null) {
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
        Report.prototype.buildLtvChart = function (mydims, field) {
            var conf = this.opt.conf;
            var dimmap = this.opt.dim;
            var data = this.data;
            var legend = [];
            var legendData = [];
            var xdata = [];
            var series = [];
            var rdays = [];
            var items = {};
            for (var _i = 0, _a = data['days']; _i < _a.length; _i++) {
                var i = _a[_i];
                rdays.push('ltv' + i);
            }
            for (var key in data['data']) {
                if (!items.hasOwnProperty(key)) {
                    items[key] = {};
                }
                for (var _b = 0, _c = data['days']; _b < _c.length; _b++) {
                    var j = _c[_b];
                    items[key][j] = 0;
                }
            }
            var maxSize = 5;
            for (var key_1 in data['data']) {
                var my_legend = [];
                for (var rday in data['data'][key_1]['ltv']) {
                    items[key_1][rday] = parseFloat(data['data'][key_1]['ltv'][rday]);
                }
                for (var _d = 0, _e = data['dims']; _d < _e.length; _d++) {
                    var dim = _e[_d];
                    if (dimmap.hasOwnProperty(dim) &&
                        dimmap[dim].hasOwnProperty(data['data'][key_1][dim])) {
                        my_legend.push(dimmap[dim][data['data'][key_1][dim]]);
                    }
                    else {
                        my_legend.push(data['data'][key_1][dim]);
                    }
                }
                var title = my_legend.join('_');
                var mySize = title.length;
                if (mySize > maxSize) {
                    maxSize = mySize;
                }
                legendData.push({ name: title, sort: Math.max(items[key_1][rday]) });
                series.push({
                    name: title,
                    type: 'line',
                    data: $.map(items[key_1], function (value, index) {
                        return [value];
                    })
                });
            }
            legendData.sort(function (a, b) {
                return b.sort - a.sort;
            });
            var option = {
                title: {
                    text: data['title']
                },
                tooltip: {
                    trigger: 'axis'
                },
                legend: {
                    data: legendData,
                    type: 'scroll',
                    orient: 'vertical',
                    right: 12,
                    top: 50,
                    bottom: '3%',
                    textStyle: {
                        fontSize: 10
                    }
                },
                grid: {
                    left: '3%',
                    right: maxSize * 8 + 50,
                    bottom: '3%',
                    containLabel: true
                },
                toolbox: {
                    feature: {
                        saveAsImage: {}
                    }
                },
                xAxis: {
                    type: 'category',
                    boundaryGap: false,
                    data: rdays
                },
                yAxis: {
                    type: 'value'
                },
                series: series
            };
            this.echart.setOption(option, true);
        };
        Report.prototype.buildLineChart = function (mydims, field) {
            var conf = this.opt.conf;
            var dimmap = this.opt.dim;
            var data = this.data.table;
            var title = conf[field];
            //中间未排序
            var mydata = {};
            //排序补全
            var xdata = {};
            var legendData = [];
            var series = [];
            var daykey = [];
            var maxSize = 3;
            var sub_key = '';
            var _loop_1 = function (item) {
                key = '';
                sub_key = '';
                mydims.forEach(function (dim) {
                    if (!dim.in_array(['days', 'hours', 'months', 'weeks'])) {
                        if (dimmap.hasOwnProperty(dim) &&
                            dimmap[dim].hasOwnProperty(item[dim])) {
                            key += dimmap[dim][item[dim]];
                        }
                        else {
                            key += dim + ':' + item[dim];
                        }
                    }
                    else {
                        sub_key += ' ' + item[dim];
                    }
                });
                if (key == '' && conf.hasOwnProperty(field)) {
                    key = conf[field];
                }
                if (!mydata.hasOwnProperty(key)) {
                    mydata[key] = {};
                }
                if (sub_key.length > 0 && !sub_key.in_array(daykey)) {
                    daykey.push(sub_key);
                }
                mydata[key][sub_key] = item[field];
                if (key.length > maxSize) {
                    maxSize = key.length;
                }
            };
            var key;
            for (var _i = 0, data_1 = data; _i < data_1.length; _i++) {
                var item = data_1[_i];
                _loop_1(item);
            }
            daykey.sort();
            var _loop_2 = function (key_2) {
                var item = mydata[key_2];
                xdata[key_2] = {};
                maxval = 0;
                daykey.forEach(function (myday) {
                    if (item.hasOwnProperty(myday)) {
                        xdata[key_2][myday] = item[myday];
                        if (item[myday] > maxval) {
                            maxval = item[myday];
                        }
                    }
                    else {
                        xdata[key_2][myday] = 0;
                    }
                });
                legendData.push({ name: key_2, sort: maxval });
                if (maxval > 0) {
                    series.push({
                        name: key_2,
                        type: 'line',
                        data: $.map(xdata[key_2], function (value, index) {
                            return [value];
                        })
                    });
                }
            };
            var maxval;
            for (var key_2 in mydata) {
                _loop_2(key_2);
            }
            legendData.sort(function (a, b) {
                return b.sort - a.sort;
            });
            var legend = {};
            var grid = {};
            if (series.length > 5) {
                legend = {
                    data: legendData,
                    type: 'scroll',
                    orient: 'vertical',
                    right: 0,
                    top: 50,
                    bottom: '3%',
                    textStyle: {
                        fontSize: 10
                    }
                };
                grid = {
                    left: '3%',
                    right: maxSize * 10 + 40,
                    bottom: '3%',
                    containLabel: true
                };
            }
            else {
                legend = {
                    data: legendData,
                    top: 10
                };
                grid = {
                    left: '3%',
                    right: '4%',
                    bottom: '%',
                    containLabel: true
                };
            }
            var option = {
                title: {
                    text: title,
                    top: 10,
                    textStyle: {
                        fontSize: 16
                    }
                },
                tooltip: {
                    trigger: 'axis',
                    axisPointer: {
                        type: 'cross',
                        label: {
                            backgroundColor: '#6a7985'
                        }
                    }
                },
                legend: legend,
                grid: grid,
                xAxis: [
                    {
                        type: 'category',
                        boundaryGap: false,
                        data: daykey
                    }
                ],
                yAxis: [
                    {
                        type: 'value'
                    }
                ],
                series: series
            };
            this.echart.setOption(option, true);
        };
        Report.prototype.buildPieChart = function (mydim, field) {
            if (field === void 0) { field = ''; }
            var conf = this.opt.conf;
            var dimmap = this.opt.dim;
            var data = this.data.table;
            //中间未排序
            var mydata = {};
            //排序补全
            var xdata = {};
            var legend = [];
            var series = [];
            var daykey = [];
            var title = conf[mydim];
            for (var _i = 0, data_2 = data; _i < data_2.length; _i++) {
                var item = data_2[_i];
                var key = '';
                if (dimmap[mydim].hasOwnProperty(item[mydim])) {
                    key += dimmap[mydim][item[mydim]];
                }
                else {
                    key += mydim + ':' + item[mydim];
                }
                if (!mydata.hasOwnProperty(key)) {
                    mydata[key] = 0;
                }
                mydata[key] += item[field];
            }
            for (var key_3 in mydata) {
                legend.push(key_3);
                series.push({
                    name: key_3,
                    value: mydata[key_3]
                });
            }
            var option = {
                title: {
                    text: title,
                    x: 'center'
                },
                tooltip: {
                    trigger: 'item',
                    formatter: '{a} <br/>{b} : {c} ({d}%)'
                },
                legend: {
                    type: 'scroll',
                    orient: 'vertical',
                    right: 10,
                    top: 20,
                    bottom: 20,
                    data: legend
                },
                series: [
                    {
                        name: title,
                        type: 'pie',
                        radius: '55%',
                        center: ['40%', '50%'],
                        data: series,
                        itemStyle: {
                            emphasis: {
                                shadowBlur: 10,
                                shadowOffsetX: 0,
                                shadowColor: 'rgba(0, 0, 0, 0.5)'
                            }
                        }
                    }
                ]
            };
            this.echart.setOption(option, true);
        };
        Report.prototype.buildChart = function (field) {
            if (field === void 0) { field = this.defaultKpi; }
            if (!this.opt.chart.show) {
                return false;
            }
            var dims = this.getDims();
            var dayDim = 'days';
            var hourDim = 'hours';
            var monthDim = 'months';
            var weekDim = 'weeks';
            $('#chart_header')
                .find('.index')
                .each(function (i, key) {
                $(this).removeClass('sign-selected');
                if ($(this).attr('field') == field) {
                    $(this).addClass('sign-selected');
                }
            });
            if (this.opt.chart_type == 'ltv') {
                this.buildLtvChart(dims, 'ltv');
            }
            else if (dims.length > 0) {
                if (dayDim.in_array(dims) ||
                    monthDim.in_array(dims) ||
                    weekDim.in_array(dims) ||
                    hourDim.in_array(dims)) {
                    this.buildLineChart(dims, field);
                }
                else {
                    this.buildPieChart(dims[dims.length - 1], field);
                }
            }
        };
        Report.prototype.getTableHeader = function () {
            var tableHeader = [];
            var table = this.opt.table;
            var dims = this.getDims();
            var kpis = this.opt.my_kpi;
            tableHeader = dims.concat(kpis);
            return tableHeader;
        };
        Report.prototype.buildLtvTable = function () {
            if (!this.opt.table.show) {
                return false;
            }
            var val = '';
            var dimmap = this.opt.dim;
            var data = this.data.data;
            var cols = this.data.cols;
            var dims = this.data.dims;
            var conf = this.opt.conf;
            // var dims: any = this.opt.dim
            var divDom = $(this.containerTable);
            var tableDom = $('<table>');
            var tbodyDom = $('<tbody>');
            var theadDom = $('<thead>');
            tableDom.addClass('mui-data-table');
            var trDom = {};
            trDom = $('<tr>');
            for (var _i = 0, cols_1 = cols; _i < cols_1.length; _i++) {
                var col = cols_1[_i];
                trDom.append($('<th>')
                    .append(col['title'])
                    .attr('field', col['field']));
            }
            theadDom.append(trDom);
            if (this.data.hasOwnProperty('ex_kpis')) {
                dims = dims.concat(this.data.ex_kpis);
            }
            for (var item in data) {
                trDom = $('<tr>');
                for (var _a = 0, dims_1 = dims; _a < dims_1.length; _a++) {
                    var dim = dims_1[_a];
                    if (dimmap.hasOwnProperty(dim) &&
                        dimmap[dim].hasOwnProperty(data[item][dim])) {
                        val = dimmap[dim][data[item][dim]];
                    }
                    else {
                        val = data[item][dim];
                    }
                    trDom.append($('<td>').append(val));
                }
                for (var _b = 0, _c = this.data.days; _b < _c.length; _b++) {
                    var i = _c[_b];
                    if (data[item]['ltv'].hasOwnProperty(i)) {
                        trDom.append($('<td>').append(data[item]['ltv'][i]));
                    }
                    else {
                        trDom.append($('<td>').append('0'));
                    }
                }
                tbodyDom.append(trDom);
            }
            tableDom.prepend(theadDom).prepend(tbodyDom);
            divDom.empty().append(tableDom);
            divDom.css('max-height', $(document.body).height() - 80 + 'px');
            this.freezeHeader(this.containerTable);
        };
        Report.prototype.buildRetentionTable = function () {
            if (!this.opt.table.show) {
                return false;
            }
            var val = '';
            var dimmap = this.opt.dim;
            var data = this.data.data;
            var cols = this.data.cols;
            var dims = this.data.dims;
            var conf = this.opt.conf;
            // var dims: any = this.opt.dim
            var divDom = $(this.containerTable);
            var tableDom = $('<table>');
            var tbodyDom = $('<tbody>');
            var theadDom = $('<thead>');
            tableDom.addClass('mui-data-table');
            var trDom = {};
            var strHtml = '';
            trDom = $('<tr>');
            for (var _i = 0, cols_2 = cols; _i < cols_2.length; _i++) {
                var col = cols_2[_i];
                trDom.append($('<th>')
                    .append(col['title'])
                    .attr('field', col['field']));
            }
            theadDom.append(trDom);
            if (this.data.hasOwnProperty('ex_kpis')) {
                dims = dims.concat(this.data.ex_kpis);
            }
            for (var item in data) {
                trDom = $('<tr>');
                for (var _a = 0, dims_2 = dims; _a < dims_2.length; _a++) {
                    var dim = dims_2[_a];
                    if (dimmap.hasOwnProperty(dim) &&
                        dimmap[dim].hasOwnProperty(data[item][dim])) {
                        val = dimmap[dim][data[item][dim]];
                    }
                    else {
                        val = data[item][dim];
                    }
                    trDom.append($('<td>').append(val));
                }
                for (var i = 0; i <= this.data.days; i++) {
                    strHtml = $('<td>');
                    if (data[item]['retention'].hasOwnProperty(i)) {
                        if (data[item]['retention'][i] == 0 ||
                            data[item]['retention'][i] == '-') {
                            strHtml.append(data[item]['retention'][i]);
                        }
                        else {
                            strHtml
                                .append(data[item]['retention'][i] + '%')
                                .addClass('progress')
                                .prepend("<div style=\"width:" + data[item]['retention'][i] + "%\"></div>");
                        }
                    }
                    else {
                        strHtml.append('-');
                    }
                    trDom.append(strHtml);
                }
                tbodyDom.append(trDom);
            }
            tableDom.prepend(theadDom).prepend(tbodyDom);
            divDom.empty().append(tableDom);
            divDom.css('max-height', $(document.body).height() - 80 + 'px');
            this.freezeHeader(this.containerTable);
        };
        Report.prototype.buildTable = function () {
            if (!this.opt.table.show) {
                return false;
            }
            var val = '';
            var data = this.data;
            var conf = this.opt.conf;
            var dims = this.opt.dim;
            var divDom = $(this.containerTable);
            var tableDom = $('<table>');
            var tbodyDom = $('<tbody>');
            tableDom.addClass('mui-data-table');
            var tableHeader = this.getTableHeader();
            var trDom = {};
            for (var _i = 0, _a = data['table']; _i < _a.length; _i++) {
                var item = _a[_i];
                trDom = $('<tr>');
                for (var _b = 0, tableHeader_1 = tableHeader; _b < tableHeader_1.length; _b++) {
                    var name_1 = tableHeader_1[_b];
                    val = item[name_1];
                    if (dims.hasOwnProperty(name_1) && dims[name_1].hasOwnProperty(val)) {
                        val = dims[name_1][val];
                    }
                    trDom.append($('<td>').append(val));
                }
                tbodyDom.append(trDom);
            }
            tableDom.prepend(tbodyDom);
            var theadDom = $('<thead>');
            trDom = $('<tr>');
            for (var _c = 0, tableHeader_2 = tableHeader; _c < tableHeader_2.length; _c++) {
                var item = tableHeader_2[_c];
                if (conf.hasOwnProperty(item)) {
                    var headerName = conf[item];
                    trDom.append($('<th>')
                        .append(headerName)
                        .addClass('sortby')
                        .addClass(item)
                        .attr('field', item));
                    if (headerName == null) {
                        console.log(item, headerName, 'is null');
                    }
                }
            }
            theadDom.append(trDom);
            if (data['total'] instanceof Object) {
                trDom = $('<tr>');
                for (var _d = 0, tableHeader_3 = tableHeader; _d < tableHeader_3.length; _d++) {
                    var item = tableHeader_3[_d];
                    trDom.append($('<td>').append(data['total'][item]));
                }
                theadDom.append(trDom);
            }
            tableDom.prepend(theadDom);
            divDom.empty().append(tableDom);
            divDom.css('max-height', $(document.body).height() - 80 + 'px');
            this.freezeHeader(this.containerTable);
            if (this.opt.sortBy != '') {
                this.sortTable(this.opt.sortBy, this.opt.sortArrow);
            }
        };
        Report.prototype.freezeHeader = function (elem) {
            // console.log(elem)
            document.querySelector(elem).addEventListener('scroll', function (e) {
                var scrollTop = this.scrollTop;
                this.querySelector('thead').style.transform =
                    'translateY(' + scrollTop + 'px)';
            });
        };
        Report.prototype.sortTable = function (field, order) {
            if (order === void 0) { order = 0; }
            var data = this.data['table'];
            var dims = this.opt.dim;
            var newJson = $.extend(true, {}, this.data);
            var tbodyDom = $(this.containerTable).find('tbody');
            var trDom = {};
            var tableHeader = this.getTableHeader();
            var val = '';
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
            for (var _i = 0, data_3 = data; _i < data_3.length; _i++) {
                var item = data_3[_i];
                trDom = $('<tr>');
                for (var _a = 0, tableHeader_4 = tableHeader; _a < tableHeader_4.length; _a++) {
                    var name_2 = tableHeader_4[_a];
                    val = item[name_2];
                    if (dims.hasOwnProperty(name_2) && dims[name_2].hasOwnProperty(val)) {
                        val = dims[name_2][val];
                    }
                    trDom.append($('<td>').append(val));
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
            if (this.opt.my_dim) {
                var defVal = this.opt.my_dim;
                for (var key in defVal) {
                    var val = [];
                    this.addDim(key);
                    if (defVal[key].length > 0) {
                        var myblk = $('#blk_' + key);
                        myblk
                            .find('.check_box')
                            .find('.dim_item')
                            .each(function () {
                            var myval = $(this).val();
                            if (myval.in_array(defVal[key])) {
                                $(this).attr('checked', CHECK_TRUE);
                            }
                        });
                    }
                }
            }
        };
        Report.prototype.doAction = function () {
            var that = this;
            this.params.compare = this.compare;
            if (this.opt.dateRange) {
                this.params.sdate = $(this.opt.dateRange).attr('sdate');
                this.params.edate = $(this.opt.dateRange).attr('edate');
            }
            else if (this.params.sdate == '' || this.params.edate == '') {
                return false;
            }
            if (this.compare) {
                var index = dims.indexOf('hours');
                if (index == -1) {
                    this.addDim('hours');
                }
            }
            for (var _i = 0, _a = this.opt.fix_dim; _i < _a.length; _i++) {
                var fdim = _a[_i];
                // console.log(fdim)
                if (!this.opt.my_dim.hasOwnProperty(fdim)) {
                    this.opt.my_dim[fdim] = '';
                    this.addDim(fdim);
                }
            }
            var dims = [];
            for (var dim in this.opt.my_dim) {
                dims.push(dim);
                if (this.opt.my_dim[dim].length > 0) {
                    this.params[dim] = this.opt.my_dim[dim].join(',');
                }
                else if (this.params.hasOwnProperty(dim)) {
                    delete this.params[dim];
                }
            }
            this.params.dims = dims.join(',');
            this.params.kpis = this.opt.my_kpi.join(',');
            if (this.opt.my_kpi.length > 0) {
                this.defaultKpi = this.opt.my_kpi[0];
            }
            this.ajaxData(this.handle);
        };
        Report.prototype.updateKpi = function () {
            if (this.modifiy) {
                this.modifiy = 0;
                this.ajaxConf(this.opt.my_kpi);
                // this.doAction()
            }
        };
        Report.prototype.updateChartHeader = function () {
            if (this.opt.chart.showTabs) {
                var kpis = this.opt.my_kpi;
                var conf = this.opt.conf;
                var table = $('#chart_header');
                table.empty();
                $.each(kpis, function (i, key) {
                    var th = $('<th>')
                        .attr({ field: key, class: 'index', title: conf[key] })
                        .append(conf[key]);
                    table.append(th);
                });
            }
        };
        Report.prototype.updayDim = function (dimName, val, doit) {
            if (doit === void 0) { doit = false; }
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
            if (csvContent == '') {
                alert('no data');
                return;
            }
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
            link.setAttribute('download', 'spm' + year + month + day + '.csv');
            link.click();
        };
        Report.prototype.getDims = function () {
            var dims = [];
            for (var dim in this.opt.my_dim) {
                dims.push(dim);
            }
            return dims;
        };
        Report.prototype.getKpis = function () {
            var kpis = [];
            var mykpi = this.opt.kpi;
            for (var pet in mykpi) {
                for (var ta in mykpi[pet]) {
                    if (mykpi[pet][ta]) {
                        kpis.push(ta);
                    }
                }
            }
            return kpis;
        };
        Report.prototype.addDim = function (dimType, doit) {
            if (doit === void 0) { doit = false; }
            if ($('#dim_' + dimType).length > 0) {
                console.log($('#dim_' + dimType).data('type'));
            }
            else {
                var dimName = this.opt.conf[dimType];
                var html = "<dd class=\"sort\"><a class=\"dim\" id=\"dim_" + dimType + "\" data-type=\"" + dimType + "\">" + dimName + "</a><a class=\"del_dim\" data-type=\"" + dimType + "\">\u2295</a></dd>";
                if (this.defaultDim) {
                    this.defaultDim.before(html);
                }
                $('#dim').sortable({});
                if (!this.opt.my_dim.hasOwnProperty(dimType)) {
                    this.opt.my_dim[dimType] = [];
                }
                if (doit) {
                    this.doAction();
                }
            }
        };
        Report.prototype.delDim = function (dimType, doit) {
            if (doit === void 0) { doit = false; }
            $('#blk_' + dimType)
                .find('input:checked')
                .removeAttr('checked');
            if (this.opt.my_dim.hasOwnProperty(dimType)) {
                delete this.opt.my_dim[dimType];
            }
            if (this.params.hasOwnProperty(dimType)) {
                delete this.params[dimType];
            }
            if (doit) {
                this.doAction();
            }
        };
        Report.prototype.afterInit = function () {
            if (!this.opt.table.show) {
                return false;
            }
            var that = this;
            var opt = this.opt;
            var opt_dims = this.opt.dim;
            if (this.dimBar.length > 0) {
                var html_default = '';
                var childMap = {
                    acitvity_id: 'acitvity_gid',
                    channel_no: 'channel_gid'
                };
                //序列化
                $.each(opt_dims, function (key, my_dim) {
                    html_default = '';
                    $.each(my_dim, function (i, item) {
                        html_default += "<li data-name=\"" + item + "\"><label><input type=\"checkbox\" class=\"dim_item\" data-dim=\"" + key + "\" value=\"" + i + "\">" + item + "</label></li>";
                    });
                    $('#blk_' + key)
                        .find('.check_box')
                        .html(html_default);
                    if (key.in_array(['pos_key'])) {
                        html_default = '';
                        var parentName = '';
                        $.each(my_dim, function (parentKey, childs) {
                            if (childMap.hasOwnProperty(key)) {
                                parentName = opt_dims[childMap[key]][parentKey];
                            }
                            else {
                                parentName = parentKey;
                            }
                            html_default += "<ul class=\"parent_box parent_key_" + parentKey + "\" data-showchild=true >";
                            html_default += "<li><label><input type=\"checkbox\" class=\"li_checkbox\" value=\"" + parentKey + "\">" + parentName + "</label><a class=\"show_children\">\u25BC</a>";
                            html_default += '<ul class=" childen_box">';
                            $.each(childs, function (childKey, childName) {
                                html_default += "<li data-name=\"" + childName + "\">\n                <label><input type=\"checkbox\"  class=\"" + key + " li_checkbox\" value=\"" + childKey + "\">" + childName + "</label>\n                </li>";
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
                                .find('.search')
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
                $('ul.childen_box').click(function (event) {
                    var ithis = $(this);
                    that.addDim(ithis.attr('id'));
                    $('#blk_default').hide();
                });
                // 添加维度
                $('.add_dim').click(function (event) {
                    var type = $(this).attr('id');
                    that.addDim(type);
                    $('#blk_default').hide();
                });
                // 删除维度
                $('.del_dim').live('click', function (e) {
                    var type = $(this).data('type');
                    that.delDim(type);
                    $(this)
                        .parent()
                        .remove();
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
                            viewDom.prepend('<div class="search_box"><input class="search" ><img width="10" height="10" class="clear" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAoAAAAKCAYAAACNMs+9AAAAQklEQVR42mMoLS1NAOL3QGzAgAZAYlC5BAYo4z+6YiRFYDkMASjfAKsBWCSw2oJN8X9c7iZOIVFWE+0ZUoKHqAAHAN50nAWGasN5AAAAAElFTkSuQmCC"></div>');
                        }
                        else if (item == 'select_box') {
                            var selectBox = $('<div class="select_box"><a class="allselect">全选</a><a class="noselect">全不选</a></div>');
                            viewDom.prepend(selectBox);
                        }
                    });
                }
            });
            $('ul.check_box').delegate('.dim_item', 'click', function (e) {
                var mydim = $(this).data('dim');
                var myval = [];
                var mylist = $(this).parents('ul.check_box');
                mylist.find('input.dim_item:checked:visible').each(function () {
                    myval.push($(this).val());
                });
                opt.my_dim[mydim] = myval;
            });
            $('#blk_kpi').delegate('.kpi_item', 'click', function (e) {
                var myval = [];
                $('#blk_kpi')
                    .find('input.kpi_item:checked')
                    .each(function () {
                    myval.push($(this).val());
                });
                opt.my_kpi = myval;
                that.modifiy = 1;
            });
            $('.blk_view')
                .delegate('.clear', 'click', function (e) {
                $(this)
                    .siblings('input')
                    .val('')
                    .trigger('keyup')
                    .focus();
            })
                .delegate('.search', 'keyup', function (e) {
                var keyword = $(this)
                    .val()
                    .toString()
                    .toLowerCase();
                var myblk = $(this).parents('.blk_view');
                var mylist = myblk.find('.check_box');
                var mydim = myblk.attr('type');
                var myval = [];
                if (mylist.children('li').length > 0) {
                    mylist.children('li').each(function () {
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
                    mylist.children('ul.parent_box').each(function () {
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
                mylist.find('input.dim_item:checked:visible').each(function () {
                    myval.push($(this).val());
                });
                opt.my_dim[mydim] = myval;
            })
                .delegate('.allselect', 'click', function (e) {
                var myblk = $(this).parents('.blk_view');
                var mylist = myblk.find('.check_box');
                var mydim = myblk.attr('type');
                var myval = [];
                mylist.find('input:checkbox:visible').each(function () {
                    $(this).attr('checked', CHECK_TRUE);
                    myval.push($(this).val());
                });
                opt.my_dim[mydim] = myval;
            })
                .delegate('.noselect', 'click', function (e) {
                var myblk = $(this).parents('.blk_view');
                var mylist = myblk.find('.check_box');
                var mydim = myblk.attr('type');
                var myval = [];
                mylist.find('input:checkbox').each(function () {
                    $(this).attr('checked', CHECK_FALSE);
                });
                opt.my_dim[mydim] = myval;
            });
            $(this.containerChartHeader).delegate('.index', 'click', function (e) {
                that.defaultKpi = $(this).attr('field');
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
            $('.kpi-group').delegate('dt', 'click', function (e) {
                var val = !$(this).data('val');
                var myval = [];
                $(this)
                    .data('val', val)
                    .siblings('dd')
                    .find(':checkbox')
                    .each(function () {
                    $(this).attr({ checked: val });
                });
                myval.push($(this).val());
                var myval = [];
                $('#blk_kpi')
                    .find('input.kpi_item:checked')
                    .each(function () {
                    myval.push($(this).val());
                });
                opt.my_kpi = myval;
                that.modifiy = 1;
            });
            // 点击空白处隐藏
            $(document).bind('mousedown', function (e) {
                var etarget = e.target || e.srcElement;
                $('.blk_view:visible').each(function (index, val) {
                    var target = etarget;
                    while (target != document && target != this) {
                        target = target.parentNode;
                    }
                    if (target == document) {
                        $(this).hide();
                        if ($(this).attr('id') == 'blk_kpi') {
                            that.updateKpi();
                        }
                    }
                });
            });
        };
        return Report;
    }());
    Spm.Report = Report;
})(Spm || (Spm = {}));
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
function getCookie(cname) {
    var name = cname + '=';
    var ca = document.cookie.split(';');
    for (var i = 0; i < ca.length; i++) {
        var c = ca[i].trim();
        if (c.indexOf(name) == 0)
            return c.substring(name.length, c.length);
    }
    return '';
}
