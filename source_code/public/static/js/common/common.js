
$(function() {

    // 删除按钮 全局搞定
    $(".del").click(function() {
        var opt = $(this);
        var url = opt.attr("href");
        opt.attr("href", "#");
        jConfirm("是否确定要删除？", function() {
            window.location.href = url;
        }, function() {
            opt.attr("href", url);
        });
    });
    // 无样式删除按钮 全局搞定
    $(".btndel").click(function() {
        var opt = $(this);
        var url = opt.attr("href");
        opt.attr("href", "#");
        jConfirm("是否确定要删除？", function() {
            window.location.href = url;
        }, function() {
            opt.attr("href", url);
        });
    });
    //--------------------左右选择框---------------------------
    // 双击事情
    $(".multi > option").on("dblclick", function() {
        var sel = $(this).parent('select').attr('_target');
        $(this).remove();
        $("#" + sel).append($(this).removeAttr("selected"));
    });
    // 添加，删除事件
    $(".selMove").on("click", function() {
        var fromId = $(this).attr("_from");
        var toId = $(this).attr("_to");
        var fromOpt = $("#" + fromId + " > option:selected");
        fromOpt.remove();
        $("#" + toId).append(fromOpt);
    });



});


function ajaxPOST(url, arg, callback) {
    var callback = callback || function(data) {
        if (data.result == 0) {
            alert(data.msg);
        } else {
            alert(data.msg);
        }
    };
    $.ajax({
        type: "POST",
        url: url,
        data: arg,
        dataType: "json",
        success: callback
    })
}
function ajaxGET(url, arg, callback) {
    var callback = callback || function(data) {
        if (data.result == 0) {
            alert(data.msg);
        } else {
            alert(data.msg);
        }
    };
    $.ajax({
        type: "GET",
        url: url,
        data: arg,
        dataType: "json",
        success: callback
    })

}


function CheckUrl(str) {
    var RegUrl = new RegExp();
    RegUrl.compile("^[A-Za-z]+://[A-Za-z0-9-_]+\\.[A-Za-z0-9-_%&\?\/.=]+$");
    if (!RegUrl.test(str)) {
        return false;
    }
    return true;
}
function getChannelByChannel_id(channel_id, boxid) {
    var url = '/adconfig/getChannelinfo';
    ajaxGET(url, "channel_id=" + channel_id, function(data) {
        if (data.error == 0) {
            if (data.msg.length > 0) {
                var channel = '';
                for (var i = 0; i < data.msg.length; i++) {
                    if (data.msg[i] != null) {
                        channel += data.msg[i].realname + "（" + data.msg[i].identifier + "）<br>";
                    } else {
                        channel += "。。。。。。";
                    }
                }
                $("#" + boxid).html(channel)
            }
        }
    })
}
function isNumber(s) {
    var regu = "^[0-9]+$";
    var re = new RegExp(regu);
    if (s.search(re) != -1) {
        return true;
    } else {
        return false;
    }
}
function in_array(stringToSearch, arrayToSearch) {
	for (s = 0; s < arrayToSearch.length; s++) {
		thisEntry = arrayToSearch[s].toString();
		if (thisEntry == stringToSearch) {
			return true;
		}
	}
	return false;
}
function getFullPath(obj) {    //得到图片的完整路径
    if (obj) {
        //ie
        if (window.navigator.userAgent.indexOf("MSIE") >= 1) {
            obj.select();
            return document.selection.createRange().text;
        }
        //firefox
        else if (window.navigator.userAgent.indexOf("Firefox") >= 1) {
            if (obj.files) {
                return obj.files.item(0).getAsDataURL();
            }
            return obj.value;
        }
        return obj.value;
    }
}
function CheckUrl(str) {
    var RegUrl = new RegExp();
    RegUrl.compile("^[A-Za-z]+://[A-Za-z0-9-_]+\\.[A-Za-z0-9-_%&\?\/.=]+$");//jihua.cnblogs.com
    if (!RegUrl.test(str)) {
        return false;
    }
    return true;
}
// JavaScript Document

