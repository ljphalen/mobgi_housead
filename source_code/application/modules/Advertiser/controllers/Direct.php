<?php

/**
 * @Encoding      :   UTF-8
 * @Author       :   hunter.fang
 * @Email         :   782802112@qq.com
 * @Time          :   2016-9-6 14:39:16
 * $Id: Direct.php 62100 2016-9-6 14:39:16Z hunter.fang $
 */


if (!defined('BASE_PATH')) exit('Access Denied!');


class DirectController extends Admin_BaseController {
    
    public $perpage = 10;
	
    public $actions = array(
        'listUrl' => '/Advertiser/Direct/list',
        'addUrl' => '/Advertiser/Direct/add',
        'copyUrl' => '/Advertiser/Direct/copy',
        'delUrl' => '/Advertiser/Direct/del',
        'changenameUrl' => '/Advertiser/Direct/changename',
        'savePostUrl' => '/Advertiser/Direct/savePost',
	);
    
    public function init(){
    	parent::init();
    	if ($this->userInfo['user_type'] == 3){
    		$this->showMsg(-1,'此用户类型不能操作');
    	}
    }
    
     /**
     * 定向管理
     */
    public function listAction(){

        $page = intval($this->getInput('page'));
        $perpage = $this->perpage;
        $params  = array();
        $params['advertiser_uid'] = $this->userInfo['user_id'];
        
        #获取列表及分页
		list($total, $list) = Advertiser_Service_DirectModel::getList($page, $perpage, $params);
        
        #获取各个定向的关联广告数
        if($list){
            foreach($list as $key=>$item){
                $list[$key]['relation_ad_num']  = Dedelivery_Service_AdConfListModel::getCountBy(array('account_id'=>$this->userInfo['user_id'], 'direct_id'=>$item['id']));
            }
        }
        
        $this->assign('list', $list);
		$this->assign('pager', Common::getPages($total, $page, $perpage, $this->actions['listUrl'].'/?'));
        
        $config = Common::getConfig('deliveryConfig');
	    foreach ($config as $key=>$val){
	        $this->assign($key, $val);
	    }
    }
    
    public function addAction(){
        $id=$this->getInput('id');
        if($id){
            $direct = Advertiser_Service_DirectModel::getDirect($id);
            if($direct['direct_config']){
            	$ditectConfData = json_decode($direct['direct_config'], true);
            	$direct = array_merge($direct, $ditectConfData);
            }
        }else{
            $direct=array();
        }
        $config = Common::getConfig('deliveryConfig');
	    foreach ($config as $key=>$val){
	        $this->assign($key, $val);
	    }
        $this->assign('indexUrl', $this->actions['indexUrl']);
        $this->assign('id', $id);
        $this->assign('result', $direct);
        
    }
    
    public function savePostAction() {
        $info = $this->getInput(array('direct_name','area_type', 'area_range', 'age_direct_type', 'age_direct_range',
            'sex_direct_type', 'os_direct_type','network_direct_type','network_direct_range', 
           'operator_direct_type','operator_direct_range','brand_direct_type','brand_direct_range',
            'screen_direct_type','screen_direct_range','interest_direct_type','interest_direct_range',
            'pay_ability_type','pay_ability_range','game_frequency_type','game_frequency_range',
            'id'
            ));

	    $info = $this->checkAddParam($info);
	    $info['direct_config'] = json_encode($this->fillDiRectConf($info));
        if(empty($info['id'])){
            if(Advertiser_Service_DirectModel::getDirectByName($info['direct_name'])){
                $this->output(-1, '定向名称已经被使用');
            }
            $info['advertiser_uid'] = $this->userInfo['user_id'];
            $directid = Advertiser_Service_DirectModel::addDirect($info);
            if (!$directid) $this->output(-1, '操作失败');
            $this->output(0, '操作成功');
        }else{
            $direct = Advertiser_Service_DirectModel::getDirect($info['id']);
            if($direct['advertiser_uid']!=$this->userInfo['user_id']){
                $this->output(-1, '只能编辑自己创建的定向');
            }
            $result = Advertiser_Service_DirectModel::updateDirect($info, $info['id']);
            if (!$result) $this->output(-1, '操作失败');
            $this->output(0, '更新成功');
        }
    }
    
