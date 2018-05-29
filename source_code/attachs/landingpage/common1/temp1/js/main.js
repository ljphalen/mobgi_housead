/**
 * 获取设备类型
 * @returns {string}
 */
function getDevType() {
    var getUa = navigator.userAgent.toLowerCase();
    var devType = 'android';
    if (getUa.match(/(iphone|ipad|ipod)/i)) {
        devType = 'ios';
    } else {
        devType = 'android';
    }
    return devType;
}

/**
 * 获取cookie
 * @param name
 * @returns {null}
 */
function getCookie(name) {
    var arr = document.cookie.match(new RegExp("(^| )" + name + "=([^;]*)(;|$)"));
    if (arr != null) {
        return unescape(arr[2]);
    } else {
        return null;
    }
}

/**
 * 刷新页面
 */
function refresh() {
    window.location.reload();
}

/**
 * 设置cookie
 * @param name
 * @param value
 */
function setCookie(name, value) {
    var Days = 1;
    var exp = new Date();  //获得当前时间
    exp.setTime(exp.getTime() + Days * 24 * 60 * 60 * 1000);  //换成毫秒
    document.cookie = name + "=" + escape(value) + ";expires=" + exp.toGMTString();
}

/**
 * 判断是否微信浏览
 * @returns {boolean}
 */
function isWeixin() {
    var ua = navigator.userAgent.toLowerCase();
    if (ua.match(/MicroMessenger/i) == "micromessenger") {
        return true;
    } else {
        return false;
    }
}

/**
 * 显示指导页
 * @param imgid
 * @returns {Function}
 */
function showZdy(imgid) {
    return function (){
        $("#"+imgid).show(); //指导页
        document.getElementById('box').style.display = "block"; //遮罩层
    }
}