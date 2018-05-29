<?php

/**
 * @Encoding      :   UTF-8
 * @Author       :   hunter.fang
 * @Email         :   782802112@qq.com
 * @Time          :   2017-9-18 17:09:11
 * $Id: Landingpage.php 62100 2017-9-18 17:09:11Z hunter.fang $
 */

if (!defined('BASE_PATH')) exit('Access Denied!');

class Systemtool_LandingpageController extends Admin_BaseController {
    public $perpage = 10;
	
	public $actions = array(
        'listUrl' => '/Admin/Systemtool_Landingpage/list',
        'addUrl' => '/Admin/Systemtool_Landingpage/add',
        'addPostUrl' => '/Admin/Systemtool_Landingpage/addPost',
        'addStaticUrl' => '/Admin/Systemtool_Landingpage/addStatic',
        'addStaticPostUrl' => '/Admin/Systemtool_Landingpage/addStaticPost',
        'uploadUrl' => '/Admin/Systemtool_Landingpage/uploadImg',
	    'uploadPostUrl' => '/Admin/Systemtool_Landingpage/uploadImgPost',
        'getStaticTemplateUrl' => '/Admin/Systemtool_Landingpage/getStaticTemplate',
	);
    
    /**
     * 落地页列表
     */
    public function listAction() {
        $params = $this->getInput(array('screatedate', 'ecreatedate', 'status', 'app_id', 'title', 'page'));
        $page = intval($params['page']);
	    if ($page < 1) $page = 1;
        $whereArr = array ();
        $pageParams = array();
        
        //默认开始日期：2017-01-01
        if(empty($params['screatedate'])  ){
            $params['screatedate'] = '2017-01-01';
        }
        if(empty($params['ecreatedate'])){
            $params['ecreatedate'] = date("Y-m-d");
        }
        
        //搜索时间
        if (isset($params['screatedate']) && $params['screatedate'] && isset($params['ecreatedate']) && $params['ecreatedate'] ) {
            $pageParams ['screatedate'] = $params ['screatedate'];
            $pageParams ['ecreatedate'] = $params ['ecreatedate'];
            $screatetime = strtotime($params['screatedate']);
            $ecreatetime = strtotime($params['ecreatedate']." 23:59:59");
            $whereArr['create_time'] = array(array('>=', $screatetime), array('<=', $ecreatetime));
        }
		if (isset ( $params ['status'] ) && $params ['status']) {
			$whereArr ['status'] =  $params ['status'] ;
            $pageParams ['status'] =  $params ['status'] ;
		}
        
        if (isset ( $params ['app_id'] ) && $params ['app_id']) {
			$whereArr ['app_id'] =  $params ['app_id'] ;
            $pageParams ['app_id'] =  $params ['app_id'] ;
		}
		if ($params ['title']) {
			$whereArr ['title'] = array('like', trim($params ['title']));
            $pageParams ['title'] =  trim($params ['title']) ;
		}
        
        list($total, $list) = Advertiser_Service_LandingpageModel::getList($page, $this->perpage, $whereArr, array('update_time'=>'DESC'));
        list($appTotal, $appList) = MobgiApi_Service_AdAppModel::getList(1, 999999, array(), array());
        $appList = common::resetKey($appList, 'app_id');
        
        if($list){
            foreach($list as $key=>$item){
                $appInfo = MobgiApi_Service_AdAppModel::getByID($item['app_id']);
                $platformStr = $appInfo['platform'] == 1?"Android":"IOS";
                $list[$key]['app_name'] = $appInfo['app_name']."(". $platformStr. ")";
            }
        }
        
        $url = $this->actions['listUrl'].'/?' . http_build_query($pageParams) . '&';
		$this->assign('pager', Common::getPages($total, $page, $this->perpage, $url));
        
        $this->assign('total', $total);
        $this->assign('list', $list);
        $this->assign('appList', $appList);
        $this->assign('params', $params);
    }
    
    /**
     * 新建落地页
     */
    public function addAction() {
        $info = $this->getInput(array('id','action'));
        
        list($appTotal, $appList) = MobgiApi_Service_AdAppModel::getList(1, 999999, array(), array());
        $appList = common::resetKey($appList, 'app_id');
        $this->assign('appList', $appList);
        
        if(!$info['id']){
            $uId = $this->userInfo['user_id'];
            $cache = Advertiser_Service_LandingpageModel::getCache();
            $key =Advertiser_Service_LandingpageModel::getAddLandingStepKey(1, $uId, intval($info['id']));
            $result = $cache->get($key);
        }else{
            $result =  Advertiser_Service_LandingpageModel::getBy(array('id'=>intval($info['id'])));
        }
        
        $dynamicTemplate = Advertiser_Service_LandingpagetemplateModel::getsBy(array('type'=>Advertiser_Service_LandingpagetemplateModel::DYNAMIC_TEMPLATE));
        if( $result['app_id'] && ($result['template_type'] == Advertiser_Service_LandingpagetemplateModel::STATIC_TEMPLATE)){
            $staticTemplate = Advertiser_Service_LandingpagetemplateModel::getsBy(array('type'=>Advertiser_Service_LandingpagetemplateModel::STATIC_TEMPLATE, 'app_id'=>$result['app_id']));
        }
	    $this->assign('result', $result);
        $this->assign('dynamicTemplate', $dynamicTemplate);
        $this->assign('staticTemplate', $staticTemplate);
    }
    
