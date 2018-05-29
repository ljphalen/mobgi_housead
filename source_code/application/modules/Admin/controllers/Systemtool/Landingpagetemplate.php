<?php

/**
 * @Encoding      :   UTF-8
 * @Author       :   hunter.fang
 * @Email         :   782802112@qq.com
 * @Time          :   2017-10-9 17:09:07
 * $Id: Landingpagetemplate.php 62100 2017-10-9 17:09:07Z hunter.fang $
 */
if (!defined('BASE_PATH')) exit('Access Denied!');

class Systemtool_LandingpagetemplateController extends Admin_BaseController {
    public $perpage = 10;
	
	public $actions = array(
        'listUrl' => '/Admin/Systemtool_Landingpagetemplate/list',
        'addUrl' => '/Admin/Systemtool_Landingpagetemplate/add',
        'addPostUrl' => '/Admin/Systemtool_Landingpagetemplate/addPost',
        'addStaticUrl' => '/Admin/Systemtool_Landingpagetemplate/addStatic',
        'addStaticPostUrl' => '/Admin/Systemtool_Landingpagetemplate/addStaticPost',
        'uploadUrl' => '/Admin/Systemtool_Landingpagetemplate/uploadImg',
	    'uploadPostUrl' => '/Admin/Systemtool_Landingpagetemplate/uploadImgPost',
        'uploadTemplateZipUrl' => '/Admin/Systemtool_Landingpagetemplate/uploadTemplateZip',
	    'uploadTemplateZipPostUrl' => '/Admin/Systemtool_Landingpagetemplate/uploadTemplateZipPost',
        
	);
    
