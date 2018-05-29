<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
/**
 * 
 * Enter description here ...
 * @author rock.luo
 *
 */
class Website_SdkdownloadController extends Admin_BaseController {
	
	public $actions = array(
		'listUrl' => '/Admin/Website_Sdkdownload/index',
		'addUrl' => '/Admin/Website_Sdkdownload/add',
		'addPostUrl' => '/Admin/Website_Sdkdownload/addPost',
		'faqUrl'=> '/Admin/Website_Sdkdownload/add',
		'deleteUrl' => '/Admin/Website_Sdkdownload/delete',
		'viewUrl' => '/Admin/Website_Sdkdownload/add',
		'uploadUrl' => '/Admin/Website_Sdkdownload/upload',
		'uploadPostUrl' => '/Admin/Website_Sdkdownload/uploadPost',
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
	    $params['tab_type'] = 3;
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
	    $this->assign('navTitle', '添加SDK下载列表');

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
		$data['name'] = $info['name'];
		$data['sdk_desc'] = $info['sdk_desc'];
		$data['version'] = $info['version'];
		$data['sdk_update_time'] = $info['sdk_update_time'];
		$data['sort'] = intval($info['sort']);
		$data['update_log'] = $info['update_log'];
		$data['apk_url'] = $info['apk_url'];
		return $data;
	}
	private function checkPostParam($info)
	{
	   if (empty($info['name']) || empty($info['sdk_desc']) || empty($info['version'])||empty($info['update_log'])){
    		$this->output ( - 1, '请填写必填字段信息！！！' );
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
	
	

	
	public function uploadAction() {
		$imgId = $this->getInput('imgId');
		$this->assign('imgId', $imgId);
		$this->getView()->display('common/uploadZip.phtml');
		exit;
	}
	
	public function uploadPostAction() {
		$ret = Common::upload('img', 'sdk_dowload',array('maxSize'=>2000000,'allowFileType'=>array('rar','zip')) );
		$imgId = $this->getInput('imgId');
		$this->assign('code' , $ret['data']);
		$this->assign('msg' , $ret['msg']);
		$this->assign('data', $ret['data']);
		$this->assign('imgId', $imgId);
		$this->getView()->display('common/uploadZip.phtml');
		exit;
	}
	
	

	public function uploadImgAction() {
		$ret = Common::upload('imgFile', 'sdk_dowload');
		if ($ret['code'] != 0) die(json_encode(array('error' => 1, 'message' => '上传失败！')));
		exit(json_encode(array('error' => 0, 'url' => Common::getAttachPath() ."/" .$ret['data'])));
	}
	
	
	
	
  
}
