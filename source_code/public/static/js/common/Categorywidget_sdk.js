/**
 * pid 格式转换为 children 格式
 * @param  {[type]} rows [description]
 * @return {[type]}      [description]
 */
function convert(rows){  
    function exists(rows, parentId){  
        for(var i=0; i<rows.length; i++){  
            if (rows[i].id == parentId) return true;  
        }  
        return false;  
    }  
      
    var nodes = [];  
    // 得到顶层节点
    for(var i=0; i<rows.length; i++){  
        var row = rows[i];  
        if (!exists(rows, row.parentId)){  
            nodes.push({  
                id:row.id,  
                text:row.name  
            });  
        }  
    }  
      
    var toDo = [];  
    for(var i=0; i<nodes.length; i++){  
        toDo.push(nodes[i]);  
    }  
    while(toDo.length){  
        var node = toDo.shift();    // 父节点 
        // 得到子节点 
        for(var i=0; i<rows.length; i++){  
            var row = rows[i];  
            if (row.parentId == node.id){  
                var child = {id:row.id,text:row.name};  
                if (node.children){  
                    node.children.push(child);  
                } else {  
                    node.children = [child];  
                }  
                toDo.push(child);  
            }  
        }  
    }  

    return nodes;  
}
/**
 * 渠道搜索组件
 * @type {String}
 */
