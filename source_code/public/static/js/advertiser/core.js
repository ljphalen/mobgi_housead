//JSON To String
function obj2str(o){
    var r = [];
    if(typeof o =="string") {
        return "\""+o.replace(/([\'\"\\])/g,"\\$1").replace(/(\n)/g,"\\n").replace(/(\r)/g,"\\r").replace(/(\t)/g,"\\t")+"\"";
    }
    if(typeof o == "object"){
        if(o===null) return "null";
        if(!o.sort){
            for(var i in o){
                r.push('"' + i + '"' + ":" + obj2str(o[i]));
            }
            if(!!document.all && !/^\n?function\s*toString\(\)\s*\{\n?\s*\[native code\]\n?\s*\}\n?\s*$/.test(o.toString)){
                r.push("toString:"+o.toString.toString());
            }
            r="{"+r.join()+"}";
        }else{
            for(var i =0;i<o.length;i++){
                r.push(obj2str(o[i]));
            }
            r="["+r.join()+"]";
        }
        return r;
    }
    return r.toString();
};
(function($){
    function dialogClass(opt){
        this.opt = opt;
        this.$mask = null;//�����ɲ�
        this.$popup = null;//���ڶ���
    };
    dialogClass.prototype.show = function(){
        var self = this;
        if(this.opt.mask){
            this.$mask = this.setMask();
            $("body").append(self.$mask);
            this.$mask.height($(document).height());
        }
        this.$popup = this.drawPopup();
        $("body").append(this.$popup);
        this.setPosition();
        $(window).resize(function(){
            self.setPosition();
        });
    };
    dialogClass.prototype.close = function(fn){
        if(this.$mask){
            this.$mask.remove();
        };
        this.$popup.remove();
        fn && fn();
    };
    dialogClass.prototype.drawPopup = function(){
        var self = this;
        var $popupHtml = $('<div class="popup_container" style="background:rgba(0,0,0,0.3);box-shadow:0 0 2px #666;position:relative;z-index:'+self.opt.zIndex+';">' +
                            '<div class="pop-up">'+
                                '<div id="dialogContent" class="dialogContent" style="font-size:14px;color:#333;display:inline-block;padding:40px 40px 10px 70px;line-height:24px;background:url(/misc/css/img/question.png) no-repeat 42px 41px;">' +
                                     '<p class="loading">数据加载中...</p>'+
                                '</div>' +
                            '</div>'+
                          '</div>');
        if(this.opt.dialogClass){
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
            "height":self.opt.height-6,
            "zoom":"1",
            "margin":"3px",
            "background":"#fff"
        });
        if(this.opt.title){
            var title = '<div style="height:40px;line-height:40px;padding:0 20px 0 30px;border-bottom:1px solid #d0d0d0;background:#ececec;">'+
                        '<span>'+self.opt.title+'</span>';
            title += '</div>';
            $popupHtml.find(".pop-up").prepend(title);
            $popupHtml.find("#close").click(function(){
                self.close();
            });
        }
        if(this.opt.closeButtom){
            var  $title = $('<a id="close" class="close" style="width:56px;height:31px;overflow:hidden;display:block;top:0;position:absolute;background:url(/misc/css/img/close.png) no-repeat;" href="javascript:void(0)"></a>');
            $title.click(function(){
                self.close();
            });
            $popupHtml.find(".pop-up").prepend($title);
        }
        if(this.opt.button){
            var $btnContent = $('<div class="submit"></div>');
            $.each(self.opt.button,function(id,v){
                $('<a id="'+id+'" class="'+v.className+'" style="margin:0 5px;">'+v.name+'</a>').appendTo($btnContent).click(function(){
                    v.fn && v.fn(self.$popup,self);
                   self.close();
                });
            });
            $btnContent.css({"position":"absolute","left":"0","bottom":"20px","width":"100%","text-align":"center"});
            $popupHtml.find(".pop-up").append($btnContent);
        }
        $popupHtml.find("#dialogContent").html(self.opt.content);
        return $popupHtml;
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
        if(this.$mask){
            this.$mask.css({height:$(document).height()});
        }
    };
    $.dialogObj = {
        defaultOptions:{
            width:600,
            height:300,
            content:'',
            zIndex:1000,
            title:'',
            mask:true,
            dialogClass:'',
            maskColor:'#000',
            maskOpacity:'.15',
            closeButtom:false
        },
        getzIndex:function(){
            var num = $(".popup_container").size();
            return $.dialogObj.defaultOptions.zIndex + num*5;
        },
        dialog:function(opt){
            var options = $.extend({},$.dialogObj.defaultOptions,opt);
            options.zIndex = $.dialogObj.getzIndex();
            var obj = new dialogClass(options);
            obj.show();
            return obj;
        },
        popup:function(){
            var opt = {
                width:320,
                height:170,
                button:{'ok':{'name':'确定',"className":"btn-s"}},
                dialogClass:this.type
            };
            if(arguments[0]){
                opt.content = arguments[0];
            }
            switch(this.type){
                case "error": // continue
                case "prompt":
                case "warn":
                case "alert":
                    if(arguments[1]){
                        if(typeof arguments[1] == 'function'){
                            opt.button.ok.fn= arguments[1];
                        }else if(typeof arguments[1] == 'object'){
                             $.extend(opt, arguments[1]);
                        }
                    }
                    if(arguments[2] && typeof arguments[2] == 'object'){
                         $.extend(opt, arguments[2]);
                    }
                    break;
                case "confirm":
                    opt.button.cancel = {'name':'取消',"className":"btn-c"};
                    if(arguments[1]){
                        if(typeof arguments[1] == 'function'){
                            opt.button.ok.fn= arguments[1];
                        }else if(typeof arguments[1] == 'object'){
                             $.extend(opt, arguments[1]);
                        }
                    }
                    if(arguments[2]){
                        if(typeof arguments[2] == 'function'){
                            opt.button.cancel.fn= arguments[2];
                        }else if(typeof arguments[2] == 'object'){
                             $.extend(opt, arguments[2]);
                        }
                    }
                    if(arguments[3] && typeof arguments[3] == 'object'){
                         $.extend(opt, arguments[3]);
                    }
                    break;
                case "mask":
                    opt.width = 300;
                    opt.height = 108;
                    opt.button = {};
                    opt.mask = true;
                    opt.content = '<p class="loading">'+opt.content+'</p>';
                    break;
                default:
                    break;
            }
            var options = $.extend({},$.dialogObj.defaultOptions,opt);
            options.zIndex = $.dialogObj.getzIndex();
            var obj = new dialogClass(options);
            obj.show();
            return obj;
        }
    }
    jDialog = function(opt){
        return  $.dialogObj.dialog(opt);
    };
    jAlert = function(){
        this.type = "alert";
        $.dialogObj.popup.apply(this,arguments);//content,callback,option
    };
    jConfirm = function(){
        this.type = "confirm";
        var check = $.dialogObj.popup.apply(this,arguments);//content,okCallback,cancelCallback,option
    };
    jError = function(){
        this.type = "error";
        $.dialogObj.popup.apply(this,arguments);//content,callback,option
    };
    jPrompt = function(){
        this.type = "prompt";
        $.dialogObj.popup.apply(this,arguments);//content,callback,option
    };
    jWarn = function(){
        this.type = "warn";
        $.dialogObj.popup.apply(this,arguments);//content,callback,option
    };
    setMask = function(){
        this.type = "mask";
        $.dialogObj.popup.apply(this,arguments);//content
    };
    removeMask = function(){
        $(".mask").prev("div").remove();
        $(".mask").remove();
    };
    $.dialogObj.timing = null;
    info = function(text,state){
        var state = state?state:"ok";
        if($(".textInfo").size() > 0){
            $.dialogObj.timing && clearTimeout($.dialogObj.timing);
            $(".textInfo").find("p").attr("class","");
            $(".textInfo").find("p").addClass(state).html(text);
            textInfo = $(".textInfo");
        }else{
            var textInfo;
            if(state=="ok"){
                textInfo = $('<div class="textInfo"><div class="pop-box'+" txtinfo_ok"+'"><div class="table-cell"><p class="'+state+'">'+ text +'</p></div></div></div>');
            }else{
                textInfo = $('<div class="textInfo"><div class="pop-box'+" txtinfo_error"+'"><div class="table-cell"><p class="'+state+'">'+ text +'</p></div></div></div>');
            }
            $("body").append(textInfo);
        }
        if(state != "loading"){
            $.dialogObj.timing = setTimeout(function(){
                textInfo.animate({
                    opacity:0
                },300,function(){
                    textInfo.remove();
                });
            },5000);
        }
        textInfo.click(function(){
            $(this).remove();
        })
    };
})(jQuery);