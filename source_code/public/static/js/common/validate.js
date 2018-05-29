// isSaving = false;
(function($){
    $.fn.validate = function(){
        var _form = $(this);
        var tmpObj = {
                objs:null,
                validate:function(){
                    tmpObj.objs = _form.find(".required");
                    var error ='<span class="error"></span>';
                    var result = true;

                    //判断最大长度
                    _form.find('input[max-length], textarea[max-length]').each(function(){
                        var panel = $(this).parent();

                        if(strlen_zh($(this).val()) > parseInt($(this).attr('max-length'), 10)){
                            result = false;
                            if(panel.find(".error").size() <= 0){
                                panel.append($(error).text('您最多可输入' + $(this).attr('max-length') + '个字'));
                            }
                        }
                        else{
                            panel.find(".error").remove();
                        }
                    });

                    tmpObj.objs.each(function(){
                        var obj = $(this);
                        var type = obj.attr("type");
                        var panel = obj.parents("li").children(".fc");
                        var label = panel.prev().text().replace("：","");
                        var $error = $(error);
                        switch(type){
                            case 'select-one':
                                if(!obj.val()){
                                    result = false;
                                    if(panel.find(".error").size() <= 0){
                                        panel.append($error.text("请选择"+label));
                                    }
                                }
                            break;
                            case 'radio':
                                var _name = obj.attr("name");
                                if($("input[name='"+_name+"']:checked").size() <= 0){
                                    result = false;
                                    if(panel.find(".error").size() <= 0){
                                        panel.append($error.text("请选择"+label));
                                    }
                                }
                            break;
                            default:
                                if(!obj.val()){
                                    result = false;
                                    var errorText = "请填写";
                                    if(obj.hasClass("isSelect")){
                                        errorText = "请选择";
                                    }
                                    if(panel.find(".error").size() <= 0){
                                        panel.append($error.text(errorText+label));
                                    }
                                }
                            break;
                        }
                    });
                    if($(".error").size() > 0){
                        var _offset = $(".error").eq(0).offset();
                        window.scrollTo(0,_offset.top-20);
                    }
                    tmpObj.bindEvent();
                    return result;
                },
                bindEvent:function(){
                    tmpObj.objs.change(function(){
                        var obj = $(this);
                        var panel = obj.parents("li").children(".fc");
                        panel.find(".error").remove();
                    });
                }
        };
        _form.submit(function(){
            if(tmpObj.validate()){
               // isSaving = true;
            }else{
                return false;
            }
        });
    };
})(jQuery)