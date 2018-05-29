(function($) {
    $.alerts = {
        alert: function(title, message, callback) {
            if( title == null ) title = 'Alert';
            $.alerts._show(title, message, null, 'alert', function(result) {
                if( callback ) callback(result);
            });
        },

        confirm: function(title, message, callback) {
            if( title == null ) title = 'Confirm';
            $.alerts._show(title, message, null, 'confirm', function(result) {
                if( callback ) callback(result);
            });
        },

        _show: function(title, msg, value, type, callback) {
            var _html = "";
            _html += '<div id="mb_box"></div><div id="mb_con"><span id="mb_tit">' + title + '</span>';
            _html += '<div id="mb_msg">' + msg + '</div><div id="mb_btnbox">';
            if (type == "alert") {
                _html += '<input id="mb_btn_ok" type="button" value="确定" />';
            }
            if (type == "confirm") {
                _html += '<input id="mb_btn_ok" type="button" value="确定" />';
                _html += '<input id="mb_btn_no" type="button" value="取消" />';
            }
            _html += '</div></div>';
            //必须先将_html添加到body，再设置Css样式
            $("body").append(_html); GenerateCss();
            switch( type ) {
                case 'alert':

                    $("#mb_btn_ok").click( function() {
                        $.alerts._hide();
                        callback(true);
                    });
                    $("#mb_btn_ok").focus().keypress( function(e) {
                        if( e.keyCode == 13 || e.keyCode == 27 ) $("#mb_btn_ok").trigger('click');
                    });
                    break;
                case 'confirm':

                    $("#mb_btn_ok").click( function() {
                        $.alerts._hide();
                        if( callback ) callback(true);
                    });
                    $("#mb_btn_no").click( function() {
                        $.alerts._hide();
                        if( callback ) callback(false);
                    });
                    $("#mb_btn_no").focus();
                    $("#mb_btn_ok, #mb_btn_no").keypress( function(e) {
                        if( e.keyCode == 13 ) $("#mb_btn_ok").trigger('click');
                        if( e.keyCode == 27 ) $("#mb_btn_no").trigger('click');
                    });
                    break;


            }
        },
        _hide: function() {
            $("#mb_box,#mb_con").remove();
        }
    }
    // Shortuct functions
    zdalert = function(title, message, callback) {
        $.alerts.alert(title, message, callback);
    }

    zdconfirm = function(title, message, callback) {
        $.alerts.confirm(title, message, callback);
    };
    //生成Css
    var GenerateCss = function () {

        $("#mb_box").css({ width: '100%', height: '100%', zIndex: '99999', position: 'fixed',
            filter: 'Alpha(opacity=60)', backgroundColor: 'black', top: '0', left: '0', opacity: '0.6'
        });
        $("#mb_con").css({ zIndex: '999999', width: '50%', position: 'fixed',
            backgroundColor: 'White', borderRadius: '15px'
        });
        $("#mb_tit").css({ display: 'block', fontSize: '14px', color: '#444', padding: '10px 15px',
            backgroundColor: '#DDD', borderRadius: '15px 15px 0 0',
            borderBottom: '3px solid #009BFE', fontWeight: 'bold'
        });
        $("#mb_msg").css({ padding: '20px', lineHeight: '20px',
            borderBottom: '1px dashed #DDD', fontSize: '13px'
        });
        $("#mb_ico").css({ display: 'block', position: 'absolute', right: '10px', top: '9px',
            border: '1px solid Gray', width: '18px', height: '18px', textAlign: 'center',
            lineHeight: '16px', cursor: 'pointer', borderRadius: '12px', fontFamily: '微软雅黑'
        });
        $("#mb_btnbox").css({ margin: '1px 0 10px 0', textAlign: 'center' });
        $("#mb_btn_ok,#mb_btn_no").css({ width: '50px', height: '20px', color: 'white', border: 'none' });
        $("#mb_btn_ok").css({ backgroundColor: '#168bbb' });
        $("#mb_btn_no").css({ backgroundColor: 'gray', marginLeft: '20px' });
        //右上角关闭按钮hover样式
        $("#mb_ico").hover(function () {
            $(this).css({ backgroundColor: 'Red', color: 'White' });
        }, function () {
            $(this).css({ backgroundColor: '#DDD', color: 'black' });
        });

        var _widht = document.documentElement.clientWidth; //屏幕宽
        var _height = document.documentElement.clientHeight; //屏幕高

        var boxWidth = $("#mb_con").width();
        var boxHeight = $("#mb_con").height();

        //让提示框居中
        $("#mb_con").css({ top: (_height - boxHeight) / 2 + "px", left: (_widht - boxWidth) / 2 + "px" });
    }
})(jQuery);
function bin2hex(s) {
    var i, l, o = '', n;
    s += '';
    for (i = 0, l = s.length; i < l; i++) {
        n = s.charCodeAt(i).toString(16);
        o += n.length < 2 ? '0' + n : n;
    }
    return o;
}