function  Categorywidget_sdk(Configs){
  var configObj = Configs;
  //interfaceName,boxId,ComboId,ComboName,ComboSaveId,ComboSaveName
  var interfaceName = configObj.interfaceName
  var boxId = configObj.boxId;
  var ComboId = configObj.ComboId;
  var ComboName = configObj.ComboName; 
  var ComboSaveId = configObj.ComboSaveId;
  var ComboSaveName = configObj.ComboSaveName;
  var DataConfig = configObj.DataConfig;
  var DataSourceConfig = configObj.DataSourceConfig;
  var myself = this; //自身引用用于回调
  this.selfName = 'Categorywidget_sdk'; //button combo id的前缀，可以防止id冲突
  this.box=null; //盒子 
  this.interfaceName =interfaceName; //引用的变量名称
  this.ComboId=ComboId;
  this.ComboSaveId=ComboSaveId;
  this.ComboName = ComboName;  //表单提交的名字
  this.ComboSaveName =ComboSaveName; //表单提交的名字
  this.ComboFilterInputClear=false; //缓存
  this.ComboSaveFilterInputClear = false;//缓存
  this.ChannelsSaveDataCache = new Array(); //缓存
  this.ChannelsWrodCache = null;//关键字缓存，combo未发生变化的时
  this.DataConfig = DataConfig;
  this.ClassComboDataCache = new Array();
  this.DataSourceConfig = DataSourceConfig;
  this.DataType =0;
  if (DataSourceConfig.DataType == 'channels'||DataSourceConfig.DataType == 'games'){
      this.DataType =1;
      if (DataSourceConfig.DataType == 'games'){
          this.DataType =2;
      }
  }
  
  /**
   * 检查参数，开始执行
   */
  this.Init=function(){
      if (!boxId||!ComboId||!ComboSaveId){
        alert('参数不正确');
        return;
      }
      myself.box = $("#"+boxId);
      if (!myself||myself.box==null){
        alert('容器不存在');
        return ; 
      }
      myself.CreateLayout(); //建立布局
     
      myself.CreateLeftCombo(); //创建左侧 combo  
      myself.readComboData(function(data){
          myself.CreateClassCombo(data); //创建 combo tree
		  myself.loadChannelsData();
       })
      
      myself.CreateRightCombo(); //创建右侧 combo
      myself.CreateMoveButton(); //创建按钮
  }
  this.readComboData=function(callBack){
	 var CategorySelData = '';
	 if('CategorySelData' in myself.DataSourceConfig) CategorySelData = myself.DataSourceConfig.CategorySelData;
     $.ajax({
          type : "get",
          async:true ,
		  timeout:10000,
          url : myself.DataSourceConfig.CategoryListCombo,
          data : {type:myself.DataType , selids:CategorySelData},
          dataType : "jsonp",
          jsonp: "callbackparam",//服务端用于接收callback调用的function名的参数
          jsonpCallback:myself.interfaceName+myself.selfName+"success_jsonpCallback",//callback的function名称
          success : function(data){
              if (data&&data!=null&&data.length>0){
                  myself.ClassComboDataCache = data;
                  if (callBack){
                    callBack(data);
                  }
              }else{
                  myself.ClassComboDataCache = false;                
              }
          },
          error:function(){
              //alert('fail(1)');
          }
      });
  }
  /**
   * 渲染右侧数据
   * @param {[type]} Datas [description]
   */
  this.RenderData=function(Datas){
    try{
      var Data = eval(Datas);
      if (Data)
      {
        for (var i=0;i<Data.length;i++){
          myself.WriteDataToSaveCombo(Data[i].value,Data[i].text)
        }
		//myself.loadChannelsData();
      }
    }catch(err){
      alert('Render数据格式错误');
    }
    
  }
  /**
   * 建立容器布局
   */
  this.CreateLayout=function(){
    var Table = '<table width="100%" class="channelsSearch_box_sys_table"><tr><td id="SysChannelsSearcher'+myself.interfaceName+'Bt1" valign="top" class="channelsSearch_box_sys_comboTreeTable"></td><td id="SysChannelsSearcher'+myself.interfaceName+'Bt2" valign="top"></td><td id="SysChannelsSearcher'+myself.interfaceName+'Bt3"></td><td id="SysChannelsSearcher'+myself.interfaceName+'Bt4"></td></tr></table>';
      myself.box.html(Table);
  }
  /**
   * 创建 ，添加、删除的按钮
   */
  this.CreateMoveButton=function(){
    $("#SysChannelsSearcher"+myself.interfaceName+"Bt3").html('<button id="'+myself.selfName+myself.interfaceName+'MoveRight" type="button">添加>></button><br/><button id="'+myself.selfName+myself.interfaceName+'MoveLeft" type="button">删除<<</button>');
    $("#"+myself.selfName+myself.interfaceName+'MoveRight').bind('click',myself.Channelsright);
    $("#"+myself.selfName+myself.interfaceName+'MoveLeft').bind('click',myself.Channelsleft );
  }
  /**
   * 创建分类选择菜单
   */
  this.CreateClassCombo=function(data){
    var setting = {
      check: {
        enable: true
      },
      callback: {
        onCheck: function(){
			myself.loadChannelsData();
		}
      },
      data: {
        simpleData: {
          enable: true
        }
      }
    };
	var newdata = [];
	newdata[newdata.length] = {'id':-999,'name':'全选','open':true};
    for (var i=0;i<data.length;i++){
	  if(! ('parentId' in data[i]) ) {
		  data[i].parentId = -999;
	  }
      data[i].pId = data[i].parentId;
	  newdata[newdata.length] = data[i];
    }
    $("#SysChannelsSearcher"+myself.interfaceName+"Bt1").html(' <div style="width:200px;height:180px;"><ul id="'+myself.interfaceName+myself.selfName+'treeBox" class="ztree" style="height:165px;margin-top:0px;"></ul></div>')
    myself.TreeCombo = $.fn.zTree.init($("#"+myself.interfaceName+myself.selfName+"treeBox"), setting, newdata);
    return;
/*    $("#SysChannelsSearcher"+myself.interfaceName+"Bt1").html('<select id="'+myself.selfName+myself.interfaceName+'ComboTree" name="pid" class="easyui-combotree" style="width:200px;"  data-options="hasDownArrow:true" multiple ></select> ');
    $('#'+myself.selfName+myself.interfaceName+'ComboTree').combotree({
       loadFilter: function(rows){
        return convert(rows);  
      },
      panelHeight:155,
      onChange:function(){
        myself.loadChannelsData();
      },
      onHidePanel:function(){
        //$('#'+myself.selfName+myself.interfaceName+'ComboTree').combotree('showPanel');
        return false;
      }
    });
    $('#'+myself.selfName+myself.interfaceName+'ComboTree').combotree('loadData',data)
    //$('#'+myself.selfName+myself.interfaceName+'ComboTree').combotree('showPanel');*/
  }
  this.ShowCategoryPanel=function(){
      $('#'+myself.selfName+myself.interfaceName+'ComboTree').combotree('showPanel');
  }
  /**
   * 响应右移动事件
   */
  this.Channelsright=function(){
    if($("#"+myself.ComboId+" option:selected").length>0){
      $("#"+myself.ComboId+" option:selected").each(function(){
    	  var flag = 1;
    /*	  var leftComBoValue = $(this).val();
    	  $("#"+myself.ComboSaveId+" option").each(function(){
    		  rightComBoValue = $(this).val();
    		  if(leftComBoValue == rightComBoValue){
    			  flag = 0;
    		  }
    	  });*/
    	  if(flag == 1){
        	  $("#"+myself.ComboSaveId).append("<option selected value='"+$(this).val()+"'>"+$(this).text()+"</option");
              myself.ChannelsSaveDataCache[myself.ChannelsSaveDataCache.length]= $(this).val();
    	  }
          $(this).remove();
        return;
      });
    }else{
      alert("请选择要添加的渠道！");
    }
  }
  /**
   * 响应左移动事件
   */
  this.Channelsleft=function(){
    if($("#"+myself.ComboSaveId+" option:selected").length>0){
      $("#"+myself.ComboSaveId+" option:selected").each(function(){
        $("#"+myself.ComboId).append("<option value='"+$(this).val()+"'>"+$(this).text()+"</option");
        	myself.ChannelsSaveDataCache.splice(jQuery.inArray($(this).val(), myself.ChannelsSaveDataCache),1); 
        $(this).remove();
        return;
      });  
    }else{
      alert("请选择要删除的渠道！");
    }
  }
  /**
   * 读取渠道数据
   * @return {[type]} [description]
   */
  this.loadChannelsData=function(){
    var nodes = myself.TreeCombo.getCheckedNodes(true);
    var ids = new Array();
    if (nodes){
      for(var i=0;i<nodes.length;i++)
      {
        ids[ids.length]= nodes[i].id;
      }
    }
    var Values = ids;
    //var Values = $('#'+myself.selfName+myself.interfaceName+'ComboTree').combotree('getValues');
      $.ajax({
          type : "get",
          async:true,
		  timeout:10000,
          url : myself.DataSourceConfig.CategoryDataUrl,
          data:{classIds:Values.toString()},
          dataType : "jsonp",
          jsonp: "callbackparam",//服务端用于接收callback调用的function名的参数
          jsonpCallback:"success_jsonpCallback",//callback的function名称
          success : function(data){
              if (data&&data!=null&&data.length>0){
                  myself.WriteDataToCombo(data);
              }else{
                  myself.ClearComboData();
              }
          },
          error:function(){
              //alert('fail(2)');
          }
      });
  }
  /**
   * 写入数据到左侧 combo
   * @param {[type]} datas [description]
   */
  this.WriteDataToCombo=function(datas){
    myself.ClearComboData();
    var valueKey =myself.DataConfig.value;
    var textKey = myself.DataConfig.text;
	  $("#"+myself.ComboSaveId+" option").each(function(){
		  rightComBoValue = $(this).val();
		  if(rightComBoValue){
			  myself.ChannelsSaveDataCache[myself.ChannelsSaveDataCache.length]= rightComBoValue+'';
		  }
	  });
    for (var i=0; i<datas.length;i++) {
      eval("var value = datas[i]."+DataSourceConfig.DataType+"."+valueKey+"+'';")
      eval("var text = datas[i]."+DataSourceConfig.DataType+"."+textKey);
      
      if (jQuery.inArray(value, myself.ChannelsSaveDataCache) == -1){
        $("#"+myself.ComboId).append("<option value='"+value+"'>"+text+"</option");  
      }
    }
  }
  /**
   * 向右侧的combo 写数据
   * @param {[type]} value [description]
   * @param {[type]} text  [description]
   */
  this.WriteDataToSaveCombo=function(value,text){
      if (jQuery.inArray(value, myself.ChannelsSaveDataCache) == -1){    	
        $("#"+myself.ComboSaveId).append("<option value='"+value+"'>"+text+"</option");  
        myself.ChannelsSaveDataCache[myself.ChannelsSaveDataCache.length]= value+'';
      }
  }
  /**
   * 搜索过滤器 获得焦点事件
   * @param {[type]} obj  [description]
   * @param {[type]} type [description]
   */
  this.Filterfocus =  function(obj,type) {
      var typeStatus =eval("myself."+type);
      if (typeStatus ==false){
        typeStatus = true;
        obj.currentTarget.value= '';
        obj.currentTarget.style.color="#000000";
      }
  }
  this.ClearComboData=function(){
    $("#"+myself.ComboId).html('');
  }
  this.CreateLeftCombo=function(){
    $("#SysChannelsSearcher"+myself.interfaceName+"Bt2").html('<input type="text" id="'+myself.ComboId+'FilterInput"  style="width:197px;color:#888888" value="<-- 搜索 -->" name=""><br><select id="'+myself.ComboId+'"  style="text-align:left;width:200px;height:150px;" multiple="multiple" name="'+myself.ComboName+'"></select>');
    $("#"+myself.ComboId).bind("dblclick", myself.Channelsright );
    $("#"+myself.ComboId+"FilterInput").bind('focus',function(event){
    	myself.Filterfocus(event,'ComboFilterInputClear');
    });
   //绑定回车事件
    $("#"+myself.ComboId+"FilterInput").bind('keyup',function(event){
    	//if(event.which == "13"){
    	if($(this).val().length >= 1){
    		$.fun.select_filter($(this).val(), myself.ComboId);
    	}    
      });
  }
  
  this.CreateRightCombo=function(){
    $("#SysChannelsSearcher"+myself.interfaceName+"Bt4").html('<input type="text" id="'+myself.ComboSaveId+'FilterInput"  style="width:197px;color:#888888" value="<-- 搜索 -->" name=""><br><select id="'+myself.ComboSaveId+'"  select="selected" style="text-align:left;width:200px;height:150px;" multiple="multiple" name="'+myself.ComboSaveName+'"> </select><label class="error" for="'+myself.ComboId+'" style="display:none" generated="true">请从左边选择至少一个项。</label>');
    $("#"+myself.ComboSaveId).bind("dblclick", myself.Channelsleft );
    $("#"+myself.ComboSaveId+"FilterInput").bind('focus',function(event){
    	myself.Filterfocus(event,'ComboSaveFilterInputClear');
    });
    //onkeyup="$.fun.select_filter(this.value,\''+myself.ComboSaveId+'\');" 
    $("#"+myself.ComboSaveId+"FilterInput").bind('keyup',function(event){
    	if($(this).val().length >= 1){
    		$.fun.select_filter($(this).val(), myself.ComboSaveId);
    	}    
      });
  }
  /**
   * 全选
   * @param {[type]} ComboType [description]
   */
  this.SelectAll=function(ComboType){
    var selectObj = null;
    if (ComboType=='SaveCombo'){
       selectObj = document.getElementById(myself.ComboSaveId);
    }
    if (ComboType=='Combo'){
       selectObj = document.getElementById(myself.ComboId);
    }
    if (selectObj==null){
      return;
    }
    for (var i=0;i<selectObj.length;i++){
      selectObj.options[i].selected=true;
    }
  }
  /**
   * 反选
   * @param {[type]} ComboType [description]
   */
  this.UnSelectAll=function(ComboType){
    var selectObj = null;
    if (ComboType=='SaveCombo'){
       selectObj = document.getElementById(myself.ComboSaveId);
    }
    if (ComboType=='Combo'){
       selectObj = document.getElementById(myself.ComboId);
    }
    if (selectObj==null){
      return;
    }
    for (var i=0;i<selectObj.length;i++){
      selectObj.options[i].selected=false;
    }
  }
  myself.Init();
}
/**
 * 搜索支持
 */
