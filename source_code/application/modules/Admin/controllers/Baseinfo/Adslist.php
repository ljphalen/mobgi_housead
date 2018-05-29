<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
/**
 * 
 * Enter description here ...
 * @author rock.luo
 *
 */
class Baseinfo_AdslistController extends Admin_BaseController {
	
	public $actions = array(
		'listUrl' => '/Admin/Baseinfo_Adslist/index',
		'addUrl' => '/Admin/Baseinfo_Adslist/add',
		'addPostUrl' => '/Admin/Baseinfo_Adslist/addPost',
		'deleteUrl' => '/Admin/Baseinfo_Adslist/delete',
		'viewUrl' => '/Admin/Baseinfo_Adslist/view',
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
	    $search= $this->getInput(array('ad_type','ads_id','name'));
	    if ($search['ad_type']) {
	    	$params['ad_type'] =  $search['ad_type'];
	    }
	    if ($search['ads_id']) {
	    	$params['ads_id'] = array('LIKE', trim($search['ads_id']));
	    }
	    if ($search['name']) {
	    	$params['name'] =  array('LIKE', trim($search['name']));
	    }
	    list($total, $adsList) =MobgiApi_Service_AdsListModel::getList($page, $this->perpage, $params);
	    $url = $this->actions['listUrl'].'/?' . http_build_query($search) . '&';
	    $this->assign('pager', Common::getPages($total, $page, $this->perpage, $url));
		$adSubType = Common_Service_Const::$mAdSubType;
		$adSubType[Common_Service_Const::INTERATIVE_AD_SUB_TYPE] = '互动广告';
	    foreach ($adsList as $key =>$val){
	    	$adsList[$key]['ad_type'] = Common_Service_Const::$mAdType[$val['ad_type']];
	    	if($val['ad_type'] != 2){
	    		$adSubTypeArr = json_decode($val['ad_sub_type'], true);
	    		$tmp = array();
	    		foreach ($adSubTypeArr as $subType){
	    			$tmp[] = $adSubType[$subType];
	    		}
	    		$adsList[$key]['ad_sub_type'] = implode(',', $tmp);
	    	}else{
	    		$adsList[$key]['ad_sub_type'] = '无';
	    	}
	    }

	    $this->assign('search', $search);
	    $this->assign('total', $total);
	    $this->assign('adsList', $adsList);
	    $this->assign('adTypeArr', Common_Service_Const::$mAdType);

	}
	
	
	
	public function addAction() {
		$id = intval ( $this->getGet ( 'id'));
		$this->assign('navTitle', '添加');
		if ($id) {
			$this->assign('navTitle', '编辑');
			$adsInfo = MobgiApi_Service_AdsListModel::getByID ( $id );
			if (! $adsInfo) {
				$this->output ( - 1, '非法操作' );
			}
			$this->assign ( 'adsInfo', $adsInfo );
		}
		$adSubTypeArr = Common_Service_Const::$mAdSubType;
		$adSubTypeArr[Common_Service_Const::INTERATIVE_AD_SUB_TYPE] = '互动广告';
	    $this->assign('adTypeArr', Common_Service_Const::$mAdType);
	    $this->assign('adSubTypeArr',$adSubTypeArr);
	}
	
	public function addPostAction() {
		$info = $this->getPost ( array (
														'ads_id',
														'name',
														'ad_type',
														'ad_sub_type',
														'interface_url',
														'is_bid',
														'settlement_method',
														'settlement_price',
														'is_foreign',
														'out_url',
														'id'
													) );
		$info = $this->checkPostParam ( $info );
		if ($info ['id']) {
			$result = MobgiApi_Service_AdsListModel::updateByID ( $info, $info ['id'] );
		} else {
			$result = MobgiApi_Service_AdsListModel::add ( $info );
		}
		if (! $result) {
			$this->output ( - 1, '操作失败' );
		}
		$this->output ( 0, '操作成功');

	}	
	
	
	private function checkUrl($url){
		if(!preg_match('/http|https:\/\/[\w.]+[\w\/]*[\w.]*\??[\w=&\+\%]*/is',$url)){
			return false;
		}
		return true;
	}
	
 	private function checkPostParam($info)
    {
        if (empty($info['ads_id']) || empty($info['name'])){
            $this->output ( - 1, '请填写必填字段信息' );
            
        }
        if($info['ad_type'] != 2){
            if(empty($info['ad_sub_type'])){
                $this->output ( - 1, '广告商要选择广告子类型' );
            }
            $info['ad_sub_type'] = json_encode($info['ad_sub_type']);
        }
        if($info['ad_type'] == 3){
        	if(!$this->checkUrl(trim($info['interface_url']))){
        		$this->output ( - 1, '请求接口地址不合法' );
        	}
        	if(!$info['settlement_method']){
        		$this->output ( - 1, '请选择计费类型' );
        	}
        }
        if($info['ad_type'] == 1){
        	if(!$info['settlement_method']){
        		$this->output ( - 1, '请选择计费类型' );
        	}
        }
        
    	if( trim($info['out_url'])  && !$this->checkUrl($info['out_url'])){
        		$this->output ( - 1, '管理后台地址不合法' );
        	}
 	    if($info['id']){
        	$params['id'] = array('<>',$info['id']);
        }
       $params['ads_id'] =trim($info['ads_id']);
       $result = MobgiApi_Service_AdsListModel::getBy($params);
       if($result){
                $this->output ( - 1, '此广告商编号已经存在' );
            }
        
        
        return $info;
    }
	


	/**
	 * 
	 * Enter description here ...
	 */
	public function deleteAction() {
		$id = $this->getGet('id');
		$result = MobgiApi_Service_AdsListModel::getByID($id);
		if (!$result) $this->output(-1, '操作失败');
		$result = MobgiApi_Service_AdsAppRelModel::getBy(array('ads_id'=>$result['ads_id']));
		if($result){
			$this->output(-1, '基本配置中"'.Common_Service_Const::$mAdSubType[$result['ad_sub_type']].'"配置此广告商');
		}
		$result = MobgiApi_Service_AdsListModel::deleteById($id);
		if (!$result) $this->output(-1, '操作失败');
		$this->output(0, '操作成功');
	}
	
	/**
	 *
	 * Enter description here ...
	 */
	public function viewAction() {
		$id = intval ( $this->getGet ( 'id'));
		$this->assign('navTitle', '添加');
		if ($id) {
			$this->assign('navTitle', '编辑');
			$adsInfo = MobgiApi_Service_AdsListModel::getByID ( $id );
			if (! $adsInfo) {
				$this->output ( - 1, '非法操作' );
			}
			$this->assign ( 'adsInfo', $adsInfo );
		}
	   $this->assign('adTypeArr', Common_Service_Const::$mAdType);
	   $this->assign('adSubTypeArr',Common_Service_Const::$mAdSubType);
	}
  
}