function getUUID(url) {
    var id;
    $.ajax({
        url:url,
        async: false,
        data: '',
        type: 'get',
        dataType: '',
        jsonp: '',
        success: function (data) {
            if(data.success){
                id = data.data.user_id;
            }
        },
        error: function (xhr, msg, e) {
            console.log(xhr + "," + msg + "," + e);
        }
    });
    return id;

/*   var canvas = document.createElement('canvas');
    var ctx = canvas.getContext('2d');
    var txt = url;
    ctx.textBaseline = "top";
    ctx.font = "14px 'Arial'";
    ctx.textBaseline = "mobgi";
    ctx.fillStyle = "#f60";
    ctx.fillRect(125,1,62,20);
    ctx.fillStyle = "#069";
    ctx.fillText(txt, 2, 15);
    ctx.fillStyle = "rgba(102, 204, 0, 0.7)";
    ctx.fillText(txt, 4, 17);
    var b64 = canvas.toDataURL().replace("data:image/png;base64,","");
    var bin = atob(b64);
    var crc = bin2hex(bin.slice(-16,-12));
    return crc;*/

   /* var canvas = document.createElement('canvas');
    var ctx = canvas.getContext("2d");
    ctx.font = "24px Arial";
    ctx.fillText(url,22,33);
    ctx.moveTo(0,60);
    ctx.lineTo(100,60);
    ctx.stroke();
    //大家就随意创建一个canvas标签就是
    var b64 = canvas.toDataURL().replace("data:image/png;base64,","");
    //然后用toDataURL方法对生成的canvas图像进行64码进制转换
    //console.log("b64="+b64);
    var bin = atob(b64);
    //console.log("bin="+bin);bin这里是一张图片了，解码图片
    //这里使用js内置的 atob()方法将64进制的解码一下
    var crc = bin2hex(bin.slice(-16, -12));
    //console.log("crc="+crc)
    return crc;*/


}

function isPC() {
    if( navigator.userAgent.match(/Android/i)
        || navigator.userAgent.match(/webOS/i)
        || navigator.userAgent.match(/iPhone/i)
        || navigator.userAgent.match(/iPad/i)
        || navigator.userAgent.match(/iPod/i)
        || navigator.userAgent.match(/BlackBerry/i)
        || navigator.userAgent.match(/Windows Phone/i)
    ){
        return false;
    }
    else {
        return true;
    }

}

function drawPrize() {
    if (state) {
        isClickGoodsDetail = false;
        state = false;
        // ajax 请求
        var  prize_id = getPrizeId();
        if(prize_id){
            //位置
            var position;
            $(".list li").each(function () {
                var id = $(this).attr("data-id");
                if (id == prize_id) {
                    position = $(this).attr("data-index");
                }
            });
            roundAction(0, 3, 0, position);
        }

    }
}

function getPrizeId() {
    var goods_id = 0 ;
    $.ajax({
        url: draw_url,
        async: false,
        data: {'token':token,'user_id':user_id,'activity_id':activity_id},
        type: 'post',
        dataType: 'json',
        jsonp: '',
        success: function (data) {
            if(data.success ){
                goods_id = data.data.id;
                prizeData = data.data;
            }else{
                isClickGoodsDetail = true;
                state = true;
                zdalert('提示',data.msg);
            }
        },
        error: function (xhr, msg, e) {
            console.log(xhr + "," + msg + "," + e);
        }
    });
    return goods_id;
}

