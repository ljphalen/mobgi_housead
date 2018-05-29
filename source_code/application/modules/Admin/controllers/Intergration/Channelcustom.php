<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
/**
 * 
 * Enter description here ...
 * @author rock.luo
 *
 */
class Intergration_ChannelcustomController extends Admin_BaseController {
	
	public $actions = array(
		'listUrl' => '/Admin/Intergration_Channelcustom/index',
		'addUrl' => '/Admin/Intergration_Channelcustom/add',
		'addPostUrl' => '/Admin/Intergration_Channelcustom/addPost',
		'deleteUrl' => '/Admin/Intergration_Channelcustom/delete',
		'viewUrl' => '/Admin/Intergration_Channelcustom/add',
		'getAppListUrl'=>'/Admin/Intergration_Fiter/getAppList',
	   'getPosInfoUrl'=>'/Admin/Intergration_Channelcustom/getPosInfo',

	);
	public $perpage = 20;
	
	/**
	 * 
	 * Enter description here ...
	 */
	public function indexAction() {
	    $params = array();
	    $page = intval($this->getInput('page'));
	    if ($page < 1) $page = 1;
	    $search= $this->getInput(array('platform','name'));
	    if ($search['platform']) {
	  		 $params['platform'] = $search['platform'];
	    }
	    if ($search['name']) {
	    	$params['name'] = array('LIKE', trim($search['name']));
	    }
	    list($total, $list) =MobgiApi_Service_PolymericAdsModel::getList($page, $this->perpage, $params,array('updatetime'=>'DESC','app_key'=>'DESC'));
	    $url = $this->actions['listUrl'].'/?' . http_build_query($search) . '&';
	    $this->assign('pager', Common::getPages($total, $page, $this->perpage, $url));	    
	    $this->assign('search', $search);
	    $this->assign('total', $total);
	    $this->assign('list', $this->fillDataToList($list));
	    $this->assign('adSubType', Common_Service_Const::$mAdSubType);

	}
	
	public function fillDataToList($list) {
		if (empty ( $list )) {
			return array ();
		}
		$appKeys = array_keys ( Common::resetKey ( $list, 'app_key' ) );
		$appInfo = MobgiApi_Service_AdAppModel::getsBy ( array (
				'app_key' => array (
						'IN',
						$appKeys 
				) 
		) );
		$appInfo = Common::resetKey ( $appInfo, 'app_key' );
		foreach ( $list as $key =>$val) {
			$list[$key]['app_name'] = $appInfo[$val['app_key']]['app_name'];
		}
		return $list;
	}
	
	
	
	
	
	public function addAction() {
		$id = intval ( $this->getGet ( 'id'));
		$this->assign('navTitle', '添加');
		$appList = MobgiApi_Service_AdAppModel::getsBy(array('is_check'=>MobgiApi_Service_AdAppModel::ISCHECK_PASS));
		$this->assign('appList', $appList);
		if ($id) {
			$this->assign('navTitle', '编辑');
			$info = MobgiApi_Service_PolymericAdsModel::getByID ( $id );
			if (! $info) {
				$this->output ( - 1, '非法操作' );
			}
			$positionConf = json_decode($info['position_conf'], true);
			foreach ($positionConf['pos_id'] as $key=>$val){
				$pos[$key] = array('id'=>$val,
						'dever_pos_key'=>$positionConf['pos_key'][$key],
						'state'=>$positionConf['status'][$key],
						'dever_pos_name'=>$positionConf['pos_name'][$key],
						'pos_key'=>$positionConf['pos_key_type'][$key],
						'rate'=>$positionConf['rate'][$key],
						'other_block_id'=>$positionConf['other_block_id'][$key],
				);
			}
			$info["pos"]=$pos;
			$this->assign('info', $info);
		}
		$this->assign('adsList', $this->getAdsList());
	}
	
	public function getAdsList(){
		//获取广告商列表
	
		$params['ad_type'] = 2;
		$result =MobgiApi_Service_AdsListModel::getsBy($params);
		$adsList = array();
		foreach ($result as $val){
			$adsList[$val['ads_id']] = $val['name'];
		}
		return $adsList;
	}
	
	

	
	public function addPostAction() {
		$info = $this->getRequest ()->getPost ();
		$this->checkPostParam($info);
		$data = $this->fillData ( $info );
		if ($info ['id']) {
			$result = MobgiApi_Service_PolymericAdsModel::updateByID ( $data, $info ['id'] );
		} else {
			$result = MobgiApi_Service_PolymericAdsModel::add ( $data );
		}
		if (! $result) {
			$this->output ( - 1, '操作失败' );
		} 
		$this->output ( 0, '操作成功');
	}	
	
	private function fillData($info){
		$data['conf_desc'] = $info['conf_desc'];
		$data['name'] = $info['name'];
		$data['platform'] = $info['platform'];
		$data['app_key'] = $info['app_key'];
		$data['secret_key'] = $info['secret_key'];
		$data['third_party_appkey'] = $info['third_party_appkey'];
		$data['position_conf']='';
		foreach ($info['pos_id'] as $key =>$val){
			$status[$key] = $info['pos_state_'.$val][0];
		}
		$position_conf['status'] = $status;
		$position_conf['rate']   = $info['rate'];
		$position_conf['pos_name']   = $info['pos_name'];
		$position_conf['pos_key']   = $info['pos_key'];
		$position_conf['pos_id']   = $info['pos_id'];
		$position_conf['other_block_id']   = $info['other_block_id'];
		if(count($position_conf)){
			$data['position_conf'] = json_encode($position_conf);
		}
		return $data;
	}
	private function checkPostParam($post)
	{
		if (empty($post['name']) || empty($post['platform']) || empty($post['app_key']) || empty($post['third_party_appkey'])){
			$this->output ( - 1, '请填写必填字段信息' );
		}
		$params['name'] = $post['name'];
		$params['app_key'] = $post['app_key'];
		if($post['id']){
			$params['id'] = array('<>', $post['id']);
		}
		$result = MobgiApi_Service_PolymericAdsModel::getBy($params);
		if($result){
			$this->output ( - 1, '此广告商对此应用已经存在！！！' );
		}
	}
	
	
	
	


	public function deleteAction() {
		$id = $this->getGet ( 'id' );
		$result = MobgiApi_Service_PolymericAdsModel::getByID ( $id );
		if (! $result) $this->output ( - 1, '操作失败' );
		$result = MobgiApi_Service_PolymericAdsModel::deleteById ( $id );
		if (! $result) $this->output ( - 1, '操作失败' );
		$this->output ( 0, '操作成功' );
	}
	
	/**
	 *
	 * Enter description here ...
	 */
	public function viewAction() {
	
	}
	
	
	public function getPosInfoAction(){
		$appKey = $this->getGet ( 'appKey' );
		if (! $appKey) $this->output ( - 1, '操作失败' );
		
		$appInfo = MobgiApi_Service_AdAppModel::getBy ( array ('app_key' =>$appKey) );
	    $adPosInfo = MobgiApi_Service_AdDeverPosModel::getsBy(array('dev_id'=>$appInfo ['dev_id'], 'app_id'=>$appInfo ['app_id']) );
	    $this->output ( 0, '操作成功',$adPosInfo );
	}
	
	
	
  
}
