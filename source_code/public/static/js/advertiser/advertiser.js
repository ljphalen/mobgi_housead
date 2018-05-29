/**
 * @description MOBGI 全局对象，负责前端的交互组织
 * @namespace 全局的命名空间
 */
isUndef = function(a) {
    return typeof a == "undefined";
};
isNull = function(a) {
    return typeof a == "object" && !a;
};
var isFF = (navigator.userAgent.toLowerCase().indexOf("firefox") != -1);
var isIE = (navigator.appVersion.indexOf("MSIE") != -1) ? true : false;
var isOpera = (navigator.userAgent.indexOf("Opera") != -1) ? true : false;
var isChrome = (navigator.userAgent.indexOf("Chrome") != -1) ? true : false;
var isSafari = (navigator.userAgent.indexOf("Safari") != -1) ? true : false;
$(".tInput").live("focusin", function() {
    $(this).css('color', '#666');
});
var sendEmailBtn = function(email, cls, time) {
    email.attr("disabled", "disabled").addClass(cls);
    var txt = email.attr("value");
    var timer = setInterval(function() {
        time--;
        email.attr("value", txt + "(" + time + ")S");
        $("#sendEmail").css('background-color', '#999999');
        if (time == 0) {
            $("#sendEmail").css('background-color', '#039954');
            clearInterval(timer);
            email.attr("disabled", false).attr("value", txt).removeClass(cls);
        }
    }, 1000);
}

var isEmail = function(email) {
    var reg = /^([a-zA-Z0-9\._-])+@([a-zA-Z0-9_-])+((\.[a-zA-Z0-9_-]{2,6}){1,3})$/;
    if (!reg.test(email)) {
        return false;
    } else {
        return true;
    }
}
function isMobile(sMobile) {
    if (!(/^1[3|4|5|8][0-9]\d{4,8}$/.test(sMobile))) {
        return false;
    }
    return true;
}
function isDate(str) {
    var reg = /^((((1[6-9]|[2-9]\d)\d{2})-(0?[13578]|1[02])-(0?[1-9]|[12]\d|3[01]))|(((1[6-9]|[2-9]\d)\d{2})-(0?[13456789]|1[012])-(0?[1-9]|[12]\d|30))|(((1[6-9]|[2-9]\d)\d{2})-0?2-(0?[1-9]|1\d|2[0-8]))|(((1[6-9]|[2-9]\d)(0[48]|[2468][048]|[13579][26])|((16|[2468][048]|[3579][26])00))-0?2-29-))$/
    if (reg.test(str)) {
        return true;
    } else {
        return false;
    }
}
function isPhone(strPhone) {
    var phoneRegWithArea = /^[0][1-9]{2,3}-[0-9]{5,8}$/;
    var phoneRegNoArea = /^[1-9]{1}[0-9]{5,8}$/;
    if (strPhone.length > 9) {
        if (phoneRegWithArea.test(strPhone)) {
            return true;
        } else {
            return false;
        }
    } else {
        if (phoneRegNoArea.test(strPhone)) {
            return true;
        } else {
            return false;
        }
    }
}
//验证QQ
function isQQ(qq)
{
    if (qq.search(/^[1-9]\d{4,12}$/) != -1) {
        return true;
    }
    else {
        return false;
    }
}
function isNumber(s) {
    var regu = "^[0-9]+$";
    var re = new RegExp(regu);
    if (s.search(re) != -1) {
        return true;
    } else {
        return false;
    }
}

