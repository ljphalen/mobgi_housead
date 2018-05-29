<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
/**
 * @desc 
 * @filesource csv.php
 * @author ljp
 * @date 
 */
 class Util_Excel_Csv implements Util_Excel_Base{
	private $mCharSet='UTF-8';
	

	public function __construct(){
		
	}
	
	/**
	 * 导入数据
	 * @see Util_Excel_Base::import()
	 */
	public function import($fileName ){

        if(!$fileName){
        	return false;
        }
        
		$file = fopen($fileName,"r");
		$out = array ();
		$n = 0 ;
		$headerFlag = 1;
		$header = array();
		while ($data = fgetcsv($file, 100000)) {   
			if($headerFlag){
				foreach ($data as $key=>$val){
				  $header[$key] = $val;
				} 
				$headerFlag = 0;
			}else{
		    	foreach ($data as $key=>$val){
		    		$out[$n][$header[$key]] = $val;
		    	}
		    	$n++;
		    }
		}
		return $out;		
	}
	
	//导出excel
	public function export($fileName, $data){
		if(!$fileName || !is_array($data)){
			return false;
		}	
		header("Content-type:text/csv");
		header("Content-Disposition:attachment;filename=".$fileName);
		header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
		header('Expires:0');
		header('Pragma:public');
		echo $data;
	}
	

	/**
	 * @desc 析构方法
	 */
	public function __destruct(){
		unset($this->mCharSet);
	}
}