    /**
     * 复制定向
     */
    public function copyAction(){
        $id=$this->getInput('id');
        if(empty($id)){
	        $this->output(1, '参数错误');
	    }
        $direct = Advertiser_Service_DirectModel::getDirect($id);
        if($direct['advertiser_uid']!=$this->userInfo['user_id']){
            $this->output(-1, '只能复制自己创建的定向');
        }
        //判断新定向名是否可用
        $new_direct_name = $direct['direct_name']."_副本";
        if(Advertiser_Service_DirectModel::getDirectByName($new_direct_name)){
            $this->output(-1, '已经存在名称为"'.$new_direct_name.'"的定向');
        }
        $copydirect = $direct;
        $copydirect['direct_name']=$new_direct_name;
        $directid = Advertiser_Service_DirectModel::addDirect($copydirect);
        if (!$directid) $this->output(-1, '操作失败');
        $this->output(0, '操作成功');
    }
    
    /**
     * 更改定向名称
     */
    public function changenameAction(){
        $id=$this->getInput('id');
        $direct_name = $this->getInput('direct_name');
        if(empty($id) || empty($direct_name)){
	        $this->output(1, '参数错误');
	    }
        $direct = Advertiser_Service_DirectModel::getDirect($id);
        if($direct['advertiser_uid']!=$this->userInfo['user_id']){
            $this->output(-1, '只能编辑自己创建的定向');
        }
        if($direct['direct_name']==$direct_name){
            $this->output(-1, '名称未改变');
        }
        //判断新定向名是否可用
        if(Advertiser_Service_DirectModel::getDirectByName($direct_name)){
            $this->output(-1, '已经存在名称为"'.$direct_name.'"的定向');
        }
        $info = array();
        $info['direct_name'] = $direct_name;
        $result = Advertiser_Service_DirectModel::updateDirect($info, $id);
        if (!$result) $this->output(-1, '操作失败');
        $this->output(0, '更新成功');
    }
    
    /**
     * 删除定向
     */
    public function delAction(){
        $id=$this->getGet('id');
        if(empty($id)){
	        $this->output(1, '参数错误1');																																	
	    }
        $direct = Advertiser_Service_DirectModel::getDirect($id);
        if($direct['advertiser_uid']!=$this->userInfo['user_id']){
            $this->output(-1, '只能删除自己创建的定向');
        }
        
        $relation_ad_num  = Dedelivery_Service_AdConfListModel::getCountBy(array('account_id'=>$this->userInfo['user_id'], 'direct_id'=>$id));
        if($relation_ad_num){
            $this->output(-1, '不能删除有关联广告的定向');
        }
        
        $result = Advertiser_Service_DirectModel::deleteDirect($id);
        if (!$result) $this->output(-1, '操作失败');
        $this->output(0, '操作成功');
    }
    
    
    private function fillDiRectConf($ditectConfData){
    	$data['area_type'] = $ditectConfData['area_type'];
    	if(isset($ditectConfData['area_range'])){
    		$data['area_range'] = $ditectConfData['area_range'];
    	}
    	$data['age_direct_type'] = $ditectConfData['age_direct_type'];
    	if(isset($ditectConfData['age_direct_range'])){
    		$data['age_direct_range'] = $ditectConfData['age_direct_range'];
    	}
    	$data['sex_direct_type'] = $ditectConfData['sex_direct_type'];
    	$data['os_direct_type'] = $ditectConfData['os_direct_type'];
    	$data['network_direct_type'] = $ditectConfData['network_direct_type'];
    	if(isset($ditectConfData['network_direct_range'])){
    		$data['network_direct_range'] = $ditectConfData['network_direct_range'];
    	}
    	$data['operator_direct_type'] = $ditectConfData['operator_direct_type'];
    	if(isset($ditectConfData['operator_direct_range'])){
    		$data['operator_direct_range'] = $ditectConfData['operator_direct_range'];
    	}
    	$data['brand_direct_type'] = $ditectConfData['brand_direct_type'];
    	if(isset($ditectConfData['brand_direct_range'])){
    		$data['brand_direct_range'] = $ditectConfData['brand_direct_range'];
    	}
    	$data['screen_direct_type'] = $ditectConfData['screen_direct_type'];
    	if(isset($ditectConfData['screen_direct_range'])){
    		$data['screen_direct_range'] = $ditectConfData['screen_direct_range'];
    	}
    	$data['interest_direct_type'] = $ditectConfData['interest_direct_type'];
    	if(isset($ditectConfData['interest_direct_range'])) {
    		$data['interest_direct_range'] = $ditectConfData['interest_direct_range'];
    	}
    	$data['pay_ability_type'] = $ditectConfData['pay_ability_type'];
    	if(isset($ditectConfData['pay_ability_range'])) {
    		$data['pay_ability_range'] = $ditectConfData['pay_ability_range'];
    	}
    	$data['game_frequency_type'] = $ditectConfData['game_frequency_type'];
    	if(isset($ditectConfData['game_frequency_range'])){
    		$data['game_frequency_range'] = $ditectConfData['game_frequency_range'];
    	}
    	return $data;
    }
    
