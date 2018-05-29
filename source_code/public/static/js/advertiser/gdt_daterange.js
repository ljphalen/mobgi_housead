$(function(){
    window.expTitle=document.title
    window._dateRangeCallBack = function(i) {
        if(typeof(i)=="undefined"){
            return
        }
        var a = timeArrToBitStr(i),
            n = [];
        n = timeArrToReadable(i);
        $.each(n, function(e, t) {
            n[e] = "<li>" + t + "</li>"
        });
        var timeset = updateTimeData(a)
        $("#timeset").val(timeset);
        $("._timesetSelected").html(n.join(""))
    }

    window._getDateRange = function() {
        var e = $("#timeset").val();
        //var e = "0000000000000000000000000000000000000000000000000000000111111111111111111111111000000000000000000000000111111111111111111111111000000000000000000000000111111111111111111111111000000000000000000000000111111111111111111111111";
        return timeStrToArr(e)
    }
    $(".ButtonBg5").click(function() {
        var timeset = makeFullTimeStr();
        $("#timeset").val('');
        document.getElementById("timesetHandlerId").loadData([]);
        $("._timesetSelected").html("-")
    });
    $("._quickSel").click(function() {
        $("#hour_set_type").val(0)
        $("._advanceSelForm").hide()
        $("._quickSel").hide()
        $("._quickSelForm").show()
        $("._advanceSel").show()
    });
    $("._advanceSel").click(function() {
        $("#hour_set_type").val(1)
        $("._quickSelForm").hide()
        $("._advanceSel").hide()
        $("._advanceSelForm").show()
        $("._quickSel").show()
    });
});
function makeFullTimeStr() {
    return (new Array(336 + 1)).join('1');
}
function timeArrToReadable(data) {
    var hc = [],
        i, len;
    var label = ["星期一", "星期二", "星期三", "星期四", "星期五", "星期六", "星期日"];
    if (!data || !data.length) {
        data = [];
        for (i = 0, len = 7; i < len; i++) {
            data.push([]);
        }
    }
    for (i = 0; i < data.length; i++) {
        var ht = [];
        var item = data[i];
        if (item.length > 0) {
            for (var j = 0; j < item.length; j++) {
                var range = item[j];
                var sh = parseInt(range.start, 10);
                var eh = parseInt(range.end, 10);
                ht.push('<span>' + sh + (range.start > sh ? ':30' : ':00') + '-' + eh + (range.end > eh ? ':30' : ':00') + '<\/span>');
                if (j < item.length - 1) {
                    ht.push(', ');
                }
            }
        }
        if (ht.length > 0) {
            hc.push(label[i] + ':' + ht.join(''));
        }
    }
    return hc;
}
function timeStrToArr(str) {
    str = str ? str : '';
    var start = 0,
        end = 0,
        pos, i, len;
    var hlist = [];
    for (i = 0, len = 7; i < len; i++) {
        hlist.push([]);
    }
    var f = function(start, end) {
        start = ((start - 1) % 48) / 2;
        end = ((end - 1) % 48 + 1) / 2;
        var o = {
            'start': start,
            'end': end
        };
        return o;
    };
    for (i = 0, len = str.length; i < len; i++) {
        var v = str.slice(i, i + 1);
        if (v == 1 && start == 0) {
            start = end = i + 1;
            continue;
        }
        if (v == 1 && start != 0) {
            end = i + 1;
        }
        if ((v == 0 && start != 0) || (i == (len - 1) && start != 0) || ((i + 1) % 48 == 0 && start != 0)) {
            pos = parseInt((start - 1) / 48, 10);
            hlist[pos].push(f(start, end));
            start = 0;
            end = 0;
            continue;
        }
    }
    if (start != 0) {
        pos = parseInt((start - 1) / 48, 10);
        hlist[pos].push(f(start, end));
    }
    setInitTimeData(hlist)
    //alert(hlist)
    return hlist;
}

function timeArrToBitStr(arr) {
    arr = arr ? arr : [];
    var num_start = 0,
        i;
    var hlist = {};
    for (i = 0; i < arr.length; i++) {
        var item = arr[i];
        if (item.length > 0) {
            for (var j = 0; j < item.length; j++) {
                var range = item[j];
                var sh = range.start;
                var eh = range.end;
                sh *= 2;
                eh *= 2;
                for (var k = sh; k < eh; k++) {
                    hlist[num_start + k + 1] = 1;
                }
            }
        }
        num_start += 48;
    }
    var str = '';
    for (i = 0; i < 336; i++) {
        var bit = (hlist[i + 1]) ? '1' : '0';
        str += bit;
    }
    str = str.replace(/0*$/, '');
    return str;
}
function timeArrSelColumn(arr) {
    var flag = true,
        t0;
    flag = flag && (arr.length == 7);
    if (flag) {
        $.each(arr, function(k, v) {
            v = v.substring(v.indexOf(':') + 1);
            if (k == 0) {
                t0 = v;
                flag = flag && (!(/:30/).test(t0));
            } else {
                flag = flag && (t0 == v);
            }
            return flag;
        });
    }
    t0 && (t0.split(',').length >= 2) && (flag = false);
    if (flag) {
        flag = t0.match(/\d+:\d+/g);
    }
    return flag;
}
function getTimeReadArr(timearr) {
    var strarr = timeArrToReadable(timearr),
        isColumn = false,
        r0;
    isColumn = timeArrSelColumn(strarr);
    if (isColumn) {
        r0 = strarr[0];
        strarr = [r0.substring(r0.indexOf(':') + 1)];
    }
    return strarr;
}
function setFlashTimeData(flashId, data) {
    var fobj = document.getElementById(flashId);
    if (fobj && fobj.loadData) {
        window._dateRangeCallBack(data);
        fobj.loadData(data);
    } else {
        setTimeout(function() {
            setFlashTimeData(flashId, data);
        }, 50);
    }
}
function setInitTimeData(i) {
    if(typeof(i)=="undefined"){
        return
    }
    var a = timeArrToBitStr(i),
        n = [];
    n = timeArrToReadable(i);
    $.each(n, function(e, t) {
        n[e] = "<li>" + t + "</li>"
    });
    var timeset = updateTimeData(a)
    $("#timeset").val(timeset);
    $("._timesetSelected").html(n.join(""))
}
function updateTimeData(timeset){
    var len = timeset.length
    var dif_len = 336 - len
    for(var i= 0;i < dif_len;i ++){
        timeset = timeset + "0"
    }
    return  timeset
}