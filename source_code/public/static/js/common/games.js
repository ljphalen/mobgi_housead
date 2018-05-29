/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
function showCreateAppKeyDiv() {
	html = '<li onclick="createAppkey()">点击自动生成一个appkey</li>';
	$("#gamelist").html(html);
	$("#gamelist").show();

}
function createAppkey() {
	ajaxGET("/admin/Baseinfo_App/createAppKey", "", function(data) {
		if (data.success) {
			$("#app_key").val(data.data);
			$("#gamelist").hide();
		} else {
			alert("生成APPKEY失败")
		}
	});
}

function createPosKey() {
	var appkey = $("input[name='app_key']").val();
	initposbox()
	ajaxGET("/admin/Baseinfo_App/createPosKey", "app_key=" + appkey, function(ret) {
		if (ret.success) {
			$("input[name='dever_pos_key_tmp']").val(ret.data);
			$("#pos_key_text").html(ret.data)
		} else {
			alert("生成广告位KEY失败,请重试")
		}
	});
	$('.pop_open').css("marginTop", - $('.pop_open')[0].offsetHeight/2+'px');
}

function initposbox() {//点击添加广告初始化
    $("input[name='pos_id_tmp']").val("")
    $("input[name='dever_pos_key_tmp']").val("")
    $("input[name='dever_pos_name_tmp']").val("")
    $("input[name='pos_key_type_tmp']").val("")
}

function add_pos_key() {
    var pos_id = $("input[name='pos_id_tmp']").val()
    var pos_key = $("input[name='dever_pos_key_tmp']").val()
    var pos_name = $("input[name='dever_pos_name_tmp']").val()
    var pos_key_type = $("select[name='pos_key_type_tmp']").val()
    var pos_key_type_text = $("#mki_select_2").find("option:selected").text();
    var size = ''
    //交叉推广添加广告子类型
    if(pos_key_type == 'CUSTOME_INTERGRATION'){
        ad_sub_type=$('input[name=custom_sub_type]:checked').val()
        if(typeof ad_sub_type == 'undefined'){
            alert('请选择交叉推广广告子类型')
            return false;
        }
        pos_key_type_text +=" " + $('input[name=custom_sub_type]:checked').attr('asSubTypeStr')
    }
    //原生广告添加广告子类型
    else if(pos_key_type == 'ENBED_INTERGRATION'){
        ad_sub_type=$('input[name=enbed_sub_type]:checked').val()
        if(typeof ad_sub_type == 'undefined'){
            alert('请选择原生广告子类型')
            return false;
        }
        pos_key_type_text +=" " + $('input[name=enbed_sub_type]:checked').attr('asSubTypeStr')
        size = $("#enbed_size_tmp").val()
        pos_key_type_text +=" " +  $("#enbed_size_tmp").find("option:selected").text();
    }else{
        ad_sub_type ='';
    }
    var pos_desc = $(".add_pos_desc textarea").val();
    var trid = pos_key
    var html = '<tr id="' + trid + '">';
    if (pos_key == "") {
        alert("广告位KEY不能为空");
        $("input[name='dever_pos_key_tmp']").focus();
        return false;
    }
    if (pos_name == "") {
        alert("广告位名称不能为空");
        $("input[name='dever_pos_name_tmp']").focus();
        return false;
    }
    flagName = false;
    $("input[name='dever_pos_name[]']").each(function(){
    	$tmpPosId = $(this).siblings("input[name='pos_id[]']").val();
    	if( $(this).val() == pos_name && pos_id == '' ){
    		flagName = true;
    		return false;
    	}
    })
    if(flagName){
    	alert("广告名称已经存在!!");
    	return false;
    }
    
    if (pos_key_type == "") {
        alert("请选择广告形式");
        $("select[name='pos_key_type_tmp']").focus();
        return false;
    }
    
    id = $("input[name='pos_id_tmp']").val();
    html += '<td>' + pos_key + '<input type="hidden" name="dever_pos_key[]" value="' + pos_key + '"/><input type="hidden" name="ad_sub_type[]" value="'+ad_sub_type+'"/><input type="hidden" name="pos_id[]" value="' + pos_id + '"/><input type="hidden" name="dever_pos_name[]" value="' + pos_name + '"/><input type="hidden" name="pos_key_type[]" value="' + pos_key_type + '"/><input type="hidden" name="pos_desc[]" value="' + pos_desc + '"/><input type="hidden" name="size[]" value="' + size + '"/></td>';
    html += '<td>' + pos_name + '</td>';
    html += '<td>' + pos_key_type_text + '</td>';
    html += '<td>' + pos_desc + '</td>';
    var pid = $(".posbox_sure").attr("pid");
    if (pid == 1) html += '<td> <p class="onfbk"><input type="hidden" name="state[]" class="" value="1"><a class="aon setPosStateOn cur">ON</a><a class="aoff setPosStateOff ">OFF</a></p></td>';
    if(pid && id == ''){
        html +='<td><input type="text" name="rate[]" value="1"></td>';
        html +='<td><input type="text" name="limit_num[]" value="0"></td>';
    }
    html += '<td><a onclick="update_pos_key(\'' + trid + '\')">编辑</a>  <a onclick="del_pos_key(this)">删除</a></td>';
    html += "</tr>"
  
    if (id != "") {//修改
        set_tr_pos_key(id, pos_key_type);
    } else {//新增
        $("#pos_tb_box").append(html);
    }
   // createposkey()
    $("input[name='dever_pos_name_tmp']").val("");
    $("input[name='pos_id_tmp']").val("");
    $("select[name='pos_key_type_tmp']").val("");
    $(".add_pos_desc textarea").val("");
    $("input[name='dever_pos_key_tmp']").val("");
    $("#pos_key_text").text("");
    $('#pos_box').hide();
    $("#custom_sub_type_div").hide();
}