if (!$.fun){
  $.fun = new function() {
  this.data = [];
  this.json = function(data) {
    if( data.substr(0,1)!="{" && data.substr(0,1)!="[" || data.substr(data.length-1,1)!="}" && data.substr(data.length-1,1)!="]" ){
      var obj_y={"isnull":"1"};
      return obj_y;
    }
    eval("var obj_x="+data);
    return obj_x;
  }
  this.add_option = function(msgobj,msgname,msgval){
    if(document.all){
      msgobj.add(new Option(msgname,msgval));
    }else{
      msgobj.add(new Option(msgname,msgval),null);
    }
  }
  this.select_filter = function(val , id) {
    var obj = $("#"+id)[0].options;
    var i;
    var arr = [];
    var is_first = false;
    if( !(id in this.data) ) {
      if(val == '') return;
      this.data[id] = [];
      is_first = true;
    }
    if(val == '') {
      var arr_x = [];
      for( i = 0 ; i < obj.length ; i++ ) {
        arr_x[arr_x.length] = obj[i].value;
      }
      for( i = 0 ; i < this.data[id].length ; i++ ) {
        if(arr_x.indexOf(this.data[id][i].value) >= 0) arr[arr.length] = this.data[id][i];
      }
      obj.length = 0;
    } else {
      for( i = 0 ; i < obj.length ; i++ ) {
        if(is_first) this.data[id][this.data[id].length] = {'value':obj[i].value , "html":obj[i].innerHTML};
        if(obj[i].innerHTML.length < val.length || obj[i].innerHTML.substr(0,val.length).toLocaleLowerCase() != val.toLocaleLowerCase()) {
          arr[arr.length] = {'value':obj[i].value , "html":obj[i].innerHTML};
          obj.remove(i);
          i--;
        }
      }
      //模糊匹配
      for( i = 0 ; i < arr.length ; i++ ) {
        if(arr[i].html.length >= val.length && arr[i].html.toLocaleLowerCase().indexOf(val.toLocaleLowerCase()) > 0) {
          $.fun.add_option(document.getElementById(id) , arr[i].html , arr[i].value);
          arr.splice(i,1);
          i--;
        }
      }
    }
    for( i = 0 ; i < arr.length ; i++ ) {
      $.fun.add_option(document.getElementById(id) , arr[i].html , arr[i].value);
    }
  }

}
}