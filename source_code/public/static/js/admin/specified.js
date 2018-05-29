$("input[name='specified_devices']").click(function(){
	var value = $("input[name='specified_devices']:checked").val(); 
	if (value != '0') {
		$("#deviceList").show();
		$(".specified_device").val(device.join('|'));
	} else {
		$(".specified_device").val('');
		$("#deviceList").hide();
	}
})
$("input[name='specified_devices'][value='0']").attr("checked",true);
if ($(".specified_device").val() != '') {
	$("input[name='specified_devices'][value='1']").attr("checked",true);
}
$("input[name='device[]']").each(function(){
	for (var i=0; i<device.length; i++){
		if ($(this).val()==device[i]){
			$(this).attr("checked", true);
			$("#deviceList").show();
		}
	}
})

$("input[name='device[]']").click(function(){
	device = new Array();
	$("input[name='device[]']").each(function(i){
		if ($(this).attr("checked") == true){
			device[device.length] = $(this).val();
		}
	})
	$(".specified_device").val(device.join('|'));
})