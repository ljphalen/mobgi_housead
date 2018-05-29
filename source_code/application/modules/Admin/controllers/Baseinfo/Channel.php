<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
/**
 * 
 * Enter description here ...
 * @author rock.luo
 *
 */
class Baseinfo_ChannelController extends Admin_BaseController {
	
	public $actions = array(
		'listUrl' => '/Admin/Baseinfo_Channel/index',
		'addUrl' => '/Admin/Baseinfo_Channel/add',
		'addPostUrl' => '/Admin/Baseinfo_Channel/addPost',
		'deleteUrl' => '/Admin/Baseinfo_Channel/delete',
		'viewUrl' => '/Admin/Baseinfo_Channel/view',
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
	    $search= $this->getInput(array('group_id','channel_id','channel_name'));
	    if ($search['group_id']) {
	    	$params['group_id'] =  $search['group_id'];
	    }else{
	    	$params['group_id'] = array('>', 0);
	    }
	    if ($search['channel_id']) {
	    	$params['channel_id'] = $search['channel_id'];
	    }
	    if ($search['channel_name']) {
	    	$params['channel_name'] =  array('LIKE', trim($search['channel_name']));
	    }
	   
	    list($total, $channelList) =MobgiApi_Service_ChannelModel::getList($page, $this->perpage, $params);
	    $url = $this->actions['listUrl'].'/?' . http_build_query($search) . '&';
	    $this->assign('pager', Common::getPages($total, $page, $this->perpage, $url));
	    
	    $this->assign('search', $search);
	    $this->assign('total', $total);
	    $this->assign('channelList', $channelList);
	    
	    $channelGroupList = MobgiApi_Service_ChannelModel::getsBy(array('group_id'=>0));
	    $channelGroupList  = Common::resetKey($channelGroupList, 'channel_id');
	    
	    $this->assign('channelGroupList', $channelGroupList);
	}
	
	
	
	public function addAction() {
		$id = intval ( $this->getGet ( 'id'));
		$this->assign('navTitle', '添加');
		if ($id) {
			$this->assign('navTitle', '编辑');
			$channelInfo = MobgiApi_Service_ChannelModel::getByID ( $id );
			if (! $channelInfo) {
				$this->output ( - 1, '非法操作' );
			}
			$this->assign ( 'channelInfo', $channelInfo );
		}
	    $channelGroupList = MobgiApi_Service_ChannelModel::getsBy(array('group_id'=>0));
	    $channelGroupList  = Common::resetKey($channelGroupList, 'channel_id');
	    $this->assign('channelGroupList', $channelGroupList);
	}
	
	public function addPostAction() {
		$info = $this->getPost ( array (
                                                            'group_id',
                                                            'channel_id',
                                                            'channel_name',
                                                            'id',
                                                            'is_custom',
                                                            'is_check_config',
													) );
		$info = $this->checkPostParam ( $info );
		if ($info ['id']) {
			$result = MobgiApi_Service_ChannelModel::updateByID ( $info, $info ['id'] );
		} else {
			$result = MobgiApi_Service_ChannelModel::add ( $info );
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
        if (empty($info['group_id']) || empty($info['group_id'])){
            $this->output ( - 1, '请选择分组ID' );
        }
        if (empty($info['channel_id'])){
        	$this->output ( - 1, '请填写渠道ID' );
        }
        if (empty(trim($info['channel_name']))){
        	$this->output ( - 1, '请填写渠道ID' );
        }
        if($info['id']){
        	$params['id'] = array('<>',$info['id']);
        }
        $params['channel_id'] = trim($info['channel_id']);
        $result = MobgiApi_Service_ChannelModel::getBy($params);
         if($result){
                $this->output ( - 1, '此渠道编号已经存在' );
         }
        return $info;
    }
	


	/**
	 * 
	 * Enter description here ...
	 */
	public function deleteAction() {
		$id = $this->getGet('id');
		$result = MobgiApi_Service_ChannelModel::getByID($id);
		if (!$result) $this->output(-1, '操作失败');
		$result = MobgiApi_Service_ChannelModel::deleteById($id);
		if (!$result) $this->output(-1, '操作失败');
		$this->output(0, '操作成功');
	}
	
	/**
	 *
	 * Enter description here ...
	 */
	public function viewAction() {
		$id = intval ( $this->getGet ( 'id'));
		$this->assign('navTitle', '查看');
		$channelInfo = MobgiApi_Service_ChannelModel::getByID ( $id );
		if (! $channelInfo) {
			$this->output ( - 1, '非法操作' );
		}
		$this->assign ( 'channelInfo', $channelInfo );
	
	    $channelGroupList = MobgiApi_Service_ChannelModel::getsBy(array('group_id'=>0));
	    $channelGroupList  = Common::resetKey($channelGroupList, 'channel_id');
	    $this->assign('channelGroupList', $channelGroupList);
	}
	
	public function getCategoryByTypeAction(){
		$get = $this->getInput(array('type','selids','callbackparam'));
		$type = intval($get['type']);
		$selids = $get['selids'];
		if(!empty($selids)){
			$selidsArr = explode(',', $selids);
		}else{
			$selidsArr = array();
		}
	
	
		$channelGroupList = MobgiApi_Service_ChannelModel::getsBy(array('group_id'=>0));
	    $channelGroupList  = Common::resetKey($channelGroupList, 'channel_id');
		$categoryArr = array();
		if(!empty($channelGroupList)){
			foreach($channelGroupList as $key=>$item)
			{
				$categoryArr[] = array('id'=>$key,
						'name'=>$item['channel_name'],
						'checked'=>in_array($item['id'], $selidsArr)?'true':''
				);
			}
		}
		$json_data = json_encode($categoryArr);
		$result = '';
		if($get['callbackparam']){
			$result = $get['callbackparam']."(".$json_data.")";
		}else{
			$result = $json_data;
		}
		echo $result;
		exit;
	}
	
	
	public function getChannelDataAction(){
		$get = $this->getInput(array('classIds','callbackparam'));
		$classIds = $get['classIds'];
		$classIdsArr = explode(',', $classIds);
		$idsArr = array();
		if(!empty($classIdsArr)){
			foreach($classIdsArr as $classId){
				$idsArr[] =$classId;
			}
		}

		$arr_channels = array();
		if(!empty($idsArr)){
			$categoryArr = MobgiApi_Service_ChannelModel::getsBy(array('group_id' => array('IN', $idsArr)));
			if($categoryArr){
				foreach($categoryArr as $channelinfo){
					$arr_channels[]['channels'] = array('identifier'=>$channelinfo['channel_id'],
							'realname'=>$channelinfo['channel_name'],
					);
				}
			}
		}
		$json_data = json_encode($arr_channels);
		$result = '';
		if($get['callbackparam']){
			$result = $get['callbackparam']."(".$json_data.")";
		}else{
			$result = $json_data;
		}
		echo $result;
		exit;
	}
  
}
