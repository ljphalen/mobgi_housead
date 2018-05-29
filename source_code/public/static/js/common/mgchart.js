///<reference path="jquery.d.ts" />
///<reference path="diy.d.ts" />
"use strict";
var CHECK_FALSE = false;
var CHECK_TRUE = true;
// 磨基报表
var Mg;
(function (Mg) {
    // 报表
    class Report2 {
        constructor() {
            this.compare = false;
            this.myDim = []; //查询维度
            this.dimBar = null;
            this.defaultDim = null;
            this.defaultPki = "view";
            this.dims = {}; //维度定义
            this.api = null; //
            this.default_dim_fields = null; //默认查询维度
            this.data = null; //查询数据结果
            this.containerChart = "#data_charts"; //图表选择器ID
            this.containerChartHeader = "#chart_header"; //图表头选择器ID
            this.containerTable = "#mg_talbe"; //图表选择器ID
            this.tableHeader = []; //表格头定义
            this.options = {
                api: {},
                conf: {},
                kpi: {},
                dim: {
                    default_dim_dom: "#dim",
                    default_dim_fields: {},
                    default_dim_value: ['app_key'],
                    dims: {},
                },
                chart: {
                    show: 1,
                    showTabs: 1,
                    pieItem: ["app_key", "platform", "ad_type", "channel_gid", "ads_id", "pos_key", "country", "area", "ssp_id", "app_version", "sdk_version"],
                },
                table: {
                    show: 1,
                    header: "",
                },
                dateRange: ""
            };
            this.params = {
                sdate: "",
                edate: "",
                dims: {},
                kpis: {}
            };
        }
        setDateRange(sdate, edate) {
            this.params.sdate = sdate;
            this.params.edate = edate;
            return this;
        }
        setDateRangePlugin(pluginDom) {
            this.options.dateRange = "#date_range";
            return this;
        }
        setCompare(val) {
            this.compare = val;
            if (this.compare) {
                this.addDim("dates");
                this.addDim("hours");
            }
            return this;
        }
        init(option = {}) {
            var default_option = this.options;
            this.options = jQuery.extend({}, default_option, option);
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
            }
            this.afterInit();
            this.bingParams();
            this.doAction();
        }
        chkKpi() {
            // kpi empty
            if (this.tableHeader.length > 0 && this.tableHeader.indexOf(this.defaultPki) == -1) {
                this.defaultPki = this.tableHeader[0];
            }
        }
        initKpi(kpi) {
            var conf = this.options.conf;
            var tableHeader = [];
            $.each(kpi, function (i, item) {
                var dlDOM = $("#kpi_" + i);
                var chkbox;
                dlDOM.empty().append($("<dt>").append(dlDOM.attr('title')));
                $.each(item, function (key, val) {
                    if (dlDOM.length > 0) {
                        chkbox = $('<input>').attr({ "type": "checkbox", "class": "kpi", "value": key, "checked": val > 0 });
                        dlDOM.append($('<dd>').append($('<label>').append(chkbox, conf[key]['name'])));
                    }
                    if (val > 0) {
                        tableHeader.push(key);
                    }
                });
            });
            this.tableHeader = this.sortKpi(tableHeader);
        }
        sortKpi(header) {
            var sortedHeader = [];
            $.each(this.options.conf, function (key, name) {
                if (key.in_array(header)) {
                    sortedHeader.push(key);
                }
            });
            return sortedHeader;
        }
        initDim(dim_options) {
            var blk = {
                originality_id: { mod: "select_box,search_box,bottom_box", style: "max-height:200px;", },
                originality_type: { mod: "select_box,search_box,bottom_box", style: "max-height:200px;", },
                app_key: { mod: "select_box,search_box,bottom_box", style: "max-height:200px;", },
                pos_key: { mod: "select_box,search_box,bottom_box", style: "max-height:200px;", },
                account_id: { mod: "select_box,search_box,bottom_box", style: "max-height:200px;", },
                unit_id: { mod: "select_box,search_box,bottom_box", style: "max-height:200px;", },
                ad_id: { mod: "select_box,search_box,bottom_box", style: "max-height:200px;", },
                ad_type: { mod: "select_box,search_box,bottom_box", style: "max-height:200px;", },
                ad_sub_type: { mod: "select_box,search_box,bottom_box", style: "max-height:200px;", },
                platform: { mod: "select_box,search_box,bottom_box", style: "max-height:200px;", }
            };
            var myblk;
            if ($("#blk")) {
                myblk = $("#blk");
            }
            else {
                myblk = $("<div>").attr('id', 'blk');
            }
            var myblkDefault = $("<ul>").attr({ id: "blk_default", class: "blk_view" }).css({ width: "140px", "margin-left": "5px", left: "60px" });
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
                $.each(blk, function (key, item) {
                    var mydom = $('<div>').attr({ "id": "blk_" + key, "type": key }).addClass('blk_view');
                    if (item.mod) {
                        mydom.attr({ "mod": item.mod }).append('<ul class="check_box" style="' + item.style + '"></ul>');
                    }
                    myblk.append(mydom);
                });
                if (this.dimBar.length > 0) {
                    this.defaultDim = $('<dd class="dim_default_btn"><a>+添加 ▼</a></dd>');
                    this.dimBar.html('<dt class="dim-label" > 维度：</dt>').append(this.defaultDim).append('<dd class="dim_execute_btn"><a>执行</a></dd>');
                    var html_default = "";
                    //默认添加按钮
                    if (dim_options.default_dim_fields) {
                        html_default = "";
                        for (var key in dim_options.default_dim_fields) {
                            html_default += (dim_options.default_dim_fields[key] == "-") ? '<hr>' : '<li id="' + key + '" class="add_dim">' + dim_options.default_dim_fields[key] + '</li>';
                        }
                        myblkDefault.html(html_default).css({ top: this.defaultDim.height });
                    }
                    this.dimBar.append(myblkDefault);
                }
            }
        }
        ajaxData(callback) {
            if (this.api.date !== null) {
                for (var key in this.params) {
                    if (this.params[key] == "") {
                        delete this.params[key];
                    }
                }
                $.ajax({
                    type: "GET",
                    url: this.api.data,
                    data: this.params,
                    dataType: "json",
                    success: function (response) {
                        callback(response);
                    },
                    beforeSend: function () {
                        $("body").mask("正在加载");
                    },
                    complete: function (a, b) {
                        if (a.status == 401) {
                            layer.alert("由于您很长时间未在线使用，请您重新登录！", 8);
                        }
                        else if (b == "error") {
                            layer.alert("请求数据失败！", 8);
                        }
                        $("body").unmask();
                    }
                });
            }
        }
        ajaxConf(filed = null, callback = function () { }) {
            if (this.api.conf !== null) {
                $.ajax({
                    type: "POST",
                    url: this.api.conf,
                    data: { "kpis": filed.join(','), token: token },
                    dataType: "json",
                    success: function (response) {
                        callback(response);
                    }
                });
            }
        }
        getConfStr(callback) {
            var confarr = [];
            if (this.api.date !== null) {
                var dims = this.getDim();
                this.params.dims = dims.join(',');
                for (var key in this.params) {
                    if (this.params[key] == "") {
                        delete this.params[key];
                    }
                }
                $.each(this.params, function (key, name) {
                    confarr.push(key + "=" + name);
                });
            }
            return confarr.join("&");
        }
        buildLineChart(dims, field) {
            var dateDim = "date";
            var hourDim = "hour";
            var conf = this.options.conf;
            var chart = {};
            var chartName = conf[field]["name"];
            chart.data = {};
            chart.serialdata = [];
            chart.categories = [];
            chart.tickInterval = 1;
            var items = this.data.table;
            var serialname = "";
            var dimName = [];
            var xxDims = [];
            var xxDimName = [];
            var xxName = "";
            if (items.length == 0) {
                return;
            }
            var count = items.length;
            if (dateDim.in_array(dims)) {
                xxDims.push(dateDim);
                dims.splice($.inArray(dateDim, dims), 1);
                chart.tickInterval = Math.floor(count / 12) + 1;
            }
            if (hourDim.in_array(dims)) {
                xxDims.push(hourDim);
                dims.splice($.inArray(hourDim, dims), 1);
                if (count > 168) {
                    chart.tickInterval = 24 * (Math.floor(count / 24 / 12) + 1);
                }
                else if (count <= 24) {
                    chart.tickInterval = 1;
                }
                else {
                    chart.tickInterval = 12 * (Math.floor(count / 12 / 12) + 1);
                }
            }
            for (var item of items) {
                serialname = "";
                dimName = [];
                xxDimName = [];
                xxName = "";
                for (var xxDim of xxDims) {
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
                    serialname = conf[field]["name"];
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
            var params = {
                title: { text: "", x: -20 },
                xAxis: {
                    tickInterval: chart.tickInterval,
                    categories: chart.categories,
                },
                yAxis: {
                    title: { text: "" },
                    min: 0,
                    plotLines: [{
                            value: 0,
                            width: 1,
                            color: '#808080'
                        }],
                    showFirstLabel: true
                },
                legend: {},
                tooltip: {
                    crosshairs: true,
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
        }
        buildPieChart(dim, field = "third_views") {
            var conf = this.options.conf;
            var chart = {};
            chart.name = conf[field]["name"];
            chart.data = [];
            var data = {};
            $.each(this.data, function (i, item) {
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
            chart.data.sort(function (x, y) { return y[1] - x[1]; });
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
                            format: '<b>{point.name}</b>: {point.percentage:.1f} %',
                        },
                        showInLegend: true
                    }
                },
                series: [{
                        type: 'pie',
                        name: chart.name,
                        data: chart.data
                    }]
            });
        }
        buildChart(field = this.defaultPki) {
            if (this.options.chart.show == 0) {
                return false;
            }
            var dims = this.getDim();
            var dateDim = "date";
            var hourDim = "hour";
            $("#chart_header").find(".index").each(function (i, key) {
                $(this).removeClass("sign-selected");
                if ($(this).attr("field") == field) {
                    $(this).addClass("sign-selected");
                }
            });
            if (dims.length > 0) {
                if (dateDim.in_array(dims) || hourDim.in_array(dims)) {
                    this.buildLineChart(dims, field);
                }
                else if (dims[dims.length - 1].in_array(this.options.chart.pieItem)) {
                    this.buildPieChart(dims[dims.length - 1], field);
                }
            }
            $(this.containerChart).children("div").children("svg").children("text:last-child").hide();
        }
        getTableHeader() {
            var tableHeader = this.tableHeader;
            var table_options = this.options.table;
            var dims = this.getDim();
            tableHeader = dims.concat(tableHeader);
            if (table_options.hasOwnProperty("header")) {
                tableHeader = tableHeader.concat(table_options.header.split(","));
            }
            return tableHeader;
        }
        buildTable(data = null) {
            if (this.options.table.show == 0) {
                return false;
            }
            var val = "";
            var conf = this.options.conf;
            var divDom = $(this.containerTable);
            var tableDom = $('<table>');
            var tbodyDom = $('<tbody>');
            tableDom.addClass("mui-data-table");
            var tableHeader = this.getTableHeader();
            var trDom = {};
            $.each(data['table'], function (i, item) {
                trDom = $('<tr>');
                $.each(tableHeader, function (j, name) {
                    val = item[name];
                    trDom.append($('<td>').append(val));
                });
                if (tableHeader[0] == "date") {
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
                trDom.append($('<th>').append(headerName).addClass("sortby").attr("field", key));
                if (headerName == null) {
                    console.log(key, headerName, "is null");
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
        }
        sortTable(field, order = 0) {
            var data = this.data["table"];
            var newJson = $.extend(true, {}, this.data);
            var tbodyDom = $(this.containerTable).find("tbody");
            var trDom = {};
            var tableHeader = this.getTableHeader();
            var val;
            tbodyDom.empty();
            data.sort(function (a, b) {
                if (a.hasOwnProperty(field) && b.hasOwnProperty(field)) {
                    return isNaN(a[field]) ? a[field].replace(/<[^>]+>/g, "").localeCompare(b[field].replace(/<[^>]+>/g, "")) : a[field] - b[field];
                }
                else {
                    return 0;
                }
            });
            for (var item of data) {
                trDom = $('<tr>');
                for (var key of tableHeader) {
                    trDom.append($('<td>').append(item[key]));
                }
                if (order) {
                    tbodyDom.prepend(trDom);
                }
                else {
                    tbodyDom.append(trDom);
                }
            }
        }
        bingParams() {
            if (this.options.dim.default_dim_value) {
                var defVal = this.dims = this.options.dim.default_dim_value;
                for (var key in defVal) {
                    var val = [];
                    this.addDim(key, defVal[key], false);
                    if (key == "app_key") {
                        var posblk = $("#blk_pos_key").find(".check_box");
                        if (defVal[key].length > 0) {
                            posblk.find(".parent_box").hide();
                            $.each(defVal[key], function (i, appkey) {
                                posblk.find(".parent_key_" + appkey).show();
                            });
                        }
                        else {
                            posblk.find(".parent_box").show();
                        }
                    }
                    if (defVal[key].length > 0) {
                        var myblk = $("#blk_" + key);
                        myblk.find(".check_box").find("input." + key).each(function () {
                            var myval = $(this).val();
                            if (myval.in_array(defVal[key])) {
                                $(this).attr('checked', CHECK_TRUE);
                                val.push(myval);
                            }
                        });
                    }
                }
            }
        }
        chgPlatform(sys = []) {
            if (sys.length > 0) {
                this.addDim('platform', sys.join(','), true);
            }
            else {
                this.removeDim('platform', true);
            }
        }
        doAction() {
            var that = this;
            this.params.dims = {};
            if (this.options.dateRange) {
                this.params.sdate = $(this.options.dateRange).attr("sdate");
                this.params.edate = $(this.options.dateRange).attr("edate");
            }
            else if (this.params.sdate == "" || this.params.edate == "") {
                return false;
            }
            this.chkKpi();
            var dims = this.getDim(); //维度
            this.params.compare = this.compare;
            if (this.compare) {
                var index = dims.indexOf("hours");
                if (index == -1) {
                    this.addDim("hours");
                    dims.push("hours");
                }
                index = dims.indexOf("dates");
                if (index == -1) {
                    this.addDim("dates");
                    dims.push("dates");
                }
            }
            for (var key in this.dims) {
                if (typeof this.dims[key] === "string") {
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
        }
        updateKpi(dimName, val) {
            if (val.length > 10) {
                alert('自定义指标最多只能选10个');
            }
            else {
                this.params[dimName] = val.join(',');
                this.tableHeader = this.sortKpi(val);
                this.ajaxConf(this.tableHeader);
                this.doAction();
            }
        }
        updateChartHeader() {
            if (this.options.chart.showTabs) {
                var tableHeader = this.tableHeader;
                var conf = this.options.conf;
                var table = $("#chart_header");
                table.empty();
                $.each(tableHeader, function (i, key) {
                    var th = $('<th>').attr({ "field": key, "class": "index", "title": conf[key]["title"] }).append(conf[key]["name"]);
                    table.append(th);
                });
            }
        }
        updateDim(dimName, val, doit = false) {
            this.dims[dimName] = val.join(',');
            if (doit) {
                this.doAction();
            }
        }
        getDim() {
            var dims = [];
            $(".dim").each(function () {
                dims.push($(this).data('type'));
            });
            if (dims.length == 0) {
                for (var key in this.dims) {
                    dims.push(key);
                }
            }
            return dims;
        }
        getKpi() {
            var kpi = [];
            var mykpi = this.options.kpi;
            for (var pet in mykpi) {
                for (var ta in mykpi[pet]) {
                    if (mykpi[pet][ta]) {
                        kpi.push(ta);
                    }
                }
            }
            return kpi;
        }
        addDim(dimType, dimValue = "", doit = false) {
            if ($('#dim_' + dimType).length > 0) {
                console.log($('#dim_' + dimType).data('type'));
            }
            else {
                this.dims[dimType] = dimValue;
                var dimName = this.default_dim_fields[dimType];
                var html = '<dd><a class="dim" id="dim_' + dimType + '" data-type="' + dimType + '">' + dimName + '</a><a class="dim_remove" data-type="' + dimType + '">X' + '</a></dd>';
                this.defaultDim.before(html);
                var that = this;
                $("#dim").sortable({
                    stop: function () {
                        that.doAction();
                    }
                });
                if (doit) {
                    this.doAction();
                }
            }
        }
        removeDim(dimType, doit = false) {
            // $("#blk_" + dimType).find("input:checked").prop("checked", false);
            $("#blk_" + dimType).find("input:checked").removeAttr('checked');
            if (this.dims.hasOwnProperty(dimType)) {
                delete this.dims[dimType];
            }
            if (this.params.hasOwnProperty(dimType)) {
                delete this.params[dimType];
            }
            if (doit) {
                this.doAction();
            }
        }
        afterInit() {
            if (this.options.table.show == 0) {
                return false;
            }
            var that = this;
            var options_dims = this.options.dim.dims;
            if (this.dimBar.length > 0) {
                var html_default = "";
                //序列化
                $.each(options_dims, function (key, mydim) {
                    if (key.in_array(['date', 'hour'])) {
                        html_default = '';
                        $.each(mydim, function (i, item) {
                            html_default += '<li><a class="date useBtn" type="' + i + '">' + item + '</a></li>';
                        });
                        $("#blk_" + key).find('.check_box').html(html_default);
                    }
                    else if (key.in_array(['app_key', 'originality_id', 'originality_type', 'platform', 'ad_id', 'ad_type', 'account_id', 'unit_id'])) {
                        html_default = "";
                        $.each(mydim, function (i, item) {
                            html_default += '<li data-name="' + item + '"><label><input type="checkbox" class="' + key + '" value="' + i + '">' + item + '</label></li>';
                        });
                        $("#blk_" + key).find('.check_box').html(html_default);
                    }
                    else if (key.in_array(['country', 'pos_key', 'ad_sub_type'])) {
                        html_default = "";
                        var parentName = "";
                        $.each(mydim, function (parentKey, childs) {
                            if (key == 'pos_key') {
                                parentName = options_dims['app_key'][parentKey];
                            }
                            else if (key == 'ad_sub_type') {
                                parentName = options_dims['ad_type'][parentKey];
                            }
                            else {
                                parentName = parentKey;
                            }
                            html_default += '<ul class="parent_box parent_key_' + parentKey + '" data-showchild=true >';
                            html_default += '<li><label><input type="checkbox" class="li_checkbox" value="' + parentKey + '">' + parentName + '</label><a class="show_children">▼</a>';
                            html_default += '<ul class=" childen_box">';
                            $.each(childs, function (childKey, childName) {
                                html_default += '<li  data-name="' + childName + '"><label><input type="checkbox"  class="' + key + ' li_checkbox"  value="' + childKey + '">' + childName + '</label></li>';
                            });
                            html_default += "</ul></li></ul>";
                        });
                        $("#blk_" + key).find('.check_box').html(html_default);
                    }
                });
                //二级操作
                $(".show_children").click(function () {
                    $(this).next("ul").toggle();
                    if ($(this).next("ul").css("display") == 'none') {
                        $(this).html("▲");
                    }
                    else {
                        $(this).html("▼");
                    }
                });
                $(".li_checkbox").click(function () {
                    if ($(this).parents("ul").hasClass("childen_box")) {
                        if ($(this).attr("checked")) {
                            $(this).parents("ul.childen_box").siblings("label").children(".li_checkbox").attr('checked', CHECK_TRUE);
                        }
                        else {
                            if ($(this).parents(".childen_box").find(".li_checkbox:checked").length == 0) {
                                $(this).parents(".childen_box").siblings("label").children(".li_checkbox").attr('checked', CHECK_FALSE);
                            }
                        }
                    }
                    else {
                        if ($(this).attr("checked")) {
                            var myUlParents = $(this).parents("ul.parent_box");
                            var can_show = myUlParents.data("showchild");
                            var mytrigger = myUlParents.data("trigger");
                            var has_search = $(this).parents('.blk_view').find(".search_input").val() != "";
                            $(this).parent().siblings("ul.childen_box").find(".li_checkbox").each(function () {
                                var is_visible = $(this).is(":visible");
                                if (has_search) {
                                    $(this).attr("checked", is_visible);
                                }
                                else {
                                    $(this).attr("checked", is_visible || can_show);
                                    if (can_show) {
                                        $(mytrigger).trigger("change");
                                    }
                                }
                            });
                        }
                        else {
                            $(this).parent().siblings("ul.childen_box").find(".li_checkbox").attr('checked', CHECK_FALSE);
                        }
                    }
                });
                // 添加维度
                var that = this;
                $(".add_dim").click(function (event) {
                    var ithis = $(this);
                    that.addDim(ithis.attr('id'));
                    $("#blk_default").hide();
                });
                // 删除维度
                $(".dim_remove").live("click", function (e) {
                    $(this).parent().remove();
                    that.removeDim($(this).data('type'));
                    return false;
                });
                // 执行动作
                $(".dim_execute_btn").live("click", function (e) {
                    that.doAction();
                    return false;
                });
                //默认添加维度
                this.defaultDim.click(function () {
                    $("#blk_default").show().css({ "left": $(this).position().left });
                });
            }
            $("#kpi").click(function (e) {
                var ddom = $(this);
                if ($(document).width() > $("#blk_kpi").width() + $(this).offset().left) {
                    $("#blk_kpi").css({ "left": $(this).position().left, "top": ddom.offset().top + ddom.height() + 5 }).show();
                }
                else {
                    var offset = $(this).width() - $("#blk_kpi").width();
                    $("#blk_kpi").css({ "left": $(this).position().left + offset, "top": ddom.offset().top + ddom.height() + 5 }).show();
                }
            });
            $("#dim").delegate(".dim", "click", function (e) {
                var dimType = $(this).data('type');
                var ddom = $(this).parent();
                $("#blk_" + dimType).css({ "left": ddom.offset().left, "top": ddom.offset().top + ddom.height() + 5 }).show();
            });
            $(".blk_view").each(function () {
                var viewDom = $(this);
                if (typeof viewDom.attr('mod') != 'undefined') {
                    $.each(viewDom.attr('mod').split(","), function (i, item) {
                        if (item == "search_box") {
                            viewDom.prepend('<div class="search_box"><input class="search_input" maxlength="10"><img width="10" height="10" class="cls_input" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAoAAAAKCAYAAACNMs+9AAAAQklEQVR42mMoLS1NAOL3QGzAgAZAYlC5BAYo4z+6YiRFYDkMASjfAKsBWCSw2oJN8X9c7iZOIVFWE+0ZUoKHqAAHAN50nAWGasN5AAAAAElFTkSuQmCC"></div>');
                        }
                        else if (item == "select_box") {
                            var selectBox = $(('<div class="select_box"><a class="allselect">全选</a><a class="noselect">全不选</a></div>'));
                            viewDom.prepend(selectBox);
                        }
                        else if (item == "bottom_box") {
                            viewDom.append('<div class="bottom_box"><a class="btn useBtn">应用</a><a class="btn closeBtn">取消</a></div>');
                        }
                    });
                }
            });
            $(".blk_view").delegate(".cls_input", "click", function (e) {
                $(this).siblings('input').val('').trigger('keyup').focus();
            }).delegate(".search_input", "keyup", function (e) {
                var keyword = $(this).val().toLowerCase();
                var check_boxDom = $(this).parents('.blk_view').find('.check_box');
                if (check_boxDom.children('li').length > 0) {
                    check_boxDom.children('li').each(function () {
                        var name = $(this).data('name');
                        name = name.toLowerCase();
                        if (name.match(keyword) == null) {
                            $(this).hide();
                        }
                        else {
                            $(this).show();
                        }
                    });
                }
                else {
                    check_boxDom.children("ul.parent_box").each(function () {
                        var showchild = $(this).data("showchild");
                        $(this).find('.childen_box').children('li').each(function () {
                            var name = $(this).data('name');
                            name = name.toLowerCase();
                            if (showchild && name.match(keyword) == null) {
                                $(this).hide();
                            }
                            else {
                                $(this).show();
                            }
                        });
                    });
                }
            }).delegate(".allselect", "click", function (e) {
                var myblk = $(this).parents('.blk_view').find('.check_box');
                myblk.find("input:checkbox").each(function () {
                    var myUlParents = $(this).parents("ul.parent_box");
                    var can_show = myUlParents.data("showchild");
                    var mytrigger = myUlParents.data("trigger");
                    var has_search = $(this).parents('.blk_view').find(".search_input").val() != "";
                    var is_visible = $(this).is(":visible");
                    if (has_search) {
                        $(this).attr("checked", is_visible);
                    }
                    else {
                        $(this).attr("checked", is_visible || can_show);
                        if (can_show) {
                            $(mytrigger).trigger("change");
                        }
                    }
                });
            }).delegate(".noselect", "click", function (e) {
                $(this).parents('.blk_view').find('.check_box').find("input:checkbox").each(function () {
                    $(this).attr("checked", CHECK_FALSE);
                });
            }).delegate(".closeBtn", "click", function (e) {
                $(this).parents('.blk_view').hide();
                return false;
            }).delegate(".useBtn", "click", function (e) {
                var myblk = $(this).parents('.blk_view');
                var mytype = myblk.attr("type");
                var val = [];
                myblk.find(".check_box").find("input." + mytype + ":checked:visible").each(function () {
                    val.push($(this).val());
                });
                if (mytype == "app_key" || mytype == "ad_type") {
                    var subType = mytype == "app_key" ? "pos_key" : "ad_sub_type";
                    var posblk = $("#blk_" + subType).find(".check_box");
                    posblk.find(".parent_box").hide();
                    $.each(val, function (i, appkey) {
                        posblk.find(".parent_key_" + appkey).show();
                    });
                }
                if (mytype == "kpi") {
                    that.updateKpi("kpi", val);
                }
                else if (mytype != "") {
                    that.updateDim(mytype, val);
                }
                $(this).parents('.blk_view').hide();
                return false;
            });
            $(this.containerChartHeader).delegate(".index", "click", function (e) {
                that.defaultPki = $(this).attr('field');
                that.buildChart($(this).attr('field'));
            });
            $(this.containerTable).delegate(".sortby", "click", function (e) {
                var ithis = $(this);
                ithis.siblings("th").removeClass("desc").removeClass("asc");
                if (ithis.hasClass("desc")) {
                    ithis.addClass("asc").removeClass("desc");
                    that.sortTable(ithis.attr('field'), 0);
                }
                else {
                    ithis.addClass("desc").removeClass("asc");
                    that.sortTable(ithis.attr('field'), 1);
                }
            });
            // 点击空白处隐藏
            $(document).bind("mousedown", function (e) {
                var etarget = e.target || e.srcElement;
                $(".blk_view").each(function (index, val) {
                    var target = etarget;
                    while (target != document && target != this) {
                        target = target.parentNode;
                    }
                    if (target == document) {
                        $(this).hide();
                    }
                });
            });
        }
    }
    Mg.Report2 = Report2;
})(Mg || (Mg = {}));
String.prototype.in_array = function (arr) {
    var isArr = Object.prototype.toString.call(arr) === "[object Array]";
    if (isArr) {
        for (var index = 0, k = arr.length; index < k; index++) {
            if (this == arr[index]) {
                return true;
            }
        }
    }
    return false;
};