    /**
     * 新建落地页第一步提交保存
     */
    public function addPostAction(){
        $info = $this->getInput(array('app_id', 'template_type','template_id', 'id', 'action' ));
        $info = $this->checkLandingpageStep1Param($info);
        $uId = $this->userInfo['user_id'];

        $cache = Advertiser_Service_LandingpageModel::getCache(); 
        $key =Advertiser_Service_LandingpageModel::getAddLandingStepKey(1, $uId, intval($info['id']));
        $result = $cache->set($key, $info, Advertiser_Service_LandingpageModel::CACHE_EPIRE);
        if (!$result){
            $this->output(1, '操作失败');
        }
        $this->output(0, '操作成功');
    }
    
    /**
     * 检测新建落地页提交保存参数校验
     * @param type $info
     * @return type
     */
    public function checkLandingpageStep1Param($info){
        if(empty($info['app_id'])){
            $this->output(1, '请选择应用');
        }
        if(empty($info['template_type'])){
            $this->output(1, '请选择模板类弄');
        }
        if(!in_array($info['template_type'], array(1, 2))){
            $this->output(1, '模板类型参数错误');
        }
        if(empty($info['template_id'])){
            $this->output(1, '请选择模板');
        }
        return $info;
    }
    
    /**
     * 静态模板页面参数
     */
    public function addStaticAction(){
        $info = $this->getInput(array('id','action'));
        $uId = $this->userInfo['user_id'];
        $cache = Advertiser_Service_LandingpageModel::getCache();
        $key1 =Advertiser_Service_LandingpageModel::getAddLandingStepKey(1, $uId, intval($info['id']));
        $result1 = $cache->get($key1);
        if (!$result1){
            $this->output(1, '操作失败');
        }
        if(!$info['id']){
            $key2 =Advertiser_Service_LandingpageModel::getAddLandingStepKey(2, $uId, intval($info['id']));
            $result2 = $cache->get($key2);
        }else{
            $result2 =  Advertiser_Service_LandingpageModel::getBy(array('id'=>intval($info['id'])));
        }
        if($result2){
            $result = array_merge($result1, $result2);
        }else{
            $result = $result1;
        }
        
        //动态模板需要获取动态模板名称
        if($result['template_type'] == 1 ){
            $templateInfo = Advertiser_Service_LandingpagetemplateModel::getBy(array('id'=>$result['template_id']));
            $result['create_name'] = $templateInfo['create_name'];
        }
        
        $attachUrl = Common::getAttachUrl();
        $this->assign('result', $result);
        $this->assign('attachUrl', $attachUrl);
        $this->assign('webRoot', Common::getWebRoot());
        $this->assign('apiRoot', Yaf_Application::app()->getConfig()->apiroot);
        
    }
    
    /**
     * 
     */
    public function addStaticPostAction(){
        $info = $this->getInput(array('title', 'url','template_data' ,'id', 'action' ));
        $info = $this->checkLandingpageStaticParam($info);
        $uId = $this->userInfo['user_id'];

        $cache = Advertiser_Service_LandingpageModel::getCache(); 
        $key =Advertiser_Service_LandingpageModel::getAddLandingStepKey(1, $uId, intval($info['id']));
        $result = $cache->get($key);
        if (!$result){
            $this->output(1, '操作失败');
        }
        
        $templateinfo  = Advertiser_Service_LandingpagetemplateModel::getBy(array('id'=>$result['template_id']));
        $template_url = $templateinfo['url'];
//        $appInfo = MobgiApi_Service_AdAppModel::getByID($result['app_id']);
//        $folder = Util_Pinyin::getShortPinyin($appInfo['app_name']);
//        $folder = 'mhhy';
//        $attachUrl = Common::getAttachUrl();
//        $template_url = $attachUrl.'/landingpage/'.$folder.'/'.$result['template_id'].'/index.html';
        if($info['id']){
            $template_url = $template_url."?id=".$info['id'];
        }
        $info['template_url'] = $template_url;
        
        $landingpageData = $this->fillLandingpageData($result, $info);
        
        if($info['id']){
            $return = Advertiser_Service_LandingpageModel::update($landingpageData, $info['id']);
        }else{
            $lastInsertId = Advertiser_Service_LandingpageModel::add($landingpageData);
            if(empty($lastInsertId)){
                $this->output(1, '操作失败');
            }
            $template_url = $template_url."?id=".$lastInsertId;
            $updateData = array('template_url'=>$template_url);
            $return = Advertiser_Service_LandingpageModel::update($updateData, $lastInsertId);
        }
        
        if($return){
            //清理缓存
            Advertiser_Service_LandingpageModel::deleteAddLandingStepKey($key);
            $this->output(0, '操作成功');
        }else{
            $this->output(1, '操作失败');
        }
        
    }
    
