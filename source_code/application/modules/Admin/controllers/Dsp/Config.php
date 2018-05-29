<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
/**
 * 
 * Enter description here ...
 * @author rock.luo
 *
 */
class Dsp_ConfigController extends Admin_BaseController {
	
    public $perpage = 20;
	public $actions = array(
        'configListUrl'=>'/Admin/Dsp_Config/index',
        'saveConfigUrl'=>'/Admin/Dsp_Config/saveConfig',

	);
	
	public function indexAction() {
		$result = Advertiser_Service_ConfigModel::getAllConfig();
		$this->assign('result', $result);
	}
	
	public function saveConfigAction(){
		$rank_price_button = $this->getInput('rank_price_button');
		$ret = Advertiser_Service_ConfigModel::setValue('rank_price_button', $rank_price_button, $this->userInfo['user_id']);
		$this->output(0 ,'操作成功');
		
	}
	
	

}