    /**
     * 落地页模板列表
     */
    public function listAction() {
        $params = $this->getInput(array('screatedate', 'ecreatedate',  'app_id', 'name', 'type', 'page'));
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
        if (isset ( $params ['app_id'] ) && $params ['app_id']) {
			$whereArr ['app_id'] =  $params ['app_id'] ;
            $pageParams ['app_id'] =  $params ['app_id'] ;
		}
		if ($params ['name']) {
			$whereArr ['name'] = array('like', trim($params ['name']));
            $pageParams ['name'] =  trim($params ['name']) ;
		}
        if ($params ['type']) {
			$whereArr ['type'] = intval($params ['type']);
            $pageParams ['type'] = intval($params ['type']);
		}
        
        list($total, $list) = Advertiser_Service_LandingpagetemplateModel::getList($page, $this->perpage, $whereArr, array('update_time'=>'DESC'));
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
     * 新建落地页模板
     */
    public function addAction() {
        $info = $this->getInput(array('id','action'));
        
        list($appTotal, $appList) = MobgiApi_Service_AdAppModel::getList(1, 999999, array(), array());
        $appList = common::resetKey($appList, 'app_id');
        $this->assign('appList', $appList);
        
        if(!$info['id']){
            $result = array();
        }else{
            $result =  Advertiser_Service_LandingpagetemplateModel::getBy(array('id'=>intval($info['id'])));
        }
     
	    $this->assign('result', $result);
    }
    
    public function addPostAction(){
        $info = $this->getInput(array('id', 'type', 'app_id', 'name', 'zip', 'create_name', 'create_zip' ));
        $info = $this->checkAddpostParam($info);
        if($info['type'] == 2){
            $appInfo = MobgiApi_Service_AdAppModel::getByID($info['app_id']);
            $folder = Util_Pinyin::getShortPinyin($appInfo['app_name']);
//            $folder = 'mhhy';
        }else{
            $folder = 'common';
        }
        $en_name = Util_Pinyin::getShortPinyin($info['name']);
        //zip包解压
        $attachPath = Common::getConfig('siteConfig', 'attachPath');
        $unzipfile = sprintf('%s/%s', $attachPath, $info['zip']);
	    $unziptarget = sprintf('%s/%s/%s', $attachPath, 'landingpage/'.$folder, date('Ymd'));
        
        $zipObj = new ZipArchive();
        if ($zipObj->open($unzipfile)=== TRUE)
        {
            $zipReturn = $zipObj->extractTo($unziptarget);//假设解压缩到在当前路径下images文件夹的子文件夹php
            $zipObj->close();//关闭处理的zip文件
        }
        if(empty($zipReturn)){
            $this->output(1, 'zip文件解压失败');
        }
        
        //服务端检测到文件夹名称是否含有bom头，如果有，则自动重命名
        if ($dh = opendir($unziptarget)) {
            while (($file = readdir($dh)) !== false) {
                if ($file != '.' && $file != '..' && is_dir($unziptarget . "/" . $file)){
                    //检测到bom头
                    if(ord($file[0])== 239 && ord($file[1])== 187 && ord($file[2]) == 191){
                        $nobomfile = substr($file,3);
                        rename($unziptarget . "/" . $file, $unziptarget . "/" . $nobomfile);
                    }else{
                        $nobomfile = $file;
                    }
                }
            }
            closedir($dh);
        }
        
        //上传到CDN
        $unziptargetDir = $unziptarget . "/" .$nobomfile;
        $unziptargetRelativeDir = '/landingpage/'.$folder.'/'.date('Ymd').'/'. $nobomfile;
        common::syncToCdnDir($unziptargetDir, $unziptargetRelativeDir);
//        $cdnFilePathResponse = Common::syncToCdn ( $cdnFilePath );
        //动态模板createzip包解压
        if($info['type'] == 1){
            $uncreatezipfile = sprintf('%s/%s', $attachPath, $info['create_zip']);
            $uncreateziptarget = sprintf('%s/%s', $attachPath, 'landingpage/'.$folder);

            $createzipObj = new ZipArchive();
            if ($createzipObj->open($uncreatezipfile)=== TRUE)
            {
                $createzipReturn = $createzipObj->extractTo($uncreateziptarget);//假设解压缩到在当前路径下images文件夹的子文件夹php
                $createzipObj->close();//关闭处理的zip文件
            }
            if(empty($createzipReturn)){
                $this->output(1, 'create_zip文件解压失败');
            }
            
            //服务端检测到文件夹名称是否含有bom头，如果有，则自动重命名
            if ($dh = opendir($uncreateziptarget)) {
                while (($createfile = readdir($dh)) !== false) {
                    if ($createfile != '.' && $createfile != '..' && is_dir($uncreateziptarget . "/" . $createfile)){
                        //检测到bom头
                        if(ord($createfile[0])== 239 && ord($createfile[1])== 187 && ord($createfile[2]) == 191){
                            $nobomcreatefile = substr($createfile,3);
                            rename($uncreateziptarget . "/" . $createfile, $uncreateziptarget . "/" . $nobomcreatefile);
                        }else{
                            $nobomcreatefile = $createfile;
                        }
                    }
                }
                closedir($dh);
            }
            
            $attachUrl = Common::getAttachUrl();
            $create_url = $attachUrl.'/landingpage/'.$folder.'/'.$info['create_name'].'/new.html';
            $info['create_url'] = $create_url;
        }
        $actUrl = Common::getActUrl();
        $template_url = $actUrl.'/'.$folder.'/'.date("Ymd").'/'.$en_name.'/index.html';
//        $attachUrl = Common::getAttachUrl();
//        $template_url = $attachUrl.'/landingpage/'.$folder.'/'.date("Ymd").'/'.$en_name.'/index.html';
        
        $info['url'] = $template_url;
        if($info['id']){
            $return = Advertiser_Service_LandingpagetemplateModel::update($info, $info['id']);
        }else{
            $return = Advertiser_Service_LandingpagetemplateModel::add($info);
        }
        
        if($return){
            $this->output(0, '操作成功', $cdnFilePathResponse);
        }else{
            $this->output(1, '操作失败');
        }
    }
    
    public function checkAddpostParam($info){
        if(!in_array($info['type'], array(1,2))){
            $this->output(1, '请选择正确的模板类型');
        }
        if (!preg_match("/^[a-zA-Z0-9]{1,50}$/", $info['name'])) {
            $this->output(1, '模板名称须是1~50字符的由英文字母与数字组成的串');
        }
        if($info['type'] == 2 && empty($info['app_id'])){
            $this->output(1, '静态模板请选择关联应用');
        }
        //动态模板名称必须唯一
        if($info['type'] == 1){
            if(empty($info['id'])){
                $isExistName = Advertiser_Service_LandingpagetemplateModel::getBy(array('type'=>$info['type'], 'name'=>$info['name']));
                if($isExistName){
                    $this->output(1, '已经存在动态模板名称为 '.$info['name']. ' 的模板');
                }
            }
        }
        //静态模板名称与关联的应用必须唯一o
        else if($info['type'] == 2){
            if(empty($info['id'])){
                $isExistName = Advertiser_Service_LandingpagetemplateModel::getBy(array('type'=>$info['type'], 'name'=>$info['name'],'app_id'=>$info['app_id']));
                if($isExistName){
                    $this->output(1, '已经存在该应用静态模板名称为 '.$info['name']. ' 的模板');
                }
            }
        }
        
        if(empty(trim($info['name']))){
            $this->output(1, '请填写模板名称');
        }
        $info['name'] = trim($info['name']);
        if(empty(trim($info['zip']))){
            $this->output(1, '请先上传模板');
        }
        $info['zip'] = trim($info['zip']);
        //动态模板需要上传创建模板名称和创建模板zip压缩包
        if($info['type'] == 1){
            if(empty(trim($info['create_name']))){
            $this->output(1, '请填写创建模板名称');
            }
            $info['create_name'] = trim($info['create_name']);
            if(empty(trim($info['create_zip']))){
                $this->output(1, '请先上传创建模板');
            }
            $info['create_zip'] = trim($info['create_zip']);
            if('create_'.$info['name'] != $info['create_name']){
                $this->output(1, '创建模板名称与模板名称不匹配(create_tname, tname)');
            }
        }
//        if(empty(trim($info['address']))){
//            $this->output(1, '请填写地址');
//        }
//        $info['address'] = trim($info['address']);
        return $info;
    }

    public function uploadTemplateZipAction() {
	    $otherId = $this->getInput('otherId');
	    $this->assign('otherId', $otherId);
	    $this->getView()->display('common/uploadTemplateZip.phtml');
	    exit;
	}
    
	public function uploadTemplateZipPostAction() {
        $name = $_FILES['other']['name']; //e: t7.zip
        //检测到bom头
        if(ord($name[0])== 239 && ord($name[1])== 187 && ord($name[2]) == 191){
            $name = substr($name,3);
            $_FILES['other']['name'] = $name;
        }
        $realname = str_replace(array('.zip', '.ZIP'), array('', ''), $name); //e: t7
	    $ret = Common::upload('other', 'landingpage/zips', array('maxSize'=>512000,'allowFileType'=>array('zip')));
	    $otherId = $this->getInput('otherId');
	    $this->assign('code' , $ret['data']);
	    $this->assign('msg' , $ret['msg']);
	    $this->assign('data', $ret['data']);
	    $this->assign('otherId', $otherId);
        $this->assign('name', $realname);
	    $this->getView()->display('common/uploadTemplateZip.phtml');
	    exit;
	}
    
}
