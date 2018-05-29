;(function($){
    function dialogClass(ele,opt){
        this.opt = opt;
        this.$mask = null;//遮罩蒙层
        this.$popup = null;//窗口对象
        this.ele = $(ele);
    };
    dialogClass.prototype.show = function(){
        var self = this;
        this.ele.click(function(){
            self.setParam(self.opt.param,function(data){
                if(self.opt.mask){
                    self.$mask = self.setMask();
                    $("body").append(self.$mask);
                    self.$mask.height($(document).height());
                }
                var id = self.ele.find("input:hidden").val();
                self.$popup = self.drawPopup(id,data);
                $("body").append(self.$popup);
                self.setPosition();
            });
        });
        $(window).resize(function(){
            self.setPosition();
            if(self.opt.mask){
                self.$mask.height($(document).height());
            }
        });
    };
    dialogClass.prototype.setParam = function(param,fn){
        var self = this;
        $.ajax({
            type:"POST",
            dataType:"json",
            url: self.opt.url,
            data:param,
            success: function(data){
                if(data){
                    fn && fn(data);
                }
            }
        });
    };
    dialogClass.prototype.close = function(fn){
        if(this.$mask){
            this.$mask.remove();
        };
        this.$popup.remove();
        fn && fn();
    };
    dialogClass.prototype.drawPopup = function(id,data){
        var self = this;
        var $popupHtml = $('<div class="popup_container" style="background:#fff;border:1px solid #a6a5a5;position:absolute;top:0;left:0;z-index:'+self.opt.zIndex+';">' +
                            '<div class="pop-up">'+
                                '<div id="dialogContent" class="dialogContent">' +
                                     '<p class="loading">数据加载中...</p>'+
                                '</div>' +
                            '</div>'+
                          '</div>');
        if(this.opt.dialogClass){//额外的样式
            $popupHtml.addClass(this.opt.dialogClass);
        }
        var pos = ($.browser.msie && parseInt($.browser.version,10) <= 6 ) ? 'absolute' : 'fixed'; // IE6 Fix
        $popupHtml.css({
            position: pos,
            zIndex: self.opt.zIndex,
            margin: 0,
            width:self.opt.width + "px",
            height:self.opt.height + "px"
        });
        $popupHtml.find(".pop-up").css({
            "height":self.opt.height,
            "zoom":"1"
        });
        self.opt.content = this.getContent(id,data);
        $popupHtml.find("#dialogContent").empty().append(self.opt.content);
        return $popupHtml;
    };
    dialogClass.prototype.getContent= function(id,data){
        var self = this;
        var $content = $("<div>" +
                            "<div style='height:40px;line-height:40px;padding:0 0px 0 30px;border-bottom:1px solid #d0d0d0;background:#ececec;'>" +
                                "<span>应用类型选择</span>" +
                                "<a href='javascript:void(0)' id='close' style='margin-top:0px;margin-right:-5px;'></a>" +
                            "</div>"+
                            "<div style='height:257px;padding:10px 0;overflow-y:auto;' id='listContent'>数据加载中...</div>" +
                            "<div style='height:56px;border-top:1px dashed #b3b2b2;background:#f0f0f0;'>" +
                                "<a href='javascript:void(0)' id='ok' style='float:right;margin:10px 10px 0 0;width:78px;height:28px;text-align:center;color:#fff;font-weight:700;font-size:14px;line-height:28px;border:1px solid #53b71c;border-bottom:1px solid #337a11;background:#5fb321;background:-webkit-gradient(linear,left top,left bottom,from(#77c92a),color-stop(0.5,#5fb321),to(#479d19));background:-moz-linear-gradient(top,#77c92a,#5fb321 50%,#479d19 100%);'>确定</a>" +
                            "</div>" +
                        "</div>");
        var _content = "";
        if(data){
            $.each(data,function(k,v){
                _content += "<dl style='height:32px;line-height:32px;'><dt style='width:110px;float:left;text-align:right;padding-right:5px;font-size:14px;font-weight:700;'>" + k + "：</dt><dd style='float:left;width:340px;'>";
                $.each(v,function(i,n){
                    if(id && id == i){
                         _content += "<a style='margin-right:5px;font-weight:700;color:#f00;white-space: nowrap;' href='javascript:void(0);' name='" + i + "'>" + n + "</a>";
                    }else{
                         _content += "<a style='margin-right:5px;white-space: nowrap;' href='javascript:void(0);' name='" + i + "'>" + n + "</a>";
                    }
                })
                _content += "</dd></dl>";
            })
            $content.find("#listContent").empty().append(_content);
            $content.find("dd").find("a").click(function(){
                self.ele.find("span").text($(this).text());
                self.ele.find("input:hidden").val($(this).attr("name")).change();
                self.close();
                return false;
            });
            $content.find("#close").click(function(){
               self.close();
            });
            $content.find("#ok").click(function(){
                self.close();
            });
        }
        return  $content;
    };
    dialogClass.prototype.setMask = function(){
        var self = this;
        var $mask = $('<div><iframe style="position:absolute;width:100%;height:100%;filter:alpha(opacity=0);opacity=0;border-style:none;"></iframe></div>')
        $mask.css({
            position: 'absolute',
            zIndex: self.opt.zIndex-1,
            top: '0',
            left: '0',
            width: '100%',
            height: '100%',
            background: self.opt.maskColor,
            opacity: self.opt.maskOpacity
        });
        return $mask;
    };
    dialogClass.prototype.setPosition = function(){
        var top = ($(window).height()/2) - (this.$popup.outerHeight()/2);
        var left = ($(window).width()/2) - (this.$popup.outerWidth()/2);
        if( $.browser.msie && parseInt($.browser.version,10) <= 6 ) {top = top + $(window).scrollTop();}
        this.$popup.css({
            top: top + 'px',
            left: left + 'px'
        });
    };
    $.fn.appType = function(options){
        var options=$.extend({
            width:478,
            height:375,
            content:'',       //要展示的内容
            url:"",
            param:{}, //向后台发起请求的参数
            zIndex:1000,
            mask:true,        //是否有遮罩
            dialogClass:'', //窗口自定义样式
            maskColor:'#000', //遮罩颜色
            maskOpacity:'.14' //遮罩不透明度
        },options);
        return this.each(function(i,ele){
            var obj = new dialogClass(ele,options);
            obj.show();
        });
    };
})(jQuery);














