<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
/**
 * 
 * Enter description here ...
 * @author rock.luo
 *
 */
class Baseinfo_TemplateController extends Admin_BaseController {
	
	public $actions = array(
		'listUrl' => '/Admin/Baseinfo_Template/index',
		'addUrl' => '/Admin/Baseinfo_Template/add',
		'addPostUrl' => '/Admin/Baseinfo_Template/addPost',
		'deleteUrl' => '/Admin/Baseinfo_Template/delete',
		'viewUrl' => '/Admin/Baseinfo_Template/view',
	     'uploadPostUrl' => '/Admin/Baseinfo_Template/uploadPost',
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
	    $search= $this->getInput(array('name','ad_type'));

	    if ($search['name']) {
	    	$params['name'] = array('LIKE',$search['name']);
	    }
	    if ($search['ad_type']) {
	        $params['ad_type'] = $search['ad_type'];
	    }
	  
	   
	    list($total, $templateList) =MobgiApi_Service_TemplateModel::getList($page, $this->perpage, $params);
	    $url = $this->actions['listUrl'].'/?' . http_build_query($search) . '&';
	    $this->assign('pager', Common::getPages($total, $page, $this->perpage, $url));
	    $this->assign('search', $search);
	    $this->assign('total', $total);
	    $this->assign('templateList', $templateList);
	    
	    $this->assign('adTypeList', Common_Service_Const::$mAdSubType);
	}
	
	
	
	public function addAction() {
		$id = intval ( $this->getGet ( 'id'));
		$this->assign('navTitle', '添加');
		if ($id) {
			$this->assign('navTitle', '编辑');
			$info = MobgiApi_Service_TemplateModel::getByID ( $id );
			if (! $info) {
				$this->output ( - 1, '非法操作' );
			}
			$this->assign ( 'info', $info );
		}
	     $this->assign('adTypeList', Common_Service_Const::$mAdSubType);
	}
	
	public function addPostAction() {
		$info = $this->getPost ( array (
                                                            'id',
                                                            'name',
                                                            'url',
                                                            'ad_type',
													) );
		$info = $this->checkPostParam ( $info );
		if ($info ['id']) {
			$result = MobgiApi_Service_TemplateModel::updateByID ( $info, $info ['id'] );
		} else {
			$result = MobgiApi_Service_TemplateModel::add ( $info );
		}
		if (! $result) {
			$this->output ( - 1, '操作失败' );
		}
		$this->output ( 0, '操作成功');

	}	
	
	

	
 	private function checkPostParam($info)
    {
      
        if (empty($info['ad_type'])){
        	$this->output ( - 1, '请选择广告类型' );
        }
        if (empty(trim($info['name']))){
        	$this->output ( - 1, '请填写模板名称' );
        }
        if (empty(trim($info['url']))){
            $this->output ( - 1, '上传文件为空' );
        }
        if($info['id']){
            $result = MobgiApi_Service_AdsAppRelModel::getBy(array('template_id'=>$info['id']));
            if($result && $result['ad_sub_type'] !=$info['ad_type']){
                $this->output(-1, '此模板正使用,不能修改类型');
            }
        }

        return $info;
    }
	


	/**
	 * 
	 * Enter description here ...
	 */
	public function deleteAction() {
		$id = $this->getInput('id');
		$result = MobgiApi_Service_TemplateModel::getByID($id);
		if (!$result) $this->output(-1, '操作失败');
		if(MobgiApi_Service_AdsAppRelModel::getBy(array('template_id'=>$id))){
		    $this->output(-1, '此模板正使用');
		}
		$result = MobgiApi_Service_TemplateModel::deleteById($id);
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
			$info = MobgiApi_Service_TemplateModel::getByID ( $id );
			if (! $info) {
				$this->output ( - 1, '非法操作' );
			}
			$this->assign ( 'info', $info );
		}
	     $this->assign('adTypeList', Common_Service_Const::$mAdSubType);
	}
	
	public function uploadPostAction() {
	    $ret = Common::upload ( 'file', 'template',array('maxSize'=>1048576));
	   $this->output($ret['code'], $ret['msg'],$ret['data']);
	}
	
	
  
}
