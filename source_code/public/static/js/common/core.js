String.prototype.trim=function(){
    return this.replace(/(^\s*)|(\s*$)/g, "");
};
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
        var $popupHtml = $('<div class="popup_container" style="background:#fff;border:1px solid #a6a5a5;position:absolute;top:0;left:0;z-index:'+self.opt.zIndex+';">' +
                            '<div class="pop-up">'+
                                '<div id="dialogContent" class="dialogContent">' +
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
            "height":self.opt.height,
            "zoom":"1"
        });
        if(this.opt.title){
            $popupHtml.find(".pop-up").prepend('<strong id="dialogTitle" class="dialogTitle">'+self.opt.title+'</strong>');
        }
        if(this.opt.button){
            var $btnContent = $('<div class="submit"></div>');
            $.each(self.opt.button,function(id,v){
                $('<a id="'+id+'" class="'+v.className+'" style="margin:0 5px;">'+v.name+'</a>').appendTo($btnContent).click(function(){
                    v.fn && v.fn(self.$popup,self);
                   self.close();
                });
            });
            $btnContent.css({"position":"absolute","left":"0","bottom":"10px","width":"100%","text-align":"center"});
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
            maskOpacity:'.50'
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
                width:300,
                height:150,
                button:{'ok':{'name':'确定',"className":"btn"}},
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
                    opt.button.cancel = {'name':'取消',"className":"cancel"};
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
            }
            var options = $.extend({},$.dialogObj.defaultOptions,opt);
            options.zIndex = $.dialogObj.getzIndex();
            var obj = new dialogClass(options);
            obj.show();
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
        $.dialogObj.popup.apply(this,arguments);//content,okCallback,cancelCallback,option
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
})(jQuery);
function isNumber(e){
    var _keyCode = e.keyCode;
    if(e.shiftKey){return false;}
    if ((_keyCode > 45 && _keyCode < 58)|| (_keyCode > 95&& _keyCode < 106) ||(_keyCode > 36 && _keyCode < 41) ||  (_keyCode == 8)  ||  (_keyCode == 190) ||  (_keyCode == 110)||  (_keyCode == 109)) {
        return true;
    }else{
        return false;
    }
};
$(function(){
    function initWrap(){
        var t_height = $(".header").outerHeight();
        var f_height = $(".footer").outerHeight();
        var m_height = $(window).height() - t_height - f_height;
        $(".colMain").css({"min-height":m_height + "px","height":"auto!important"});
    };
    initWrap();
    $(window).resize(function(){
        initWrap();
    });
    $("input.number").on("keydown",function(e){
         return isNumber(e);
     })
    $("input.number").on("change",function(){
         $(this).val($(this).val().replace(/\D/g,""));
     });
});