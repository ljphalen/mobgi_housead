<?php
if (!defined('BASE_PATH')) exit('Access Denied!');

/**
 * 任务系统-任务工厂
 * @author rock.luo
 *
 */
class Dsp_Factory{
    
    private  static $dspClassList = array (
            Common_Service_Const::ETORON_DSP_ID=>'Dsp_Etoron',
            Common_Service_Const::DOMOB_DSP_ID=>'Dsp_Domob',
            Common_Service_Const::SMAATO_DSP_ID=>'Dsp_Smaato',
            Common_Service_Const::TOUTIAO_DSP_ID=>'Dsp_Toutiao',
            Common_Service_Const::INMOBI_DSP_ID=>'Dsp_Inmobi',
            Common_Service_Const:: UNIPLAY_DSP_ID =>'Dsp_Common',
            Common_Service_Const:: HOUSEAD_DSP_ID =>'Dsp_Common',
			Common_Service_Const:: OPERA_DSP_ID =>'Dsp_Opera',
		    Common_Service_Const::ZHIZIYUN_DSP_ID=>'Dsp_Common',
		    Common_Service_Const::ADIN_DSP_ID=>'Dsp_Adin',
			Common_Service_Const::YOMOB_DSP_ID=>'Dsp_Yomob',
			Common_Service_Const::BULEMOBI_DSP_ID=>'Dsp_Bulemobi'
    );
	static $dspInstances;


    /**
     * 根据任务名称创建有效的任务对象
     * 
     * @param obj $event            
     */
    public static function getDspInstances($dspNo) {


		try{
			$instanceExists = (isset(self::$dspInstances[$dspNo]) &&is_object(self::$dspInstances[$dspNo])) ? true : false;
			if($instanceExists) {
				return self::$dspInstances[$dspNo];
			}
			if(!isset(Dsp_Factory::$dspClassList[$dspNo])){
				throw new Exception('Dsp的ID未配置::'.$dspNo);
			}
			$dspClass = Dsp_Factory::$dspClassList[$dspNo];
			if (!class_exists($dspClass)) {
				throw new Exception($dspNo.'实例化类不存在::'.$dspClass);
			}
			$dspInstances[$dspNo] = new  $dspClass();
		}catch (Exception $e){
			throw new Exception('DSP实例化有误'.$dspNo);
		}
        //$dspObj->setPostData($postData);
        //$dspInstances [] = $dspObj;
        return $dspInstances[$dspNo];
    }






}