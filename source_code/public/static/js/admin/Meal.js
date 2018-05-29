$(document).ready(function(){
	$('.datetime').datetimepicker();
	$('#addrule').click(function(){
		$('.hasDatepicker').each(function(){
			$(this).removeAttr('id');
			$(this).removeClass('hasDatepicker');
		});
		var html = '<div><span onclick="removeself(this);" class="s1" style="cursor:pointer;">删除</span>' + $('#normaltable').html() + '</div>';
		$('#rule').append(html);
		$('.datetime').datetimepicker();
	});
});
	
ajaxForm("addMealFrom",function(ret){
	var baseUrl = $('#baseurl').val();
	ajaxRedirect(ret, baseUrl);			
});
	
var uploadImgUrl = $('#uploadimgUrl').val();
var editor;
KindEditor.ready(function(K) {
	K.token = $('#token').val();
    editor = K.create('textarea[id="desc"]', {
    	uploadJson : uploadImgUrl,
        resizeType : 1,
        allowPreviewEmoticons : false,
        allowImageUpload : true,
        items : [
            'source', 'preview', '|', 'plainpaste', 'wordpaste', '|', 
            'justifyleft', 'justifycenter', 'justifyright',
			'justifyfull', 'insertorderedlist', 'insertunorderedlist', 'indent', 'outdent', 'subscript',
			'superscript', 'clearhtml', 'quickformat','|', 'formatblock', 'fontname', 'fontsize', '|', 
			'forecolor', 'hilitecolor', 'bold','italic', 'underline', 'strikethrough', 'lineheight', 
			'removeformat', '|', 'image','table', 'hr', 'link', 'unlink']
    });
});
function removeself(ob) {
	$(ob).parent().remove();
}
function showinput(ob) {
	var value = parseInt($(ob).val());
	if (value == 1) $(ob).next().show();
	else $(ob).next().hide();
}
function isclockin(value) {
	if (value==1) {
		$("#clockin_num_label").show();
	} else {
		$("#clockin_num_label").hide();
	}
}

function mealPic(name,src){
	if($(".mealImg li").size()<1){
		var html = '<li><img src="' + src + '" />';
		html += '<input type="hidden" name="meal_img" value="' + name + '">';
		html += '<br /><a href="javascript:;" class="delMealImages">删除</a></li>';
		$(".mealImg").append(html);
	}else{
		showMsg('出错了','套餐图片的个数最多1个');
	}
	$("#mealImgIframe").attr("src", uploadmealimgUrl);
	if($(".mealImg li").size()==1){
		$("#mealImgIframe").hide();
	}
}
$(".delMealImages").live('click',function(){
	$(this).parent().remove();
	if($(".mealImg li").size()<1){
		$("#mealImgIframe").show();
	}
})

function attachPic(name,src){
	if($(".attImg li").size()<4){
		var html = '<li><img src="' + src + '" />';
		html += '<input type="hidden" name="attach_images[]" value="' + name + '">';
		html += '<br /><a href="javascript:;" class="delAttachImages">删除</a></li>';
		$(".attImg").append(html);
	}else{
		showMsg('出错了','附加图片的个数最多4个');
	}
	$("#attImgIframe").attr("src", uploadattachimgUrl);
	if($(".attImg li").size()==4){
		$("#attImgIframe").hide();
	}
}
$(".delAttachImages").live('click',function(){
	$(this).parent().remove();
	if($(".attImg li").size()<4){
		$("#attImgIframe").show();
	}
})