showMsg = function(title, msg) {
	$("#msg_content").html(msg);
	return $("#msg_box_box").dialog({
				title : title,
				draggable : false,
				modal : false,
				resizable : false
			});
}

showError = function(title, msg) {
	$("#err_content").html(msg);
	return $("#error_msg_box").dialog({
				title : title,
				draggable : false,
				modal : false,
				resizable : false
			});
}

function forwardToPrePage(url){
    id = $.trim($("input[name='id']").val());
    if(id){
    	location.href = url+'?id='+id;
    }else{
    	location.href =url;
    }

}
function showConfirm(msg, callback) {
	if (confirm(msg)) {
		callback.call();
	} else {
		return false;
	}
}

function deleteAll(){
	msg = '确认删除该条信息？';
	showConfirm(msg, function() {
        		$('#action').val('del');
            	$("#batchForm").submit();
			});
}
logout = function (url) {
	if (top) parent.window.location.href = url;
	location.href = url;
}
AjaxLoader = function() {
	var _self = this;
	_self.show = function() {
		$('#ajax_loader').dialog({
					title : '处理中...',
					draggable : false,
					modal : true,
					resizable : false,
					close : function() {
					}
				});
		$('.ui-dialog-titlebar-close').hide();
	}
	_self.hide = function() {
		$('#ajax_loader').dialog('close');
		$('.ui-dialog-titlebar-close').show();
	}

}
var editor;

var EDITOR_ITEMS = [
                    'source', 'preview', '|', 'plainpaste', 'wordpaste', '|',
                    'justifyleft', 'justifycenter', 'justifyright',
        			'justifyfull', 'insertorderedlist', 'insertunorderedlist', 'indent', 'outdent', 'subscript',
        			'superscript', 'clearhtml', 'quickformat','|', 'formatblock', 'fontname', 'fontsize', '|',
        			'forecolor', 'hilitecolor', 'bold','italic', 'underline', 'strikethrough', 'lineheight',
        			'removeformat', '|', 'image','multiimage','table', 'hr', 'link', 'unlink'];
var ajaxLoader = new AjaxLoader();
function ajaxForm(formName, callback, presubmit) {
	if (undefined == callback)	callback = function() {};
	if (undefined == presubmit)	presubmit = function() {};
	if ($('#' + formName)) {
		$('#' + formName).submit(function() {
					if(editor) editor.sync();
					preresult = presubmit.call();
					if(preresult  == false){
						return false;
					}
					var options = {
						dataType : 'json',
						success : function(data) {
							ajaxLoader.hide();
							callback(data);
						}
					};
					ajaxLoader.show();
					$(this).ajaxSubmit(options);
					return false;
				});
	}
}

// ajax默认回调函数
function ajaxCall(ret) {
	if (ret == '')
		return false;
	ret = ('object' == typeof(ret)) ? ret : eval('(' + ret + ')');
	if (ret.success) {
		showMsg('', ret.msg);
	} else {
		showError('', ret.msg);
	}
}

// ajax跳转
function ajaxRedirect(ret, url) {
	if (ret == '' || ret ==  null ||typeof(ret) == 'undefined'){
		location.href = url;
		return false;
	}
	if (ret) {
		if (ret.success) {
			showMsg('', ret.msg);
			setTimeout(function() {
						location.href = url;
					}, 500);
		} else {
			showError('', ret.msg);
		}
	}
}

// 删除单个信息
function deleteOne(url, msg, e) {
	if (msg == '')
		msg = '确认删除该条信息？';
	showConfirm(msg, function() {
				$.ajax({
							url : url,
							type : 'POST',
							dataType : 'json',
							data : 'token='+token,
							success : function(ret) {
								if (ret.success) {
									showMsg('', ret.msg);
									setTimeout(function() {
												location.reload();
											}, 500);
								} else {
									showError('', ret.msg);
								}
							}
						});
			}, e);

}
// 删除单个信息
function changeStatus(url, msg, e) {
	if (msg == '')
		msg = '确认下线该条信息？';
	showConfirm(msg, function() {
		$.ajax({
			url : url,
			type : 'POST',
			dataType : 'json',
			data : 'token='+token,
			success : function(ret) {
				if (ret.success) {
					showMsg('', ret.msg);
					setTimeout(function() {
						location.reload();
					}, 500);
				} else {
					showError('', ret.msg);
				}
			}
		});
	}, e);

}