// 递归
function roundAction(num, end, max, id) {
    var arr = [],
        isEnd = false;
    $(".list li").each(function () {
        var index = parseInt($(this).attr("data-index"));
        var spend = max + 50;
        if (num == 0) {
            spend = (200 - (10 * index)) * index; // 加速度
        } else if (num == end) {
            isEnd = true;
            spend = spend + ((50 * index) * index); // 减速度
        } else {
            spend = spend + (50 * index); // 匀速
            //sconsole.log(index, spend)
        }
        arr.push(spend)
        if (isEnd) {
            if (id >= index) {
                // console.log(index)
                animationAction(index, $(this), spend); // 动画
                if (id == index) {
                    setTimeout(function () {
                        state = true;
                        isClickGoodsDetail = true;
                        showDrawResultDiv();

                    }, spend + 500);
                }
            }
        } else {
            animationAction(index, $(this), spend); // 动画
        }

    });
    arr.sort(function (a, b) {
        return b - a;
    });
    if (num == end) {
        return false;
    } else {
        num = num + 1;
        //console.log("arr", arr[0])
        roundAction(num, end, arr[0], id);
    }
}

function showDrawResultDiv() {
    $(".wrapper").css("height", '100%');
    $(".wrapper").css("overflow", 'hidden');
    if(prizeData.isPrize){
        $(".success-wrap").find(".success-img img").attr("src",prizeData.big_img);
        $(".success-wrap").find(".desc").html('获得：'+prizeData.title+'x1');
        $(".success-wrap").find(".submit").attr("href",prizeData.goodsUrl);
        $(".success-wrap").show();
    }else{
        $(".error-wrap").show();
    }
    $("#draw_times").text(prizeData.times);
}


// 延时动画
function animationAction(index, _t, time) {
    if (index) {
        setTimeout(function () {
            $(".list li i").removeClass("active");
            _t.find("i").addClass("active");
        }, time);
    }
}

function getUser(url) {
	$.ajax({
	  url: url+'?user_id='+user_id+'&activity_id='+activity_id,
	  async: true,
	  data: '',
	  type: 'get',
	  dataType: '',
	  jsonp: '',
	  success: function (data) {
		 if(data.success && isShowPrizeTimes){
            $("#draw_times").text(data.data.times);
         }
	  },
	  error: function (xhr, msg, e) {
		  console.log(xhr + "," + msg + "," + e);
	  }
	});
}

function initGoodsList() {
    if(!goodsData.length){
        return false;
    }
    var mapping = {
        0 :{  index: 1 } ,
        1: { index : 2  } ,
        2: { index:  3 } ,
        3: { index : 8  } ,
        4: { index : 4  } ,
        5: { index : 7  } ,
        6 :{ index: 6 } ,
        7: { index : 5  }
    };
    var temp = '';
    for(var i in goodsData){
        temp = temp + `<li data-index="`+mapping[i]['index'] +`" data-id="`+goodsData[i]['id'] +`">
                    <i class=""></i>
                    <p class="content">
                        <u>
                            <img  src="`+goodsData[i]['icon'] +`" />
                        </u>
                        <span>`+goodsData[i]['title'] +`</span>
                    </p>
                </li>`;
    }
    $(".list").html(temp);
    $($(".list li").get(4)).before('<li data-index="0" data-id="0" class="start"></li>');

}


function goodsDeatail(id) {
    var item = {};
    for(var i in goodsData){
        if(goodsData[i]['id'] == id){
            item = goodsData[i];
            break;
        }
    }
    $(".view-wrap").find(".banner img").attr("src",item.big_img);
    $(".view-wrap").find(".banner-title").text(item.title);
    $(".view-wrap").find(".banner-desc").html(item.desc);
    $(".view-wrap").show();
}