    /**
     * 静态页模板参数检测
     * @param type $info
     */
    public function checkLandingpageStaticParam($info){
        if(!empty($info['template_data'])){
            $template_data = html_entity_decode($info['template_data']);
        }else{
            $template_data = $info['template_data'];
        }
        if($template_data){
            if (! Common::is_json ( $template_data )) {
                $this->output ( 1, '模板参数格式错误' );
            }
            $template_data_arr = json_decode($template_data, true);

            if($template_data_arr['downModule']['DownLink']){
                $info['url'] = $template_data_arr['downModule']['DownLink'];
            }
            if($template_data_arr['pageModule']['pageTitle']){
                $info['title'] = $template_data_arr['pageModule']['pageTitle'];
            }
        }
        
        if(empty(trim($info['title']))){
            $this->output(1, '请填写页面标题');
        }
        $info['title'] = trim($info['title']);
        if(empty(trim($info['url']))){
            $this->output(1, '请填写下载地址');
        }
        $info['url'] = trim($info['url']);
        return $info;
    }
    
    /**
     * 填充参数
     * @param type $result
     * @param type $info
     * @return type
     */
    private function fillLandingpageData($result, $info){
        $data = array();
        $data['app_id'] = $result['app_id'];
        $data['title'] = $info['title'];
        $data['url'] = $info['url'];
        $data['status'] = $info['status']?$info['status']:1;
        $data['template_data'] = $info['template_data']?$info['template_data']:'';
        $data['template_type'] = $result['template_type'];
        $data['template_id'] = $result['template_id'];
        $data['template_url'] = $info['template_url'];
        return $data;
    }
    
    public function uploadImgAction() {
	    $imgId = $this->getInput('imgId');
	    $this->assign('imgId', $imgId);
	    $this->getView()->display('common/uploadLandingpage.phtml');
	    exit;
	}
	

    /**
     * 返回json数据
     */
	public function uploadImgPostAction() {
	    $imgId = $this->getInput('imgId');
        $tmpimage = $_FILES['img']['tmp_name'];
        $isUpload = true;
        $allowFileType= array('gif','jpeg','jpg','png','bmp');
        $maxSize= 2048;//单位为K
        $ext = strtolower(substr(strrchr($_FILES['img']['name'], '.'), 1));
        if(in_array($ext, $allowFileType)){
            list($width, $height) = getimagesize($tmpimage);
            if($isUpload){
                $ret = Common::upload('img', 'delivery', array('allowFileType'=>$allowFileType,'maxSize'=>$maxSize));
            }
        }else{
            $ret['code'] = -1;
            $ret['data'] = '';
            $ret['msg'] = '图片格式不符合';
        }

	    $this->assign('code' , $ret['code']);
	    $this->assign('msg' , $ret['msg']);
	    $this->assign('filepath', $ret['data']);
        $this->assign('url', Common::getAttachPath () . $ret['data']);
	    $this->assign('imgId', $imgId);
        $this->assign('fileType', 'img');
        $this->assign('width', $width);
        $this->assign('height', $height);
	    $this->getView()->display('common/uploadLandingpage.phtml');
	    exit;
	}
    
    
    /**
     * ajax 拉取静态模板列表
     */
    public function getStaticTemplateAction(){
        $result = array();
        $result['code'] = 0;
        $result['msg'] = '';
        $result['data'] = array();
        
        $app_id = $this->getInput('app_id');
        $appInfo = MobgiApi_Service_AdAppModel::getByID($app_id);
        if(empty($appInfo)){
            $result['code'] = 1;
            $result['msg'] = '应用不存在';
        }
        $staticTemplate = Advertiser_Service_LandingpagetemplateModel::getsBy(array('type'=>Advertiser_Service_LandingpagetemplateModel::STATIC_TEMPLATE, 'app_id'=>$app_id));
        $result['data'] = $staticTemplate;
        echo json_encode($result, true);
        exit();
    }
    
}