$(document).ready(function() {
	$.datepicker.regional['zh-CN'] = {
		clearText : '清除',
		clearStatus : '清除已选日期',
		closeText : '关闭',
		closeStatus : '不改变当前选择',
		prevText : '&lt;上月',
		prevStatus : '显示上月',
		nextText : '下月&gt;',
		nextStatus : '显示下月',
		currentText : '今天',
		currentStatus : '显示本月',
		monthNames : ['一月', '二月', '三月', '四月', '五月', '六月', '七月', '八月', '九月',
				'十月', '十一月', '十二月'],
		monthNamesShort : ['一月', '二月', '三月', '四月', '五月', '六月', '七月', '八月',
				'九月', '十月', '十一月', '十二月'],
		monthStatus : '选择月份',
		yearStatus : '选择年份',
		weekHeader : '周',
		weekStatus : '年内周次',
		dayNames : ['星期日', '星期一', '星期二', '星期三', '星期四', '星期五', '星期六'],
		dayNamesShort : ['周日', '周一', '周二', '周三', '周四', '周五', '周六'],
		dayNamesMin : ['日', '一', '二', '三', '四', '五', '六'],
		dayStatus : '设置 DD 为一周起始',
		dateStatus : '选择 m月 d日, DD',
		dateFormat : 'yy-mm-dd',
		firstDay : 1,
		initStatus : '请选择日期',
		isRTL : false,
		changeMonth : true,
		changeYear : true
	};
	$.datepicker.setDefaults($.datepicker.regional['zh-CN']);
	$('#stime').datepicker({
				buttonImage : 'img/calendar.gif',
				buttonImageOnly : true,
				showOn : 'both'
			});
	$('#etime').datepicker({
				buttonImage : 'img/calendar.gif',
				buttonImageOnly : true,
				showOn : 'both'
			});
});

function showImage(id, imgsrc, value, width, height, size) {
	$('#'+id).children('img').attr('src', imgsrc);
	$('#'+id).children('input').attr('value', value);
    $('#'+id).children('input').attr('imagewidth', width);
    $('#'+id).children('input').attr('imageheight', height);
    $('#'+id).children('input').attr('imagesize', size);
}

function showAttach(id, imgsrc, value) {
	$('#'+id).children('img').attr('src', imgsrc);
	$('#'+id).children('input').attr('value', value);
}

function showZip(id, imgsrc, value) {
	$('#'+id).children('a').attr('href', imgsrc);
	$('#'+id).children('input').attr('value', value);
	$('#'+id).children('span').html(imgsrc);
}

function showgdtAttach(id, imgsrc, value, ourimageid) {
	$('#'+id).children('img').attr('src', imgsrc);
	$('#'+id).children('input.our_image').attr('value', value);
    $('#'+id).children('input.our_imageid').attr('value', ourimageid);
}
function showgdtOtherAttach(id, mediasrc, value, ourmediaid) {
	$('#'+id).children('input.our_media_url').attr('src', mediasrc);
	$('#'+id).children('input.our_media').attr('value', value);
    $('#'+id).children('input.our_mediaid').attr('value', ourmediaid);
}
function showApkAttach(id, imgsrc, value) {
	$('#'+id).children('input').attr('value', imgsrc);
}

function showOtherAttach(id, imgsrc, value){
	$('#'+id).children('input').attr('value', value);
}

function showPackage(id, packageName){
	$('#'+id).val(packageName);
}



