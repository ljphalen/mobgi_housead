<?php
/**
 *@desc excel接口
* @author 
*/
interface  Util_Excel_Base
{
	//导入
	public function import($fileName);
	//导出
	public function export($fileName, $data);

	
}
