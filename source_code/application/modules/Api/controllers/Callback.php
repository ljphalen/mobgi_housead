<?php

/*
cdn回调
 */
class CallbackController extends Api_BaseController{

	public function reportAction(){
		Util_Cdn::report();
	}
	
}
?>