function showUnitDiv(title, isForword, isEdit){
	return $("#showDeliveryDiv").dialog({
					title : title,
					draggable : false,
					width:600,
					modal : true,
					resizable : false,
					buttons: {
					        'ok': function() {
					        	unit_id = $.trim($("input[name='unit_id']").val());
					            name = $.trim($("input[name='unit_name']").val());
					            limit_type =  $("input[name='limit_type']:checked").val();
					            limit_range = $.trim($("input[name='limit_range']").val());
					            mode_type =  $("input[name='mode_type']:checked").val();
                                unit_type =  $("input[name='unit_type']:checked").val();
					        	if(name == ''){
					        		alert('投放单元名称不能为空');
					        		return false;
					        	}
					        	if(limit_type==null || limit_type == 'undefined' ){
					                alert("投放限额没选中!");
					                return false;
					            }
					         	if(limit_type == 1 && limit_range == ''){
					        		alert("每日限额不能为空!");
					                return false;
					        	}
					        	if(mode_type==null || mode_type == 'undefined'){
					                alert("投放方式没选中!");
					                return false;
					            }
                                if(unit_type==null || unit_type == 'undefined'){
                                    alert("请选择是否内部订单!");
					                return false;
                                }
					        	var object = $(this);
								$.ajax({
										url : baseurl+'/Advertiser/Delivery/addUnit',
										type : 'POST',
										dataType : 'json',
										data : { 'token':token , 'name': name, 'limit_type':limit_type,'limit_range':limit_range,'mode_type': mode_type,'unit_id':unit_id,'unit_type':unit_type },
										success : function(ret) {
											if (ret.success) {
												//showMsg('', ret.msg);
												if(isForword == '0'){
													$("#unit_id").append("<option selected value='"+ret.data.id+"'>"+ret.data.name+"</option>");
                                                    //重新绑定select的选择组件
                                                    $("#unit_id").removeClass("chzn-done");
                                                    $("#unit_id_chzn").remove();
                                                    $("#unit_id").chosen({});
												}else{
													location.reload();
												}
												object.dialog('close');
											} else {
												showError('', ret.msg);
											}
										}
									});
					        },
					        Cancel: function() {
					        	location.reload();
					            $(this).dialog('close');
					        }
					    }
				});
}



function showFile(data, path) {
	var data = data;
	var str = '';
	str += '<input type="hidden" name="file" value="'+data.file+'">';
	str += '缩略图：<img src="'+path+'/'+data.icon+'" />&nbsp;';
	str += '<input type="hidden" name="icon" value="'+data.icon+'">';
	str += '预览图gif：<img src="'+path+'/'+data.img_gif+'" />&nbsp;';
	str += '<input type="hidden" name="img_gif" value="'+data.img_gif+'">';
	str += '预览图png：<img src="'+path+'/'+data.img_png+'" />&nbsp;';
	str += '<input type="hidden" name="img_png" value="'+data.img_png+'">';
	str += '<input type="hidden" name="file_size" value="'+data.file_size+'">';
	$("#File").html(str);
}

function checkAll(classname) {
	$(classname).each(function(){
		if($(this).attr("checked")){
	        $(this).removeAttr("checked");
	    }else{
	        $(this).attr("checked",'true');
	    }
	});
}

//查询
function getQueryString(name) { 
    var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)", "i");
    var r = window.location.search.substr(1).match(reg);
    if (r != null) return decodeURI(r[2]);
    return '';
}

//ajax 
function requestAjax(options,callbackObj){
        var defaults = {
            url : null,
            async : false,
            data : null,
            type : 'get',
            dataType : 'json',
        }
        var empty = {};
        var settings = $.extend(empty, defaults, options);
        $.ajax({
            url : settings.url ,
            async: settings.async,
            data : settings.data,
            type : settings.type,
            dataType : settings.dataType,
            jsonp : 'callback',
            success:function(data){
                callbackObj.success(data);
            },
            error :function(xhr,msg,e){
                callbackObj.error(xhr,msg,e);
            }
        }); 
} 


