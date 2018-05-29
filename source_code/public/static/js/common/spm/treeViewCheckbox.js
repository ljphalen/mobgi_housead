;(function(){

    // 点击其他区域隐藏
    $.fn.autoHide = function(){
        var that = $(this);
        $(document).bind("mousedown", function (e) {
            var etarget = e.target || e.srcElement;
            $(".treebox-list").each(function (index, val) {
                var target = etarget;
                while (target != document && target != this) {
                    target = target.parentNode;
                }
                if (target == document) {
                    $(this).hide();
                    $(this).attr('data-open',false);
                }
            });
        });
        return this;
    };
    var Utils = function(element,setting){
        this.$element = element;
        this.defaults  = {
            data : {
                list:[],
                names :[],
                selected : [],
                title : [],
            },
            selected : {},
            name :'xxx',
            title : '请选择',
            request : {
                url : '',
                data : null,
                type : 'get'
            },

        };
        this.opts = $.extend(this.defaults,setting);
    };
    Utils.prototype = {
        //遍历选中项
        setSelected : function(t){
            var names = [];
            var keys = [];
            // var arr = {};
            var name = t.find("li").parent().attr("data-name");
            t.find("li").each(function(){
                var act = $(this).attr("class");
                if(act && act=="active"){
                    names.push($(this).html());
                    keys.push($(this).attr("data-key"));
                }
            });
            $(".item-list").each(function(){
                if($(this).attr("data-name")==name){
                    var li = '';
                    for(var k in names){
                        li = li + '<li data-key="'+keys[k]+'">'+names[k]+'<span>x</span> </li>';
                    }
                    $(this).html(li);
                }
            });
            t.parent().parent().find(".text-selectednum").html(keys.length);
            var selected = new Object();
            selected[name] = { 'keys': keys , 'names':names };
            selected['keys'] = keys;
            selected['names'] = names;
            return selected;
        },
        initSelected : function(utils){
            var selecteds = utils.opts.data.selected;
            var selectedResult = utils.opts.selected;
            var that = $(utils.$element);
            var arr= {}, initarr={};
            //
            if(selecteds.length>0){
                var itemBox = that.find(".item-box .treeView-text");
                for(var index in selecteds){
                    var list = selecteds[index];
                    var items = $(itemBox.get(index)).find(".treebox-list ul li");
                    var name = $(itemBox.get(index)).find(".treebox-list ul").attr("data-name");
                    var selectednum = $(itemBox.get(index)).find(".text-selectednum");
                    var names = [];
                    var keys = [];
                    for(var k in list){
                        var key = list[k];
                        items.each(function(){
                            if(key==$(this).attr("data-key")){
                                $(this).addClass("active");
                                names.push($(this).html());
                                keys.push(key);
                                return;
                            }
                        });
                    }
                    selectednum.html(keys.length);
                    $(".item-list").each(function(){
                        if($(this).attr("data-name")==name){
                            var li = '';
                            for(var k in names){
                                li = li + '<li data-key="'+keys[k]+'">'+names[k]+'<span>x</span> </li>';
                            }
                            $(this).html(li);
                        }
                    });
                    var selected = new Object();
                    //selected[name] = { 'keys': keys , 'names':names };
                    selected['keys'] = keys;
                    selected['names'] = names;
                    arr[name] = selected;
                }
            }

            //
            var selectedResultList = [];
            if(selectedResult){
                var li = '';
                for(var r_key in selectedResult){
                    li = li + '<li data-id="'+ r_key +'"><span>x</span>'+ selectedResult[r_key] +'</li>';
                    selectedResultList.push(r_key);
                }
                that.find(".treeView-right ul").html(li);
            }
            initarr['selected'] = arr;
            initarr['selectedList'] = selectedResultList;
            that.find("input[name='"+utils.opts.name+"']").val(selectedResultList);
            return initarr;
        }

    };

    $.fn.treeViewCheckbox = function(setting){
        //var treeViews = $(setting.el);
        return this.each(function(index,el){
            var utils = new Utils(this,setting);
            var opts = utils.opts;
            var request = opts.request;
            var that = $(this);
            var li = '',body = '',body_item ='';
            var selected = {},selectedList=[];
            for(var index in opts.data.list){
                selected[opts.data.names[index]] = [];
                var list = opts.data.list[index];
                var li = ''
                for(var k in list){
                    li = li + '<li data-key="'+k+'">'+list[k]+'</li>';
                }
                body = body + `<div class="treeView-text ">
                    <input type="text" value="" placeholder="`+opts.data.title[index]+`" />
                    <i class="text-edge"></i>
                    <span class="text-selectednum">0</span>
                    <div class="treebox-list">
                    <ul data-name="`+opts.data.names[index]+`" >
                            `+ li +`
            </ul>
                <div class="nolist">无匹配记录</div>
                    <div class="tv-btns-wrap">
                    <div class="tv-btn-inline">全选</div>
                    <div class="tv-btn-inline">全不选</div>
                    </div>

                    </div>
                    </div>`;
                body_item = body_item + `<div class="item">
                    <div class="item-title">`+opts.data.title[index]+`：</div>
                <ul class="item-list" data-name="`+opts.data.names[index]+`">

                    </ul>
                    </div>`
            }
            var temp = `<div class="treeViewCheckbox">
                <div class="treeView-left">
                <div class="item-box">
                        `+ body +`
            <div class="tv-btn search_btn">查询</div>
                </div>
                <div class="treeView-selecte-main">
                        `+body_item+`
            </div>
            <div class="treeView-result">
                <div class="result-title-wrap">
                <div class="result-title">查询结果：</div>
            <input class="result-search" type="text" value="" placeholder="搜索" />
                </div>
                <ul class="result-list"></ul>
                <div class="noresult"> <p>无匹配记录</p> </div>
                <div class="tv-btn-inline">全选</div>
                <div class="tv-btn-inline">反选</div>
                </div>
                </div>
                <div class="treeView-group-wrap">
                <div class="treeView-group">
                <p class="add">添加</p>
                <p class="clear">清空</p>
                </div>
                </div>
                <div class="treeView-right">
                <p class="text">已选中：</p>
            <ul></ul>
            </div>
            <input type="hidden" name="`+opts.name+`" value=""  />
                </div>
            `;
            that.html(temp);

            // 初始化已选中
            var ininArr = utils.initSelected(utils);
            selected = ininArr.selected;
            selectedList = ininArr.selectedList;
            that.find(".treeView-right .text").html("已选中："+selectedList.length);

            // 点击下拉框
            that.find(".treeView-text").click(function(){
                var isOpen = $(this).find(".treebox-list").attr("data-open");
                if(isOpen!='true'){
                    $(this).find(".treebox-list").show().autoHide();
                    $(this).find(".treebox-list").attr("data-open",true);
                }
            })
            // 下拉框搜索
            that.delegate(".treeView-text input","input propertychange",function(){
                var value = $(this).val().replace(/\s/g, "");
                var list = $(this).parent().find(".treebox-list ul li");
                var isShow = true;
                list.each(function(){
                    if($(this).html().search(value) !=-1 ){
                        $(this).show();
                        isShow = false;
                    }else{
                        $(this).hide();
                    }
                });
                if(isShow){
                    list.parent().parent().find('.nolist').show();
                }else{
                    list.parent().parent().find('.nolist').hide();
                }
            });
            // 选中 下拉列表
            that.delegate(".treeView-text .treebox-list ul li","click",function(){
                var active = $(this).attr("class");
                var name = $(this).parent().attr("data-name");
                if(active && active=="active"){
                    $(this).removeClass("active");
                }else{
                    $(this).addClass("active");
                }
                //遍历选中项
                var arr =  utils.setSelected($(this).parent());
                //
                selected[name] = arr[name] ;
                // console.log("selected:",selected)
            });
            // 移除选中
            that.delegate('.item .item-list li span','click',function(){
                var key = $(this).parent().attr("data-key");
                var name = $(this).parent().parent().attr('data-name');
                $(this).parent().remove();
                var ul = $(".treebox-list ul");
                var ulObj = null;
                ul.each(function(){
                    if(name==$(this).attr("data-name")){
                        ulObj = $(this);
                        return ;
                    }
                });
                ulObj.find("li").each(function(){
                    if(key==$(this).attr('data-key')){
                        $(this).removeClass('active');
                        return ;
                    }
                });
                //遍历选中项
                var arr =  utils.setSelected(ulObj);
                selected[name] = arr[name] ;
            });

            // 结果 全选 ， 反选
            that.find(".treeView-result .tv-btn-inline").click(function(){
                var text = $(this).html();
                if(text=="全选"){
                    $(this).parent().find(".result-list li").each(function(){
                        if($(this).css("display")=='block'){
                            $(this).addClass("active");
                        }
                    });
                }else if(text=="反选"){
                    $(this).parent().find(".result-list li").each(function(){
                        if($(this).css('display')=="block"){
                            if($(this).attr("class")=='active'){
                                $(this).removeClass("active");
                            }else{
                                $(this).addClass("active");
                            }
                        }
                    });
                }
            });


            //  查询条件 全选 ， 全不选
            that.find(".treeView-text .tv-btn-inline").click(function(){
                var text = $(this).html();
                var name = $(this).parent().parent().find("ul").attr("data-name");
                if(text=="全选"){
                    $(this).parent().parent().find("ul li").each(function(){
                        if($(this).css("display")=="block"){
                            $(this).addClass("active");
                        }
                    });
                }else if(text=="全不选"){
                    $(this).parent().parent().find("ul li").each(function(){
                        if($(this).css("display")=="block"){
                            $(this).removeClass("active");
                        }
                    });
                }
                var arr =  utils.setSelected($(this).parent().parent().find('ul'));
                selected[name] = arr[name];
                //console.log("selected:",selected)
            });

            // 查询按钮
            that.find('.search_btn').click(function(){
                var data = request.data;
                var names = opts.data.names;
                for(var i in names) {
                    data[names[i]] = JSON.stringify(selected[names[i]]['keys']);
                }
                $.ajax({
                    type: request.type,
                    url: request.url,
                    dataType:"json",
                    data: data,
                    success: function(data) {
                        //console.log("data",data);
                        if(data.ret==0){
                            var result = data.data;
                            var view = '';
                            if(result && result.length==0){
                                that.find(".treeView-result .noresult p").html(data.msg+"，暂无数据");
                                that.find(".treeView-result").show();
                                that.find(".treeView-result .result-list").hide();
                                that.find(".treeView-result .noresult").show();
                                that.find(".treeView-result .result-search").hide();
                            }else{
                                for(var index in result){
                                    view = view +  '<li data-id="'+result[index]['id']+'">'+result[index]['name']+'</li>';
                                }
                                that.find(".treeView-result .result-list").html(view);
                                that.find(".treeView-result").show();
                                that.find(".treeView-result .result-list").show();
                                that.find(".treeView-result .noresult").hide();
                                that.find(".treeView-result .result-search").show();
                            }
                        }else{
                            that.find(".treeView-result .noresult p").html(data.msg);
                            that.find(".treeView-result").show();
                            that.find(".treeView-result .result-list").hide();
                            that.find(".treeView-result .noresult").show();
                            that.find(".treeView-result .result-search").hide();
                        }
                    },
                    error: function(xml,msg,error){
                        that.find(".treeView-result .noresult p").html("查询失败");
                        that.find(".treeView-result").show();
                        that.find(".treeView-result .result-list").hide();
                        that.find(".treeView-result .noresult").show();
                        that.find(".treeView-result .result-search").hide();
                    }
                });
            });

            // 点击添加
            that.find(".treeView-group .add").click(function(){
                var list = that.find(".treeView-result .result-list li");
                var keys = [];
                var names = [];
                var li = '';
                // console.log(selectedList);
                list.each(function(){
                    var active = $(this).attr("class");
                    if(active && active=="active"){
                        var key = $(this).attr("data-id");
                        var name = $(this).html();
                        keys.push(key);
                        names.push(name);
                        if(selectedList.length>0){
                            var isSelected = false;
                            for(var index in selectedList){
                                if(key==selectedList[index]){
                                    isSelected = true;
                                    return ;
                                }
                            }
                            if(!isSelected){
                                selectedList.push(key);
                                li = li + '<li data-id="'+ key +'"><span>x</span>'+name+'</li>';
                            }
                        }else{
                            selectedList.push(key);
                            li = li + '<li data-id="'+ key +'"><span>x</span>'+name+'</li>';
                        }
                    }
                });
                that.find(".treeView-right ul").append(li);
                that.find("input[name='"+utils.opts.name+"']").val(selectedList);
                that.find(".treeView-right .text").html("已选中："+selectedList.length);
            });

            // 删除 -- 添加结果
            that.find(".treeView-right ul").delegate("li span",'click',function(){
                var id = $(this).parent().attr('data-id');
                $(this).parent().remove();
                for(var i in selectedList){
                    if(id==selectedList[i]){
                        selectedList.splice(i,1);
                        break;
                    }
                }
                that.find("input[name='"+utils.opts.name+"']").val(selectedList);
                that.find(".treeView-right .text").html("已选中："+selectedList.length);
            });
            // 清空 -- 添加结果
            that.find(".treeView-group .clear").click(function(){
                selectedList = [];
                that.find("input[name='"+utils.opts.name+"']").val(selectedList);
                that.find(".treeView-right .text").html("已选中："+selectedList.length);
                that.find(".treeView-right ul").html("");
            });

            //  查询结果 --- 选中效果
            that.find(".treeView-result").delegate(".result-list li","click",function(){
                var active = $(this).attr("class");
                if(active && active=="active"){
                    $(this).removeClass("active");
                }else{
                    $(this).addClass("active");
                }
            });
            // 结果搜索
            that.find(".treeView-result").delegate(".result-search","input propertychange",function(){
                var text = $(this).val().replace(/\s/g, "");;
                var isShow = true;
                that.find(".treeView-result").find(".result-list li").each(function(){
                    if($(this).html().search(text) !=-1){
                        $(this).show();
                        isShow = false;
                    }else{
                        $(this).hide();
                    }
                });
                if(isShow){
                    that.find(".treeView-result").find(".result-list").hide();
                    that.find(".treeView-result").find(".noresult").show();
                }else{
                    that.find(".treeView-result").find(".result-list").show();
                    that.find(".treeView-result").find(".noresult").hide();
                }
            });
        });
    };

})();