/*
 根据〖中华人民共和国国家标准 GB 11643-1999〗中有关公民身份号码的规定，公民身份号码是特征组合码，由十七位数字本体码和一位数字校验码组成。排列顺序从左至右依次为：六位数字地址码，八位数字出生日期码，三位数字顺序码和一位数字校验码。
 地址码表示编码对象常住户口所在县(市、旗、区)的行政区划代码。
 出生日期码表示编码对象出生的年、月、日，其中年份用四位数字表示，年、月、日之间不用分隔符。
 顺序码表示同一地址码所标识的区域范围内，对同年、月、日出生的人员编定的顺序号。顺序码的奇数分给男性，偶数分给女性。
 校验码是根据前面十七位数字码，按照ISO 7064:1983.MOD 11-2校验码计算出来的检验码。
 
 出生日期计算方法。
 15位的身份证编码首先把出生年扩展为4位，简单的就是增加一个19或18,这样就包含了所有1800-1999年出生的人;
 2000年后出生的肯定都是18位的了没有这个烦恼，至于1800年前出生的,那啥那时应该还没身份证号这个东东，⊙﹏⊙b汗...
 下面是正则表达式:
 出生日期1800-2099  (18|19|20)?\d{2}(0[1-9]|1[12])(0[1-9]|[12]\d|3[01])
 身份证正则表达式 /^\d{6}(18|19|20)?\d{2}(0[1-9]|1[12])(0[1-9]|[12]\d|3[01])\d{3}(\d|X)$/i            
 15位校验规则 6位地址编码+6位出生日期+3位顺序号
 18位校验规则 6位地址编码+8位出生日期+3位顺序号+1位校验位
 
 校验位规则     公式:∑(ai×Wi)(mod 11)……………………………………(1)
 公式(1)中： 
 i----表示号码字符从由至左包括校验码在内的位置序号； 
 ai----表示第i位置上的号码字符值； 
 Wi----示第i位置上的加权因子，其数值依据公式Wi=2^(n-1）(mod 11)计算得出。
 i 18 17 16 15 14 13 12 11 10 9 8 7 6 5 4 3 2 1
 Wi 7 9 10 5 8 4 2 1 6 3 7 9 10 5 8 4 2 1
 
 */
//身份证号合法性验证 
//支持15位和18位身份证号
//支持地址编码、出生日期、校验位验证
function IdentityCodeValid(code) {
    var city = {11: "北京", 12: "天津", 13: "河北", 14: "山西", 15: "内蒙古", 21: "辽宁", 22: "吉林", 23: "黑龙江 ", 31: "上海", 32: "江苏", 33: "浙江", 34: "安徽", 35: "福建", 36: "江西", 37: "山东", 41: "河南", 42: "湖北 ", 43: "湖南", 44: "广东", 45: "广西", 46: "海南", 50: "重庆", 51: "四川", 52: "贵州", 53: "云南", 54: "西藏 ", 61: "陕西", 62: "甘肃", 63: "青海", 64: "宁夏", 65: "新疆", 71: "台湾", 81: "香港", 82: "澳门", 91: "国外 "};
    var tip = "";
    var pass = true;

    if (!code || !/^\d{6}(18|19|20)?\d{2}(0[1-9]|1[12])(0[1-9]|[12]\d|3[01])\d{3}(\d|X)$/i.test(code)) {
        tip = "身份证号格式错误";
        pass = false;
    }

    else if (!city[code.substr(0, 2)]) {
        tip = "地址编码错误";
        pass = false;
    }
    else {
        //18位身份证需要验证最后一位校验位
        if (code.length == 18) {
            code = code.split('');
            //∑(ai×Wi)(mod 11)
            //加权因子
            var factor = [7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2];
            //校验位
            var parity = [1, 0, 'X', 9, 8, 7, 6, 5, 4, 3, 2];
            var sum = 0;
            var ai = 0;
            var wi = 0;
            for (var i = 0; i < 17; i++)
            {
                ai = code[i];
                wi = factor[i];
                sum += ai * wi;
            }
            var last = parity[sum % 11];
            if (parity[sum % 11] != code[17]) {
                tip = "校验位错误";
                pass = false;
            }
        }
    }
    if (!pass)
        // alert(tip);
        return pass;
}
//验证护照是否正确
function isPassport(number) {
    var str = number;
//在JavaScript中，正则表达式只能使用"/"开头和结束，不能使用双引号
    var Expression = /(Pd{7})|(Gd{8})/;
    var objExp = new RegExp(Expression);
    if (objExp.test(str) == true) {
        return true;
    } else {
        return false;
    }
}

var C_DEVID = getCookie("dev_id");

