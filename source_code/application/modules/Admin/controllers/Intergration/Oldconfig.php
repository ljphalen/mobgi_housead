<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
/**
 * 
 * Enter description here ...
 * @author rock.luo
 *
 */
class Intergration_OldconfigController extends Admin_BaseController {
	
	public $actions = array(
		'listUrl' => '/Admin/Intergration_Oldconfig/index',
		'addUrl' => '/Admin/Intergration_Oldconfig/add',
		'addPostUrl' => '/Admin/Intergration_Oldconfig/addPost',
		'deleteUrl' => '/Admin/Intergration_Oldconfig/delete',
		'viewUrl' => '/Admin/Intergration_Oldconfig/add',
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
	    $params['del'] = MobgiApi_Service_VideoAdsModel::NOT_DEL_FLAG;
	    list($total, $list) =MobgiApi_Service_VideoAdsModel::getList($page, $this->perpage, $params,array('updated'=>'DESC','app_key'=>'DESC'));
	    $url = $this->actions['listUrl'].'/?' . http_build_query($search) . '&';
	    $this->assign('pager', Common::getPages($total, $page, $this->perpage, $url));	    
	    $this->assign('search', $search);
	    $this->assign('total', $total);
	    $this->assign('list', $this->fillDataToList($list));

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
			$info = MobgiApi_Service_VideoAdsModel::getByID ( $id );
			if (! $info) {
				$this->output ( - 1, '非法操作' );
			}
			$conf = json_decode($info['video_ads_com_conf'], true);
			$adsList = array();
			if($conf){
				foreach ($conf as $key=>$val){
					$adsList[$key] = array('ads_id'=>$key,
							'percent'=>$val
					);
				}
			}
			$this->assign('adsList',$adsList);
			$this->assign('info', $info);
		}else{
			$this->assign('adsList', $this->getAdsList());
		}
		
	}
	
	public function getAdsList(){
		//获取广告商列表
		$params['ad_type'] = 1;
		$result =MobgiApi_Service_AdsListModel::getsBy($params);
		$adsList = array();
		foreach ($result as $val){
			$adsList[$val['ads_id']] = array('ads_id'=>$val['ads_id'],
					'percent'=>0	
			);
		}
		return $adsList;
	}
	
	

	
	public function addPostAction() {
		$info = $this->getRequest ()->getPost ();
		$this->checkPostParam($info);
		$data = $this->fillData ( $info );
		if ($info ['id']) {
			$result = MobgiApi_Service_VideoAdsModel::updateByID ( $data, $info ['id'] );
		} else {
			$result = MobgiApi_Service_VideoAdsModel::add ( $data );
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
		$data['video_ads_com_conf'] = json_encode($info['video_ads_com_conf']);
		return $data;
	}
	private function checkPostParam($info)
	{
	   if (empty($info['name']) || empty($info['platform']) || empty($info['app_key'])){
    		$this->output ( - 1, '请填写必填字段信息！！！' );
        }
	}
	
	
	
	


	public function deleteAction() {
		$id = $this->getGet ( 'id' );
		$result = MobgiApi_Service_VideoAdsModel::getByID ( $id );
		if (! $result) $this->output ( - 1, '操作失败' );
		$result = MobgiApi_Service_VideoAdsModel::deleteById ( $id );
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
