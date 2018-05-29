$(function(){
    var clone_num = $("._creativeForm").length + 1;
    $("._creativeForm").live("mouseover",function(){
        $(this).addClass('hover');
    });
    $("._creativeForm").live("mouseout",function(){
        $(this).removeClass('hover');
    });
    $("._displayWrap").live("mouseover",function(){
        var $el = $(this).parent().parent('.sucai-img');
        $el.addClass('sucai-img-reupload');
        var $reload = $(this).find('._reupload');
        $reload.removeClass('none');
    });
    $("._displayWrap").live("mouseout",function(){
        var $el = $(this).parent().parent('.sucai-img');
        $el.removeClass('sucai-img-reupload');
        var $reload = $(this).find('._reupload');
        $reload.addClass('none');
    });
    $("._removeCabinet").live("click",function(){
        $(this).parent().parent('li').remove();
    });
    $("._addCreative").bind("click",function(){
        if($("._creativeForm").length < 5){
            var new_div = getCreateDiv(clone_num);
            $("._modAddCreative").before(new_div);
            clone_num = clone_num +1;
        }
    });
    $("._upload_65").live("change",function(){
        var $form = $(this).parent('form');
        $("body").mask("处理中");
        $form.ajaxSubmit({
            async : false,
            type : "POST",
            url : "/Advertiser/GdtDelivery/uploadImgPost65",
            data : {},
            dataType : "json",
            success :  function(data) {
                $("body").unmask();
                if(data==''){
                    showError('', "上传文件失败");return false;
                }
                if(data.code!='0'){//上传失败，显示错误信息
                    showError('', data.msg);return false;
                }
                $form.parent('div').next('div').find('img').attr('src',data.img_url);
                $form.parent('div').next('div').find("input[name='upload_template65_img']").val(data.image_id);
                $form.parent('div').next('div').find("input[name='upload_template65_ourimageid']").val(data.our_imageid);
                alert(data.msg);
                //showMsg('', data.msg);return false;
            }
        });
    });
    $("._multiple_upload_65").live("change",function(){
        var $form = $(this).parent('form');
        $("body").mask("处理中");
        $form.ajaxSubmit({
            async : false,
            type : "POST",
            url : "/Advertiser/GdtDelivery/uploadImgsPost65",
            data : {},
            dataType : "json",
            success :  function(data) {
                $("body").unmask();
                if(data==''){
                    showError('', "上传文件失败");return false;
                }
                if(data.code!='0'){//上传失败，显示错误信息
                    showError('', data.msg);return false;
                }
                $.each(data.creative_arr,function(name,value) {
                    var $obj = $("._detail_65").find(".65_createtive_avail");
                    if($obj.length >0){
                        var $first = $("._detail_65").find(".65_createtive_avail").first();
                        $first.find("._displayContainer").find('img').attr('src',value.img_url);
                        $first.find("._displayWrap").find("input[name='upload_template65_img']").val(value.image_id);
                        $first.find("._displayWrap").find("input[name='upload_template65_ourimageid']").val(value.our_imageid);
                        $first.find('.init-img').addClass('ng-hide');
                        $first.find('._displayWrap').removeClass('ng-hide');
                        $first.removeClass('65_createtive_avail');
                    }else{
                        addCreateUsed(value.image_id,value.our_imageid,value.img_url)
                    }
                });
                alert(data.msg);
                //showError('', data.msg);return false;
            }
        });
    });
    function addCreateUsed(image_id,our_imageid,img_url){
        if($("._creativeForm").length < 5){
            var new_div = getCreateUsedDiv(clone_num,image_id,our_imageid,img_url);
            $("._modAddCreative").before(new_div);
            clone_num = clone_num +1;
        }
    }

});
//验证textarea的长度
function changeLength(obj,lg){
    var len = $(obj).val();
    $(obj).next().find("strong").text(len.length);
    if(len.length>=lg){
        $(obj).next().find("strong").text(lg);
        $(obj).val(len.substring(0,lg));
    }
}
function addCreateForm(){
    var new_div = getCreateDiv(1);
    $("._modAddCreative").before(new_div);
}
function getCreateDiv(i)
{
    var token = $("#token").val(); // 必须提交token。
    var new_div = '<li class="sucai-list _creativeForm 65_createtive_avail"  id="65_creativeForm_'+i+'" >';
    new_div = new_div + '<div class="sucai-list-inner">';
    new_div = new_div + '<div class="close _removeCabinet">关闭</div>';
    new_div = new_div + '<div class="sucai-area sucai-num" >';
    new_div = new_div + '<span class="prompt _tnameTextarea">';
    new_div = new_div + '<textarea placeholder="创意标题"  name="creative_name"  class="_tnameInput"  onkeyup="changeLength(this,30)" ></textarea>';
    new_div = new_div + '<span class="somets c-tx3"><strong class="_current">0</strong>/<span>30</span></span>';
    new_div = new_div + '<input  name="tid" type="hidden" /></span></div>';
    new_div = new_div + '<div class="sucai-area sucai-wenan " style="overflow: visible;">';
    new_div = new_div + '<span class="prompt"><textarea name="creative_desc" class="_tdescInput"  placeholder="创意描述"  onkeyup="changeLength(this,30)"></textarea>';
    new_div = new_div + '<span class="somets c-tx3" ><strong class="_current">0</strong>/<span>30</span>';
    new_div = new_div + '</span></span></div>';
    new_div = new_div + '<div class="sucai-area sucai-img" data-format="JPG/JPEG/PNG">';
    new_div = new_div + '<div class="prompt"><div class="sucai-img-container init-img" >';
    new_div = new_div + '<img src="/static/advertiser/css/gdt-img/img218.png" /></div>';
    new_div = new_div + '<div class="sucai-img-standard init-img" >';
    new_div = new_div + '<p class="standard-size"> <span class="c-red">1000px</span>&times; <span class="c-red">560px</span> </p>';
    new_div = new_div + '<p class="c-tx3">(90KB以内的JPG/JPEG/PNG图片)</p>';
    new_div = new_div + '<label class="btn-upload input-file-sizeimg" for="creativeList_'+i+'_multiple_image" title="可以按住CTRL键在对话框中点选不超过5张图片，不符合当前规格的图片不能上传。">批量上传</label>';
    new_div = new_div + '<form name="creativeChild" method="post" enctype="multipart/form-data">';
    new_div = new_div + '<input type="hidden" name="token" value="'+token+'" /> ';
    new_div = new_div + '<input name="filelist[]" id="creativeList_'+i+'_multiple_image" class="_multiple_upload_65" accept="" multiple="" type="file" />';
    new_div = new_div + '<input name="file" id="creativeList_'+i+'_image" class="_upload_65" accept=""  type="file" />';
    new_div = new_div + '</form></div>';
    new_div = new_div + '<div class="_displayWrap ng-hide" ><div class="sucai-img-container _displayContainer">';
    new_div = new_div + '<span class="dot _preview_image"></span><img src="" /></div>';
    new_div = new_div + '<input class="our_image" type="hidden" name="upload_template65_img" value="">';
    new_div = new_div + '<input class="our_imageid" type="hidden" name="upload_template65_ourimageid" value="">';
    new_div = new_div + '<div class="sucai-img-standard _reupload none">';
    new_div = new_div + '<div class="sucai-img-reupload-area">';
    new_div = new_div + '<p>重新选择创意</p>';
    new_div = new_div + '<p> <label class="btn-upload input-file-sizebtn" for="creativeList_'+i+'_image" title="可以按住CTRL键在对话框中点选不超过5张图片，不符合当前规格的图片不能上传。">本地</label></p>';
    new_div = new_div + '</div></div></div></div></div></li>';
    return new_div;
}
function getCreateUsedDiv(i,image_id,our_imageid,img_url)
{
    var token = $("#token").val(); // 必须提交token。
    var new_div = '<li class="sucai-list _creativeForm"  id="65_creativeForm_'+i+'" >';
    new_div = new_div + '<div class="sucai-list-inner">';
    new_div = new_div + '<div class="close _removeCabinet">关闭</div>';
    new_div = new_div + '<div class="sucai-area sucai-num" >';
    new_div = new_div + '<span class="prompt _tnameTextarea">';
    new_div = new_div + '<textarea placeholder="创意标题"  name="creative_name"  class="_tnameInput"  onkeyup="changeLength(this,30)" ></textarea>';
    new_div = new_div + '<span class="somets c-tx3"><strong class="_current">0</strong>/<span>30</span></span>';
    new_div = new_div + '<input  name="tid" type="hidden" /></span></div>';
    new_div = new_div + '<div class="sucai-area sucai-wenan " style="overflow: visible;">';
    new_div = new_div + '<span class="prompt"><textarea name="creative_desc" class="_tdescInput"  placeholder="创意描述"  onkeyup="changeLength(this,30)"></textarea>';
    new_div = new_div + '<span class="somets c-tx3" ><strong class="_current">0</strong>/<span>30</span>';
    new_div = new_div + '</span></span></div>';
    new_div = new_div + '<div class="sucai-area sucai-img" data-format="JPG/JPEG/PNG">';
    new_div = new_div + '<div class="prompt"><div class="sucai-img-container init-img ng-hide" >';
    new_div = new_div + '<img src="/static/advertiser/css/gdt-img/img218.png" /></div>';
    new_div = new_div + '<div class="sucai-img-standard init-img ng-hide" >';
    new_div = new_div + '<p class="standard-size"> <span class="c-red">1000px</span>&times; <span class="c-red">560px</span> </p>';
    new_div = new_div + '<p class="c-tx3">(90KB以内的JPG/JPEG/PNG图片)</p>';
    new_div = new_div + '<label class="btn-upload input-file-sizeimg" for="creativeList_'+i+'_multiple_image" title="可以按住CTRL键在对话框中点选不超过5张图片，不符合当前规格的图片不能上传。">批量上传</label>';
    new_div = new_div + '<form name="creativeChild" method="post" enctype="multipart/form-data">';
    new_div = new_div + '<input type="hidden" name="token" value="'+token+'" /> ';
    new_div = new_div + '<input name="filelist[]" id="creativeList_'+i+'_multiple_image" class="_multiple_upload_65" accept="" multiple="" type="file" />';
    new_div = new_div + '<input name="file" id="creativeList_'+i+'_image" class="_upload_65" accept=""  type="file" />';
    new_div = new_div + '</form></div>';
    new_div = new_div + '<div class="_displayWrap" ><div class="sucai-img-container _displayContainer">';
    new_div = new_div + '<span class="dot _preview_image"></span><img src="'+img_url+'" /></div>';
    new_div = new_div + '<input class="our_image" type="hidden" name="upload_template65_img" value="'+image_id+'">';
    new_div = new_div + '<input class="our_imageid" type="hidden" name="upload_template65_ourimageid" value="'+our_imageid+'">';
    new_div = new_div + '<div class="sucai-img-standard _reupload none">';
    new_div = new_div + '<div class="sucai-img-reupload-area">';
    new_div = new_div + '<p>重新选择创意</p>';
    new_div = new_div + '<p> <label class="btn-upload input-file-sizebtn" for="creativeList_'+i+'_image" title="可以按住CTRL键在对话框中点选不超过5张图片，不符合当前规格的图片不能上传。">本地</label></p>';
    new_div = new_div + '</div></div></div></div></div></li>';
    return new_div;
}
// template_id 为 65 时候，将数据更新
function updateTemplateId65(){
    var json = {};
    var i = 0;
    $("._creativeForm").each(function(){
        json[i] = {};
        json[i]['creative_name']=$(this).find("._tnameInput").val();
        json[i]['creative_desc']=$(this).find("._tdescInput").val();
        json[i]['template65_img']=$(this).find("input[name='upload_template65_img']").val();
        json[i]['template65_ourimageid']=$(this).find("input[name='upload_template65_ourimageid']").val();
        json[i]['template65_img_url']=$(this).find("._displayContainer").find('img').attr('src');
        if($(this).hasClass('_creativeEdit')){
            json[i]['is_edit'] = 1;
        }else{
            json[i]['is_edit'] = 0;
        }
        i = i + 1;
    });
    var data =JSON.stringify(json);
    $("#creative_arr").val(data);
}
// template_id 为 271 时候，将数据更新
function updateTemplateId271(){
    var creative_name = $("#creative_name_271").val();
    $("#creative_name").val(creative_name);
    var creative_desc = $("#creative_desc_271").val();
    $("#creative_desc").val(creative_desc);
    var template271_img1 = $("input[name='upload_template271_img1']").val();
    $("input[name='template271_img1']").val(template271_img1);
    var template271_ourimageid1 = $("input[name='upload_template271_ourimageid1']").val();
    $("input[name='template271_ourimageid1']").val(template271_ourimageid1);
    var template271_img2 = $("input[name='upload_template271_img2']").val();
    $("input[name='template271_img2']").val(template271_img2);
    var template271_ourimageid2 = $("input[name='upload_template271_ourimageid2']").val();
    $("input[name='template271_ourimageid2']").val(template271_ourimageid2);
    var template271_img3 = $("input[name='upload_template271_img3']").val();
    $("input[name='template271_img3']").val(template271_img3);
    var template271_ourimageid3 = $("input[name='upload_template271_ourimageid3']").val();
    $("input[name='template271_ourimageid3']").val(template271_ourimageid3);
}
// template_id 为 351 时候，将数据更新
function updateTemplateId351(){
    var creative_name = $("#creative_name_351").val();
    $("#creative_name").val(creative_name);
    var creative_desc = $("#creative_desc_351").val();
    $("#creative_desc").val(creative_desc);
    var template351_img1 = $("input[name='upload_template351_img1']").val();
    $("input[name='template351_img1']").val(template351_img1);
    var template351_ourimageid1 = $("input[name='upload_template351_ourimageid1']").val();
    $("input[name='template351_ourimageid1']").val(template351_ourimageid1);
    var template351_img2 = $("input[name='upload_template351_img2']").val();
    $("input[name='template351_img2']").val(template351_img2);
    var template351_ourimageid2 = $("input[name='upload_template351_ourimageid2']").val();
    $("input[name='template351_ourimageid2']").val(template351_ourimageid2);
    var template351_video = $("input[name='upload_template351_video']").val();
    $("input[name='template351_video']").val(template351_video);
    var template351_ourvideoid = $("input[name='upload_template351_ourvideoid']").val();
    $("input[name='template351_ourvideoid']").val(template351_ourvideoid);
}