var URL_BASE = "http://www.mobgi.com/";
function ajaxPOST(url, arg, callback) {
    var callback = callback || function(data) {
        if (data.error == 0) {
            alert(data.msg);
        } else {
            alert(data.msg);
        }
    };
    $.ajax({
        type: "POST",
        url: url,
        data: arg,
        dataType: "json",
        success: function(response) {
            callback(response);
        }
    })
}

function ajaxGET(url, arg, callback) {
    var callback = callback || function(data) {
        if (data.error == 0) {
            alert(data.msg);
        } else {
            alert(data.msg);
        }
    };
    $.ajax({
        type: "GET",
        url: url,
        data: arg,
        dataType: "json",
        success: function(response) {
            callback(response);
        }
    })
}

function getParameter(parameter, url) {
    return isNull(url.match(new RegExp("[?#&]" + parameter + "=(.*?)(?:[#&]|$)", "i"))) ? "" : RegExp.$1;
}

function setCookie(name, value, sec) {
    if (arguments.length > 2) {
        var expireDate = new Date(new Date().getTime() + sec * 1000);
        document.cookie = name + "=" + escape(value) + "; path=/; domain=gougou.com; expires=" + expireDate.toGMTString();
    } else
        document.cookie = name + "=" + escape(value) + "; path=/; domain=gougou.com";
}

function setZoneCookie(name, value, sec) {
    if (arguments.length > 2) {
        var expireDate = new Date(new Date().getTime() + sec * 1000);
        document.cookie = name + "=" + escape(value) + "; path=/; domain=zone.gougou.com; expires=" + expireDate.toGMTString();
    } else
        document.cookie = name + "=" + escape(value) + "; path=/; domain=zone.gougou.com";
}

function getCookie(name) {
    return (document.cookie.match(new RegExp("(^" + name + "| " + name + ")=([^;]*)")) == null) ? "" : RegExp.$2;
}

//将JSON字符串转为JSON对象
function jsonStrToObject(jsonString) {
    if ($.browser.msie) {
        return eval('(' + jsonString + ')');
    } else {
        return new Function('return ' + jsonString)();
    }
}
/*
 层的显示与隐藏操作.调用如下:
 layoutControl.show('div1','titledivid'); //以动画方式显示层,并且使层可以移动.
 layoutControl.hide('div1'); //解除屏幕锁并且隐藏层.
 */
var layoutControl = {
    show: function(divid, titleid) {

        divCenter(divid);

        $('#' + divid).fadeIn('show');

        if (typeof(titleid) != 'undefined') {
            ddraggable(titleid, divid);
        }

    },
    hide: function(divid) {
        $('#' + divid).css('display', 'none');
    }
}
//层的托动效果 titleDivid:一般是强出层中的标题栏 dragDivid:整个用于托动的层
function ddraggable(titleDivid, dragDivid) {
    document.getElementById(titleDivid).style.cursor = "move";
    document.getElementById(titleDivid).onmousedown = function(e) {
        var posX;
        var posY;
        var fdiv = document.getElementById(dragDivid);
        if (!e)
            e = window.event;
        posX = e.clientX - parseInt(fdiv.style.left);
        posY = e.clientY - parseInt(fdiv.style.top);
        document.onmousemove = function(ev) {
            if (ev == null)
                ev = window.event;
            fdiv.style.left = (ev.clientX - posX) + "px";
            fdiv.style.top = (ev.clientY - posY) + "px";
            return false;
        }
        document.onmouseup = function() {
            document.onmousemove = null;
        }
    }
}
function tips() {
    var html = '<div class="delete_tan" id="tips" style="display:none">' +
            '<div class="head">' +
            '<a href="javascript:;" class="close"><img src="images/close.png" alt="" /></a>' +
            '</div>' +
            '<div class="btn" style="">' +
            '<div class="delete" id="msg">是否要删除消息？</div><br />' +
            '<input id="Button1" type="button" value="确定" class="sure"/>' +
            '<input id="Button2" type="button" value="取消" class="return"/>' +
            '</div>' +
            '</div>';

}