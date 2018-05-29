$(document).ready(function(){
	$('#start_time').datetimepicker();
	$('#end_time').datetimepicker();
	var uploadImgUrl = $('#uploadimgUrl').val();
    var editor;
    KindEditor.ready(function(K) {
    	K.token = $('#token').val();
        editor = K.create('textarea[id="desc"]', {
        	uploadJson : uploadImgUrl,
            resizeType : 1,
            allowPreviewEmoticons : false,
            allowImageUpload : true,
			urlType : 'domain',
            items : [
                'source', 'preview', '|', 'plainpaste', 'wordpaste', '|', 
                'justifyleft', 'justifycenter', 'justifyright',
				'justifyfull', 'insertorderedlist', 'insertunorderedlist', 'indent', 'outdent', 'subscript',
				'superscript', 'clearhtml', 'quickformat','|', 'formatblock', 'fontname', 'fontsize', '|', 
				'forecolor', 'hilitecolor', 'bold','italic', 'underline', 'strikethrough', 'lineheight', 
				'removeformat', '|', 'image','table', 'hr', 'link', 'unlink']
        });
    });
})