    private function checkAddParam($info){
        if(empty($info['direct_name'])){
	        $this->output(1, '请填写定向名称');
	    }
        //判断是否重复
        if(strlen($info['direct_name'])>120){
            $this->output(1, '名称限制在120个英文字符内');
        }
	    if(!isset($info['area_type'])){
	        $this->output(1, '地域没有选中');
	    }
	    if($info['area_type'] == '1' && !isset($info['area_range'])){
	        $this->output(1, '地域的定向没有选中');
	    }
	    if(!isset($info['age_direct_type'])){
	        $this->output(1, '年龄没有选中');
	    }
	    if($info['age_direct_type'] == '1' && !isset($info['age_direct_range'])){
	        $this->output(1, '年龄的定向没有选中');
	    }
	    if(!isset($info['sex_direct_type'])){
	        $this->output(1, '性别没有选中');
	    }
	    if(!isset($info['os_direct_type'])){
	        $this->output(1, '操作系统没有选中');
	    }
	    if(!isset($info['network_direct_type'])){
	        $this->output(1, '网络环境没有选中');
	    }
	    if($info['network_direct_type'] == '1' && !isset($info['network_direct_range'])){
	        $this->output(1, '网络环境的定向没有选中');
	    }
	    if(!isset($info['operator_direct_type'])){
	        $this->output(1, '运营商没有选中');
	    }
	    if($info['operator_direct_type'] == '1' && !isset($info['operator_direct_range'])){
	        $this->output(1, '运营商的定向没有选中');
	    }
	    if(!isset($info['brand_direct_type'])){
	        $this->output(1, '手机品牌没有选中');
	    }
	    if($info['brand_direct_type'] == '1' && !isset($info['brand_direct_range'])){
	        $this->output(1, '手机品牌的定向没有选中');
	    }
	    if(!isset($info['screen_direct_type'])){
	        $this->output(1, '屏幕大小没有选中');
	    }
	    if($info['screen_direct_type'] == '1' && !isset($info['screen_direct_range'])){
	        $this->output(1, '屏幕大小的定向没有选中');
	    }
	    if(!isset($info['interest_direct_type'])){
	        $this->output(1, '游戏兴趣没有选中');
	    }
	    if($info['interest_direct_type'] == '1' && !isset($info['interest_direct_range'])){
	        $this->output(1, '游戏兴趣的定向没有选中');
	    }
	    if(!isset($info['pay_ability_type'])){
	        $this->output(1, '付费能力没有选中');
	    }
	    if($info['pay_ability_type'] == '1' && !isset($info['pay_ability_range'])){
	        $this->output(1, '付费能力的定向没有选中');
	    }
	    if(!isset($info['game_frequency_type'])){
	        $this->output(1, '游戏频率没有选中');
	    }
	    if($info['game_frequency_type'] == '1' && !isset($info['game_frequency_range'])){
	        $this->output(1, '游戏频率的定向没有选中');
	    }
	    return $info;
	}
    
}