function del_pos_key(thiss) {
    if (confirm("你确定删除么?该操作不可恢复")) {
        var pos_key = $(thiss).parent().parent().find("td:eq(0)").text();
        $(thiss).parent().parent().remove();
   /*     ajaxGET("/apps/del_pos_key", "pos_key=" + pos_key, function(data) {
            if (data.result == 0) {
                $(thiss).parent().parent().remove();
                $("input[name='pos_key']").val(data.msg);
                $("#pos_key_text").html(data.msg)
            } else {
                alert("生成广告位KEY失败,请重试")
            }
        });*/
    }

}

function update_pos_key(trid) {
    var trobj = $("#" + trid)
    var pos_id = trobj.find("input[name='pos_id[]']").val()
    var pos_key = trobj.find("input[name='dever_pos_key[]']").val()
    var pos_name = trobj.find("input[name='dever_pos_name[]']").val()
    var pos_key_type = trobj.find("input[name='pos_key_type[]']").val()
    var pos_key_type_text = trobj.find("select[name='pos_key_type[]']").find("option:selected").text();
    var pos_desc = trobj.find("input[name='pos_desc[]']").val()
    var pos_key_type = trobj.find("input[name='pos_key_type[]']").val()
    var ad_sub_type = trobj.find("input[name='ad_sub_type[]']").val()
    var size = trobj.find("input[name='size[]']").val()
    $('#pos_box').show();
    if(pos_key_type == 'CUSTOME_INTERGRATION'){
        $("#custom_sub_type_div").show();
        $("#enbed_sub_type_div").hide();
        $("#enbed_size_div").hide();
        $("input[name='custom_sub_type'][value="+ad_sub_type+"]").attr("checked",true);
    }else if(pos_key_type == 'ENBED_INTERGRATION'){
        $("#custom_sub_type_div").hide();
        $("#enbed_sub_type_div").show();
        $("#enbed_size_div").show();
        $("input[name='enbed_sub_type'][value="+ad_sub_type+"]").attr("checked",true);
        $("#enbed_size_tmp").val(size);
    }else{
        $("#custom_sub_type_div").hide();
        $("#enbed_sub_type_div").hide();
        $("#enbed_size_div").hide();
    }
/*   //交叉推广添加广告子类型
   if(pos_key_type == 'CUSTOME_INTERGRATION'){
       ad_sub_type=$('input[name=custom_sub_type]:checked').val()
       if(typeof ad_sub_type == 'undefined'){
           alert('请选择交叉推广广告子类型')
           return false;
       }
       pos_key_type_text +=" " + $('input[name=custom_sub_type]:checked').attr('asSubTypeStr')
   }else{
       ad_sub_type ='';
   }*/
    
    if (pos_id == "") {
        $("input[name='pos_id_tmp']").val(trid)
    } else {
        $("input[name='pos_id_tmp']").val(pos_id)
    }
    $("input[name='dever_pos_key_tmp']").val(pos_key)
    $("#pos_key_text").text(pos_key)
    $("input[name='dever_pos_name_tmp']").val(pos_name)
    $("select[name='pos_key_type_tmp']").val(pos_key_type)
    $(".add_pos_desc textarea").val(pos_desc)
}

function set_tr_pos_key(trid, pos_key_type) {
    var trobj = $("#" + trid)
    pos_key_type_text = $("#mki_select_2").find("option:selected").text();
    size=''
    if(pos_key_type == 'CUSTOME_INTERGRATION'){
        ad_sub_type=$('input[name=custom_sub_type]:checked').val()
        pos_key_type_text +=" " + $('input[name=custom_sub_type]:checked').attr('asSubTypeStr')
    }else if (pos_key_type == 'ENBED_INTERGRATION'){
        ad_sub_type=$('input[name=enbed_sub_type]:checked').val()
        size = $("#enbed_size_tmp").val()
        pos_key_type_text +=" " + $('input[name=enbed_sub_type]:checked').attr('asSubTypeStr')
        pos_key_type_text +=" " +  $("#enbed_size_tmp").find("option:selected").text();
    }else{
        ad_sub_type = 0
    }
    trobj.find("td:eq(1)").text($("input[name='dever_pos_name_tmp']").val());
    trobj.find("input[name='dever_pos_name[]']").val($("input[name='dever_pos_name_tmp']").val());
    trobj.find("td:eq(2)").text(pos_key_type_text);
    trobj.find("input[name='pos_key_type[]']").val($("select[name='pos_key_type_tmp']").val());
    trobj.find("td:eq(3)").text($(".add_pos_desc textarea").val());
    trobj.find("input[name='pos_desc[]']").val($(".add_pos_desc textarea").val());
    trobj.find("input[name='ad_sub_type[]']").val(ad_sub_type);
    trobj.find("input[name='size[]']").val(size);
}
