/********************
@author:linzhang
@date:2016-08-30
@功能:图片轮播js
********************/

(function (window, undefined) {
    var run = {
        length: 0,
        isrunling: true,
        d: 0,
        d2: 0,
        index: 0,
        width: 0,
        zindex: 0,
        thisTime: 0,
        oldTime: 0,
        initialContent: "",
        length2:0,
        init: function () {
            $(".runContent").append("<li>" + $(".runContent li:first").html() + "</li>");
            $(".runContent").css("width", $(".runContent").children().size() * 640 + "px");
            run.changeScreen();
            run.length = $(".d_img").children().size();
            run.length2 = $(".runContent").children().size();
            var ind = 0;
            $(".d_img li").each(function () {
                ind = $(this).index();
                if (ind == 0) $(this).addClass("filter");
                else if (ind == 1) $(this).addClass("bwm_3 blur");
                else if (ind == run.length - 1) $(this).addClass("bwm_2 blur");
                else $(this).addClass("bwm_list");
            });
            $(".bwm_list").show();
            $(".d_img").swipe({ fingers: 'all', swipeLeft: run.swipe1, swipeRight: run.swipe2 });
            $(".runContent").swipe({ fingers: 'all', swipeLeft: run.swipe3, swipeRight: run.swipe4 });
        },

        swipe1: function (event, direction, distance, duration, fingerCount) {
            run.thisTime = Date.now();
            if (run.thisTime - run.oldTime > 500) {
                run.d++;
                run.d = run.d > run.length - 1 ? 0 : run.d;
                run.rolj();
                $(".d_menu li").eq(run.d).addClass("cur").siblings().removeClass("cur");
            }
        },

        swipe2: function (event, direction, distance, duration, fingerCount) {
            run.thisTime = Date.now();
            if (run.thisTime - run.oldTime > 500) {
                run.d--;
                run.d = run.d < 0 ? run.length - 1 : run.d;
                run.rolj();
                $(".d_menu li").eq(run.d).addClass("cur").siblings().removeClass("cur");
            }
        },
        swipe3: function (event, direction, distance, duration, fingerCount) {
            run.thisTime = Date.now();
            if (run.thisTime - run.oldTime > 500) {
                run.d2++;
                $(".runContent").animate({ "left": "-" + 640 * run.d2 + "px" }, 500, function () {
                    if (run.d2 == run.length2 - 1) {
                        run.d2 = 0;
                        $(".runContent").css({ "left": "0px" });
                    }
                    run.oldTime = Date.now();
                    $(".runNav li").eq(run.d2).addClass("cur").siblings().removeClass("cur");
                });
            }
        },

        swipe4: function (event, direction, distance, duration, fingerCount) {
            run.thisTime = Date.now();
            if (run.thisTime - run.oldTime > 500) {
                run.d2--;
                if (run.d2 < 0) {
                    $(".runContent").css({ "left": "-" + (run.length2 - 1) * 640 + "px" });
                    run.d2 = run.length2 - 2;
                }
                $(".runContent").animate({ "left": "-" + 640 * run.d2 + "px" }, 500, function () { run.oldTime = Date.now(); });
                $(".runNav li").eq(run.d2).addClass("cur").siblings().removeClass("cur");
            }
        },

        rolj: function () {
            $(".d_img li").each(function () {
                run.index = $(this).index();
                run.scale = 1;
                if (run.index == 0) {
                    if (run.d % run.length == 0) {
                        run.width = 0;
                        run.scale = 1.2;
                        run.zindex = 5;
                        $(this).removeClass("blur");
                    } else if (run.d % run.length == 1) {
                        run.width = -230;
                        run.zindex = 3;
                        $(this).addClass("blur");
                    } else if (run.d % run.length == run.length - 1) {
                        run.width = 230;
                        run.zindex = 4;
                        $(this).addClass("blur");
                    } else {
                        run.width = 0;
                        run.scale = 0;
                        run.zindex = 1;
                    }
                } else if (run.index == 1) {
                    if (run.d % run.length == 0) {
                        run.width = 0;
                        run.zindex = 4;
                        $(this).addClass("blur");
                    } else if (run.d % run.length == 1) {
                        run.width = -230;
                        run.scale = 1.2;
                        run.zindex = 5;
                        $(this).removeClass("blur");
                    }
                    else if (run.d % run.length == 2) {
                        run.width = -460;
                        run.zindex = 3;
                        $(this).addClass("blur");
                    } else {
                        run.width = -230;
                        run.scale = 0;
                        run.zindex = 1;
                    }
                } else if (run.index == run.length - 1) {
                    if (run.d % run.length == 0) {
                        run.width = 0;
                        run.zindex = 3;
                        $(this).addClass("blur");
                    } else if (run.d % run.length == run.length - 2) {
                        run.width = 460;
                        run.zindex = 4;
                        $(this).addClass("blur");
                    }
                    else if (run.d % run.length == run.length - 1) {
                        run.width = 230;
                        run.zindex = 5;
                        run.scale = 1.2;
                        $(this).removeClass("blur");
                    } else {
                        run.width = 230;
                        run.zindex = 1;
                        run.scale = 0;
                    }
                } else {
                    if (run.index - run.d == 1) {
                        run.width = 230;
                        run.zindex = 4;
                        $(this).addClass("blur");
                    } else if (run.index - run.d == 0) {
                        run.width = 0;
                        run.scale = 1.2;
                        run.zindex = 5;
                        $(this).removeClass("blur");
                    } else if (run.index - run.d == -1) {
                        run.width = -230;
                        run.zindex = 3;
                        $(this).addClass("blur");
                    } else {
                        run.width = 0;
                        run.scale = 0;
                        run.zindex = 1;
                    }
                }
                run.rolling($(this), run.width, run.scale, run.zindex);
            });
            run.oldTime = Date.now();
        },

        rolling: function (obj, width, scale, index) {
            obj.css({ "z-index": index, '-webkit-transform': 'translate(' + width + 'px,0) scale(' + scale + ',' + scale + ')' });
        },

        changeScreen: function () {
            if ($(window).width() > $(window).height()) {
                run.initialContent = 'target-densitydpi=device-dpi, width=1136, user-scalable=no';
                document.getElementsByName('viewport')[0].setAttribute('content', run.initialContent);
                $(".page").addClass("page_width").removeClass("page_height");
                $(".page_width").css("height", $(window).height());  
                $(".banner").hide().siblings(".banner-width").show();
            } else {
                run.initialContent = 'target-densitydpi=device-dpi, width=640, user-scalable=no';
                document.getElementsByName('viewport')[0].setAttribute('content', run.initialContent);
                $(".page").addClass("page_height").removeClass("page_width");
                $(".banner-width").hide().siblings(".banner").show();
                $(".banner").css("height", $(window).height() - 176 + "px");
            }
        }


    };
    window.run = run;
})(window);


$(document).ready(function () {
    run.init(); //初始化;
});
$(window).resize(function () {
    run.changeScreen();
});
