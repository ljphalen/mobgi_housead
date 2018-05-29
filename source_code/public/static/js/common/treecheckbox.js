;(function(){
    function eachlist(list,selected){
        var item = '';
        for(var k in list){
            var children = '';
            var checked = '';
            for(var j in selected){
                if(list[k].id==selected[j].id && list[k].level==selected[j].level){
                    checked = 'checked';
                }
            }
            if(list[k].item.length>0){
                children = eachlist(list[k].item,selected);
                item = item +`
                    <li>
                        <a href="javascript:;">
                        <i></i>
                        <input type="checkbox" `+checked+` data-name="`+ list[k].name +`"   value="`+ list[k].id +`" data-parentid="`+ list[k].parent_id +`" data-level="`+list[k].level+`"  />
                        <span>`+list[k].name+`</span>
                        </a>
                        `+children+`
                    </li>
            `;
            }else{
                item = item +`
                    <li>
                        <a href="javascript:;">
                        <input type="checkbox" `+checked+`  value="`+ list[k].id +`" data-parentid="`+ list[k].parent_id +`" data-level="`+list[k].level+`" />
                        <span>`+list[k].name+`</span>
                        </a>
                    </li>
                    `;
            }
        }
        return '<ul>'+ item +'</ul>' ;
    }

    //
    $.fn.treecheckbox = function(setting){
        return this.each(function(){
            var defaults = {
                data : [],
                selected :[],
                name :'',
                title : '请选择',
            };   
            var opts = $.extend(defaults,setting);
            var that = $(this);
            var t_name = $(this).html();
            var ul =eachlist(opts.data,opts.selected);
            var temp = `
            <div class="treebox-wrap">
                <div class="treebox-tree-list-wrap">
                    <div class="tree-title">`+t_name+`<span>已选中0个</span></div>
                    <input class="tree-search" type="type" value="" placeholder="搜索" />
                    <div class="tree-btns-wrap">
                        <div class="tree-btn" title="all">全选</div>
                        <div class="tree-btn" title="noall">全不选</div>
                    </div>
                    `+ ul +`
                </div>
                <div class="treebox-checked-wrap">
                    <ul class="treebox-checked-list">
                         
                    </ul>    
                </div>
                <div class="treebox-selected" data-name="`+setting.name+`" style="display:none;"></div>
            </div>
            `; 
            $(this).html(temp);
            $($(this).find(".treebox-tree-list-wrap ul").get(0)).addClass("tree-list");
            if(opts.selected.length>0){
                var treeInputs = $(this).find(".tree-list input");
                var treeInputChild = [];
                treeInputs.each(function(){
                    if($(this).prop("checked")){
                        treeInputChild.push($(this));
                    }
                });
                showCheckboxNameList(treeInputChild,$(this).find(".tree-list")); 
            }
            // 全选/全不选
            $(this).delegate('.tree-btn','click',function(){
                 var t = $(this).attr('title');
                 if(t=='all'){
                    that.find('input[type="checkbox"]').prop("checked",true);
                    var inputs = that.find(".tree-list input");
                    var inputchild = [];
                    inputs.each(function(){
                        inputchild.push($(this));
                    });
                    inputchild = getFiltrateChildren(inputchild);
                    showCheckboxNameList(inputchild,that.find(".tree-list"));
                 }if(t=='noall'){
                    that.find('input[type="checkbox"]').prop("checked",false);
                    showCheckboxNameList([],that.find(".tree-list"));
                 }
            });
            // 子列表 显示/隐藏
            $(this).delegate('.treebox-tree-list-wrap ul li i','click',function(){
                $($(this).parent().parent().find("ul").get(0)).toggle();
                if($(this).hasClass("children-active")){
                    $(this).removeClass("children-active");
                }else{
                    $(this).addClass("children-active");
                };
            });
            //  子列表全选
            $(this).delegate('ul li input[type="checkbox"]','click',function(){       
                if($(this).prop("checked")){     
                    $(this).parent().parent().find('ul input[type="checkbox"]').prop("checked",true);
                    var thisLi =  $(this).parent().parent().parent();
                    checkedCheckbox(thisLi,true); // 修改上下级 关联状态
                    showCheckedList($(this).parent().parent().parent(),opts);
                }else{ 
                    $(this).parent().parent().find('ul input[type="checkbox"]').prop("checked",false); 
                    checkedCheckbox($(this),false);  // 修改上下级 关联状态
                    showCheckedList($(this).parent().parent().parent(),opts);        
                };
            });
            // 搜索
            $(this).delegate('.tree-search','input propertychange',function(){
                var curval = $(this).val().replace(/\s/g, "");
                var list = that.find("a");
                if(curval){
                    list.each(function () {
                        if ($(this).find("span").html().search(curval) != -1) {  
                            if($(this).parent().find("ul").length>0){
                                $(this).parent().find("ul").show();
                                $($(this).find("i").get(0)).addClass("children-active");
                            };
                            eachsearch($(this).parent().parent());
                        }else {
                            $(this).parent().find("ul").hide();
                            $(this).find("i").removeClass("children-active");
                        }
                    });
                }else{
                   that.find(".tree-list ul").hide();
                   that.find("i").removeClass("children-active");
                }
                
            });  
            
            // 删除 选中
            $(this).delegate(".treebox-checked-list i","click",function(){
                var id = $(this).parent().attr("data-id");
                var level =  $(this).parent().attr("data-level");
                var li = $(this).parent().parent().find("li");
                $(this).parent().remove();
                if(li.length==1){
                    that.find(".treebox-checked-wrap").hide();
                }
                var inputs = that.find(".tree-list input");
                inputs.each(function(){
                    var ilevel = $(this).attr("data-level");
                    var iid = $(this).val();
                    if(ilevel==level && id==iid){
                        $(this).prop("checked",false);
                        checkedCheckbox($(this),false);  // 修改上下级 关联状态
                    }
                });
                var len = that.find(".treebox-checked-list li").length;
                that.find(".tree-title span").html("已选中"+len+"个");
                var treeInputs = that.find(".tree-list input");
                var treeInputChild = [];
                treeInputs.each(function(){
                    if($(this).prop("checked")){
                        treeInputChild.push($(this));
                    }
                });
                showCheckboxNameList(treeInputChild,that.find(".tree-list"))
            });

        });
    };
    //搜索
    function eachsearch(t){
        if(!t.hasClass("tree-list")){
            t.show();
            $(t.parent().find("a i").get(0)).addClass("children-active");
            var that = t.parent().parent();
            eachsearch(that);
        }
    } 
    // 遍历 checkbox 选中状态
    function checkedCheckbox(t,state){
        if(state){      
            var siblingsCheckbox =  t.find('input[type="checkbox"]');
            var isAllchecked = true;
            siblingsCheckbox.each(function(){
                if(!$(this).prop("checked")){
                    isAllchecked = false;
                }
            });
            if(isAllchecked){
                $(t.parent().find('input[type="checkbox"]').get(0)).prop("checked",true);
            }
            if(!t.hasClass('tree-list')){
               checkedCheckbox(t.parent().parent(),true);
            }
        }else{ 
            if(!t.parent().parent().parent().hasClass("tree-list")){
                var thisbox = $(t.parent().parent().parent().parent().find('input[type="checkbox"]').get(0));
                thisbox.prop("checked",false);
                checkedCheckbox(thisbox,false);
            }
        }
    }

    //  获取 筛选选中 子集 
    function getFiltrateChildren(inputs){
        var list = [];
        for(var i in inputs){
            var that = inputs[i];
            var isTrue = false;
            for(var j in inputs){
                if(inputs[j].attr("data-level") && inputs[j].attr("data-level")!='undefined'){
                    var level = parseInt(inputs[j].attr("data-level"))-1;
                    var thatLevel = parseInt(that.attr("data-level"));
                    if(that.val()==inputs[j].attr('data-parentid') && (level==thatLevel)){
                        isTrue = true;
                        break;
                    };
                }else{
                    if(that.val()==inputs[j].attr('data-parentid')){
                        isTrue = true;
                    };
                }
            };
            if(!isTrue){
                list.push(inputs[i]);
            }
        };
        return list;       
    }


    //  遍历 checkbox选中的 名字
    function showCheckedList(t,opts){
        if(t.hasClass('tree-list')){
            var boxlist = t.find("li input[type='checkbox']");        
            var list = [];
            var inputs = [];
            boxlist.each(function(){
                if($(this).prop("checked")){
                    inputs.push($(this));
                }
            });
            var children = getFiltrateChildren(inputs);
            showCheckboxNameList(children,t);
        }else{
            showCheckedList(t.parent().parent());
        }
    }

    // 显示选中的 checkbox名称
    function showCheckboxNameList(list,ul){ 
        var parentUL = ul.parent().parent().parent().parent();
        if(list.length>0){
            var li ='';
            var arr =[];
            var name = parentUL.find(".treebox-selected").attr("data-name");
            var inputs = ''
            for(var k in list){
               arr.push(list[k].val());
               inputs = inputs + '<input type="hidden" name="'+name+'" value="'+list[k].val()+'" data-level="'+list[k].attr('data-level')+'" /> '
               li = li + `<li data-id="`+ list[k].val() +`" data-parentid="`+ list[k].attr('data-parentid') +`" data-level="`+list[k].attr('data-level')+`"
               ><i></i>`+list[k].parent().find("span").html()+`</li>`;
            }
            parentUL.find(".treebox-checked-list").html(li);
            parentUL.find(".treebox-checked-wrap").show();
            parentUL.find(".treebox-selected").html(inputs);
            parentUL.find(".tree-title span").html("已选中"+list.length+"个");
        }else{
            parentUL.find(".treebox-checked-list").html("");
            parentUL.find(".treebox-checked-wrap").hide();
            parentUL.find(".treebox-selected").html("");
            parentUL.find(".tree-title span").html("已选中0个");
        }
    }

    

})();