//重新渲染form
function renderLayuiForm(){
	layui.use('form', function(){
		var form = layui.form;
		form.render();
	});
}

function uploadImg(id,uploadUrl,attachPath) {
    var upload = layui.upload;
    var $ = layui.jquery;
    var uploadInst = upload.render({
        elem: '#'+id //绑定元素
        ,url:  uploadUrl//上传接口
        ,method:'POST'
        ,accept:'file'
        ,data:{'token':token}
        ,before: function(obj){
            layer.load(); //上传loading
            //预读本地文件示例，不支持ie8
            obj.preview(function(index, file, result){
                $('#'+id).parent().find('img').attr('src', result); //图片链接（base64）
            });
        }
        ,done: function(res){
            if(res.success){
                layer.closeAll('loading');
                var uploadtext = '<span class="green">上传成功：</span>'
                $('#'+id).parent().find('input[type="hidden"]').val(res.data);
                $('#'+id).parent().find('p').html(uploadtext+attachPath+res.data);
                layer.msg('上传成功');
            }else{
                layer.closeAll('loading');
                layer.msg(res.msg);
            }
            //上传完毕回调
        }
        ,error: function(){
            layer.msg(msg);
        }
    });
}



function previewOriginality(id,url,attachPath) {
    $.ajax({
        url:  baseurl+url,
        type: "POST",
        dataType : 'json',
        data : { 'token':token,'id':id},
        success: function(ret) {
            if(!ret){
                return false;
            }
            var  originality_type = ret.data.originality_type;
            $("#preview_originality_type").val(originality_type);
            var upload_content = ret.data.upload_content;
            if(upload_content.icon){
                $("#preview_icon").attr('src',attachPath+upload_content.icon);
            }
            $('#preview_title').html(ret.data.title);
            $('#preview_desc').html(ret.data.desc);


            $(".preview_originality"+originality_type).show().siblings().hide();
            $(".preview_li").show();
            if(originality_type==5){
                $('#preview_action_text').html(upload_content.action_text);
                $('#preview_size').html(upload_content.enbed_image_size);
                $('#preview_score').html(upload_content.score);
                if(ret.data.ad_sub_type = 51){
                    $('#preview_single_img_div').show();
                    $('#preview_muti_img_div').hide()
                    $("#preview_single_img").attr('src',attachPath+upload_content.single_img);
                }else if(ret.data.ad_sub_type = 52){
                    $('#preview_muti_img_div').show();
                    $('#preview_single_img_div').hide();
                    $("#preview_muti_img1").attr('src',attachPath+upload_content.combination_img1);
                    $("#preview_muti_img2").attr('src',attachPath+upload_content.combination_img2);
                    $("#preview_muti_img3").attr('src',attachPath+upload_content.combination_img3);
                }
            }else if(originality_type == 1){
                $("#preview_h5").html(attachPath+upload_content.h5);
                $("#preview_video").html(attachPath+upload_content.video);
                $("#preview_video_src").attr('src',attachPath+upload_content.video);
            }else if(originality_type == 2 || originality_type == 3 || originality_type == 4){
                $("#preview_cross_img").attr('src',attachPath+upload_content.cross_img);
                $("#preview_vertical_img").attr('src',attachPath+upload_content.vertical_img);
            }
            return $("#preview").dialog({
                title : '预览',
                draggable : false,
                width:600,
                modal : true,
                resizable : false,
                open: function() {

                },
                buttons: {
                    '返回': function() {
                        $(this).dialog('close');
                    }
                }
            });
        }
    });
}


function bin2hex(s) {
    var i, l, o = '', n;
    s += '';
    for (i = 0, l = s.length; i < l; i++) {
        n = s.charCodeAt(i)
            .toString(16);
        o += n.length < 2 ? '0' + n : n;
    }
    return o;
}

function getUUID(domain) {
    var canvas = document.createElement('canvas');
    var ctx = canvas.getContext('2d');
    var txt = domain;
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
    return crc;
}