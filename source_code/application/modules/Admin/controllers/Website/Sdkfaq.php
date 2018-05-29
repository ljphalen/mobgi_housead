<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
/**
 * 
 * Enter description here ...
 * @author rock.luo
 *
 */
class Website_SdkfaqController extends Admin_BaseController {
	
	public $actions = array(
		'listUrl' => '/Admin/Website_Sdkfaq/index',
		'addUrl' => '/Admin/Website_Sdkfaq/add',
		'addPostUrl' => '/Admin/Website_Sdkfaq/addPost',
		'faqUrl'=> '/Admin/Website_Sdkfaq/add',
		'deleteUrl' => '/Admin/Website_Sdkfaq/delete',
		'viewUrl' => '/Admin/Website_Sdkfaq/add',
		'uploadImgUrl'=> '/Admin/Website_Sdkdownload/uploadImg',

	);
	public $perpage = 20;
	
	public $mSdkType = array(1=>'安卓',2=>'IOS');
	
	/**
	 * 
	 * Enter description here ...
	 */
	public function indexAction() {
	    $params = array();
	    $page = intval($this->getInput('page'));
	    if ($page < 1) $page = 1;
	    $search= $this->getInput(array('sdk_type','name','tab_type'));
	    $params['tab_type'] = array('IN', array(1,2));    
	    if ($search['sdk_type']) {
	  		 $params['sdk_type'] = $search['sdk_type'];
	    }
	    if ($search['name']) {
	    	$params['name'] = array('LIKE', trim($search['name']));
	    }
	    list($total, $list) =MobgiApi_Service_SdkInfoModel::getList($page, $this->perpage, $params,array('sort'=>'DESC'));
	    $url = $this->actions['listUrl'].'/?' . http_build_query($search) . '&';
	    $this->assign('pager', Common::getPages($total, $page, $this->perpage, $url));	    
	    $this->assign('search', $search);
	    $this->assign('total', $total);
	    $this->assign('list', $list);
	    $this->assign('navTitle', '添加问题与流程');

	}

	public function addAction() {
		$id = intval ( $this->getGet ( 'id'));
		$this->assign('navTitle', '添加');
		if ($id) {
			$this->assign('navTitle', '编辑');
			$info = MobgiApi_Service_SdkInfoModel::getByID ( $id );
			if (! $info) {
				$this->output ( - 1, '非法操作' );
			}
			$this->assign('info', $info);
		}
		$this->assign('sdkType', $this->mSdkType);
	}

	public function addPostAction() {
		$info = $this->getRequest ()->getPost ();
		$this->checkPostParam($info);
		$data = $this->fillData ( $info );
		if ($info ['id']) {
			$result = MobgiApi_Service_SdkInfoModel::updateByID ( $data, $info ['id'] );
		} else {
			$result = MobgiApi_Service_SdkInfoModel::add ( $data );
		}
		if (! $result) {
			$this->output ( - 1, '操作失败' );
		} 
		$this->output ( 0, '操作成功');
	}	
	
	private function fillData($info){
		$data['tab_type'] = $info['tab_type'];
		$data['sdk_type'] = $info['sdk_type'];	
		$data['update_log'] = $info['update_log'];
		return $data;
	}
	private function checkPostParam($info)
	{
	   if (empty($info['tab_type']) || empty($info['update_log']) || empty($info['sdk_type'])){
    		$this->output ( - 1, '请填写必填字段信息！！！' );
        }
        $params['tab_type'] = intval($info['tab_type']);
        $params['sdk_type'] = intval($info['sdk_type']);
        if($info['id']){
        	$params['id'] =  array('<>', $info['id']);
        }
       $result=MobgiApi_Service_SdkInfoModel::getBy($params);
        if ( $result) {
        	$this->output ( - 1, '此类型已经存在' );
        }
    
        
	}
	
	
	
	


	public function deleteAction() {
		$id = $this->getGet ( 'id' );
		$result = MobgiApi_Service_SdkInfoModel::getByID ( $id );
		if (! $result) $this->output ( - 1, '操作失败' );
		$result = MobgiApi_Service_SdkInfoModel::deleteById ( $id );
		if (! $result) $this->output ( - 1, '操作失败' );
		$this->output ( 0, '操作成功' );
	}
	
	/**
	 *
	 * Enter description here ...
	 */
	public function viewAction() {
	
	}

}
