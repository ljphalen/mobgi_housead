<?php

/**
 * @Encoding      :   UTF-8
 * @Author       :   hunter.fang
 * @Email         :   782802112@qq.com
 * @Time          :   2016-12-7 15:06:58
 * $Id: Originality.php 62100 2016-12-7 15:06:58Z hunter.fang $
 */

if (!defined('BASE_PATH')) exit('Access Denied!');

class Sona_V1_OriginalityController extends Api_BaseController {
   
    /**
     * 
     * 1.创建创意
     * originality/create
     * method: post
     * 插页
     * curl 'http://rock.advertiser.housead.com/v1/originality/create' -H 'Authorization: Bearer NCwxNDgyNzMzNjMzLDAxOTI5ZWUxYTFkNTI0NTg1YmJjYmMzYmMzZmExYTJlODA2ZGMxYmI=' -d 'advertiser_id=4' -d 'ad_id=50' -d 'originality_name=originality_name_hunter074' -d 'originality_conf_id=2' -d 'originality_status=1' -d 'originality_content={"icon":"4:4d7d24782ac1e2df763d3658a9209212","cross_img":"4:601690dfb3a9d5ccb367fc85f7ee2296","vertical_img":"4:9da12013a6a18271f97f50021a349387"}'
     * {"code":0,"msg":"","data":{"originality_id":"55","outer_originality_id":0}}
     * 视频
     * curl 'http://rock.advertiser.housead.com/v1/originality/create' -H 'Authorization: Bearer NCwxNDgyNzMzNjMzLDAxOTI5ZWUxYTFkNTI0NTg1YmJjYmMzYmMzZmExYTJlODA2ZGMxYmI=' -d 'advertiser_id=4' -d 'ad_id=50' -d 'originality_name=originality_name_hunter077' -d 'originality_conf_id=1' -d 'originality_status=1' -d 'originality_content={"icon":"4:4d7d24782ac1e2df763d3658a9209212","cross_img":"4:601690dfb3a9d5ccb367fc85f7ee2296","vertical_img":"4:9da12013a6a18271f97f50021a349387","video":"4:0a5da25e140c1f782fa3f4d04f4854b7","title":"testtitle","desc":"testdesc"}'
     * {"code":0,"msg":"","data":{"originality_id":"56","outer_originality_id":0}}
     */
    public function createAction() {
        $advertiser_id = $this->getPost('advertiser_id');
        if(empty($advertiser_id)){
            $this->sonaOutput(31000, 'advertiser_id:lack required parameters');
        }
        $this->checkSonaToken($advertiser_id);
        
        $deliveryConfig = Common::getConfig('deliveryConfig');
        
        //必填,广告id
        $ad_id = intval($this->getPost('ad_id'));
        if(empty($ad_id)){
            $this->sonaOutput(31001, 'ad_id:parameter value should not be empty');
        }
        $adInfo = Dedelivery_Service_AdConfListModel::getBy(array('account_id'=>$advertiser_id, 'id'=>$ad_id));
        if(empty($adInfo)){
            $this->sonaOutput(31010, 'ad_id:object operated not exist');
        }
        
        $unit_id = $adInfo['unit_id'];
        
        //必填,创意名称
        $originality_name = trim($this->getPost('originality_name'));
        if(!$originality_name){
	        $this->sonaOutput(31001, 'originality_name:parameter value should not be empty');
	    }
	    if(Common::strLength($originality_name) >= 30){
	        $this->sonaOutput(31007, 'originality_name:string length is too long');
	    }
        $params = array();
	    $params['title'] = $originality_name;
	    $params['account_id'] = $advertiser_id;
	    $ret = Dedelivery_Service_OriginalityRelationModel::getBy($params);
	    if($ret){
	        $this->output(31013, 'originality_name:duplicate name is not allowed');
	    }
        
        //必填, 创意类型 1=>'IOS插页', 2=>'IOS视频', 3=>'安卓插页', 4=>'安卓视频'
        $originalityConfLlist = Advertiser_Service_OriginalityConfModel::getsBy(array('is_delete'=>Common_Service_Const::NOT_DELETE_FLAG));
        $originalityConfLlist = common::resetKey($originalityConfLlist, 'originality_type');
        $originality_conf_id = intval($this->getPost('originality_conf_id'));
        if(empty($originality_conf_id)){
            $this->sonaOutput(31001, 'originality_conf_id:parameter value should not be empty');
        }
        if(!isset($originalityConfLlist[$originality_conf_id])){
            $this->sonaOutput(31017, 'originality_type:param not in enumeration list');
        }
        
        $originality_type = $originalityConfLlist[$originality_conf_id]['originality_type'];
        
        //必填, 上传内容
        $originality_content_str = html_entity_decode($this->getPost('originality_content'));
        if(!$originality_content_str){
            $this->sonaOutput(31001, 'originality_content:parameter value should not be empty');
        }
        if(Common::is_json($originality_content_str)){
            $originality_content = json_decode($originality_content_str, TRUE);
        }else{
            $this->sonaOutput(31005, 'originality_content:invalid data format');
        }
        if(!is_array($originality_content)){
            $this->sonaOutput(31005, 'originality_content:invalid data format');
        }
        
        //originality_content结构体内参数, 验证icon
        if(isset($originality_content['icon'])){
            $icon = $originality_content['icon'];
        }else{
            $icon = '';
        }
        if(!$icon){
            $this->sonaOutput(31001, 'originality_content icon:parameter value should not be empty');
        }
        $iconInfo = Advertiser_Service_ImageModel::getByImageid($icon);
        if($iconInfo){
            if($iconInfo['width'] != 100){
                $this->sonaOutput(32520, 'icon width:image size do not meet the requirement ');
            }
            if($iconInfo['height'] != 100){
                $this->sonaOutput(32520, 'icon height:image size do not meet the requirement ');
            }
        }else{
            $this->sonaOutput(31010, 'icon:object operated not exist');
        }
        
        //originality_content结构体内参数, 验证横屏图片
        if(isset($originality_content['cross_img'])){
            $cross_img = $originality_content['cross_img'];
        }else{
            $cross_img = '';
        }
        if(empty($cross_img)){
            $this->sonaOutput(31001, 'originality_content cross_img:parameter value should not be empty');
        }
        $crossimageInfo = Advertiser_Service_ImageModel::getByImageid($cross_img);
        if($crossimageInfo){
            if($crossimageInfo['width'] != 960){
                $this->sonaOutput(32520, 'cross_img width:image size do not meet the requirement ');
            }
            if($crossimageInfo['height'] != 640){
                $this->sonaOutput(32520, 'cross_img height:image size do not meet the requirement ');
            }
        }else{
            $this->sonaOutput(31010, 'cross_img:object operated not exist');
        }
        
        //originality_content结构体内参数, 验证竖屏图片
        if(isset($originality_content['vertical_img'])){
            $vertical_img = $originality_content['vertical_img'];
        }else{
            $vertical_img = '';
        }
        if(empty($vertical_img)){
            $this->sonaOutput(31001, 'originality_content vertical_img:parameter value should not be empty');
        }
        $verticalimageInfo = Advertiser_Service_ImageModel::getByImageid($vertical_img);
        if($verticalimageInfo){
            if($verticalimageInfo['width'] != 640){
                $this->sonaOutput(32520, 'vertical_img width:image size do not meet the requirement ');
            }
            if($verticalimageInfo['height'] != 960){
                $this->sonaOutput(32520, 'vertical_img height:image size do not meet the requirement ');
            }
        }else{
            $this->sonaOutput(31010, 'vertical_img:object operated not exist');
        }

        
	    if($originality_type == Common_Service_Const::VIDEO_AD_SUB_TYPE){
            //originality_content结构体内参数, 验证视频
            if(isset($originality_content['video'])){
                $video = $originality_content['video'];
            }else{
                $video = '';
            }
            if(empty($video)){
                $this->sonaOutput(31001, 'originality_content video:parameter value should not be empty');
            }
            
            if(isset($originality_content['title'])){
                $title = $originality_content['title'];
            }else{
                $title = '';
            }
            if(empty($title)){
                $this->sonaOutput(31001, 'originality_content title:parameter value should not be empty');
            }
            if(Common::strLength($title) >= 30){
                $this->sonaOutput(31007, 'originality_content title:string length is too long');
            }
            
            if(isset($originality_content['desc'])){
                $desc = $originality_content['desc'];
            }else{
                $desc = '';
            }
            if(empty($desc)){
                $this->sonaOutput(31001, 'originality_content desc:parameter value should not be empty');
            }
            if(Common::strLength($desc) >= 30){
                $this->sonaOutput(31007, 'originality_content desc:string length is too long');
            }
            
            
            $videoInfo = Advertiser_Service_VideoModel::getByVideoid($video);
            if($videoInfo){
                if(!in_array($videoInfo['file_format'], array('mp4','avi'))){
                    $this->sonaOutput(32507, 'video width:video type is error');
                }
                if($videoInfo['size'] > 3*1024*1024){
                    $this->sonaOutput(32508, 'video size:video size cannot match the require of the creative size');
                }
            }else{
                $this->sonaOutput(31010, 'video:object operated not exist');
            }
            
            $zipInfo = array();
            $attachPath = common::getAttachPath();
            $zipInfo['h5template'] = 2;//单图模式
            $zipInfo['mainpic'] = $attachPath . $verticalimageInfo['url'];
            $zipInfo['iconpic'] = $attachPath . $iconInfo['url'];
            $zipInfo['videotitle'] = $title;
            $zipInfo['videodesc'] = $desc;
            $zipInfo['commentnum'] = 454;
            $zipInfo['buttonvalue'] = '免费下载';
            $zipReturn = Common::createZip($zipInfo);
	    }
        
        //非必填, 投放状态 1投放2暂停;
        $originality_status = intval($this->getPost('originality_status'));
        if($originality_status && !isset($deliveryConfig['originalityStatus'][$originality_status])){
            $this->sonaOutput(31017, 'originality_status:param not in enumeration list');
        }
        
        //非必填, 调用方创意id
        $outer_originality_id = intval($this->getPost('outer_originality_id'));
        if($outer_originality_id){
            if(Dedelivery_Service_OriginalityRelationModel::getBy(array('account_id'=>$advertiser_id, 'outer_originality_id'=>$outer_originality_id))){
                $this->sonaOutput(31027, 'outer_originality_id:client outer key mapping created before service');
            }
        }
        
        $info = array();
        $info['originality_conf_id'] = $originality_conf_id;
        $info['ad_id'] = $ad_id;
        $info['unit_id'] = $unit_id;
        $info['title'] = $originality_name;
        $info['desc'] = '';
        $info['originality_type'] = $originality_type;
        $upload_content_arr = array();
        $upload_content_arr['isSona'] = 1;
        $upload_content_arr['icon'] = $iconInfo['url'];
        if($originality_type == Common_Service_Const::PIC_AD_SUB_TYPE){
            $upload_content_arr['cross_img'] = $crossimageInfo['url'];
            $upload_content_arr['vertical_img'] = $verticalimageInfo['url'];
            $upload_content = json_encode($upload_content_arr, true);
        }else if($originality_type == Common_Service_Const::VIDEO_AD_SUB_TYPE){
            $upload_content_arr['video'] = $videoInfo['url'];
            $upload_content_arr['h5'] = $zipReturn['h5'];
            $upload_content = json_encode($upload_content_arr, true);
        }
        $info['upload_content'] = $upload_content;
        $info['account_id'] = $advertiser_id;
        if($originality_status){
            $info['status'] = $originality_status==1?1:4;;
        }else{
            $info['status'] = 1;
        }
        $info['outer_originality_id'] = $outer_originality_id;
        $originality_id = Dedelivery_Service_OriginalityRelationModel::add($info);
	    if (!$originality_id){
	        $this->sonaOutput(30000, 'unknown internal error, please try again later');
	    }
        
        /*操作日志start*/
        $data = array();
        $data['module'] = 'adver_delivery';
        $data['sub_module'] = 'add_originality';
        $data['content'].=$originality_id.',title:'.$originality_name.',ad_id:'.$ad_id;
        $this->addSonaOperatelog($advertiser_id, $data);
        /*操作日志end*/
        
	    $this->sonaOutput(0, '',array('originality_id'=>$originality_id,'outer_originality_id'=>$outer_originality_id));
    }
    
    /**
     * 2.读取创意
     * originality/read
     * method: get
     * curl -G 'http://rock.advertiser.housead.com/v1/originality/read' -H 'Authorization: Bearer NCwxNDgxNzcwNzY1LGM5MDEwMTIzMjkyYjgzOTdhMDkxNDI3MjM5YmIxNGY4ZjA3N2ExZTk=' -d 'advertiser_id=4' -d 'originality_id=47'
     * {"code":0,"msg":"","data":{"originality_id":"47","unit_id":null,"originality_name":"originality_name_hunter05","originality_conf_id":"2","originality_content":"{\"isSona\":1,\"icon\":\"icon111\",\"video\":\"video111\",\"h5\":\"h5111\"}","originality_status":1,"outer_originality_id":"5","create_time":1481782503,"update_time":1481782507}}
     */
    public function readAction() {
        $advertiser_id = $this->getGet('advertiser_id');
        if(empty($advertiser_id)){
            $this->sonaOutput(31000, 'advertiser_id:lack required parameters');
        }
        $this->checkSonaToken($advertiser_id);
        
        $originality_id = intval($this->getGet('originality_id'));
        if(empty($originality_id)){
            $this->sonaOutput(31000, 'originality_id:lack required parameters');
        }
        $originalityInfo = Dedelivery_Service_OriginalityRelationModel::getBy(array('account_id'=>$advertiser_id, 'id'=>$originality_id));
        if(empty($originalityInfo)){
            $this->sonaOutput(31010, 'originality_id:object operated not exist');
        }
        
        $outerOriginalityInfo = array();
        $outerOriginalityInfo['originality_id'] = $originalityInfo['id'];
        $outerOriginalityInfo['unit_id'] = $originalityInfo['unit'];
        $outerOriginalityInfo['originality_name'] = $originalityInfo['title'];
        $outerOriginalityInfo['originality_conf_id'] = $originalityInfo['originality_conf_id'];
        $outerUploadInfo = json_decode($originalityInfo['upload_content'], true);
        unset($outerUploadInfo['isSona']);
        $outerOriginalityInfo['originality_content'] = json_encode($outerUploadInfo);
        $outerOriginalityInfo['originality_status'] = $originalityInfo['status']==1?1:2;
        $outerOriginalityInfo['outer_originality_id'] = $originalityInfo['outer_originality_id'];
        $outerOriginalityInfo['create_time'] = strtotime($originalityInfo['create_time']);
        $outerOriginalityInfo['update_time'] = strtotime($originalityInfo['update_time']);
        $this->sonaOutput(0, '', $outerOriginalityInfo);
    }
    
    /**
     * 
     * 3.更新创意
     * originality/update
     * method: post
     * 插页
     * curl 'http://rock.advertiser.housead.com/v1/originality/update' -H 'Authorization: Bearer NCwxNDgyNzMzNjMzLDAxOTI5ZWUxYTFkNTI0NTg1YmJjYmMzYmMzZmExYTJlODA2ZGMxYmI=' -d 'advertiser_id=4' -d 'originality_id=59' -d 'originality_name=originality_name_hunter084' -d 'originality_conf_id=2' -d 'originality_status=1' -d 'originality_content={"icon":"4:4d7d24782ac1e2df763d3658a9209212","cross_img":"4:601690dfb3a9d5ccb367fc85f7ee2296","vertical_img":"4:9da12013a6a18271f97f50021a349387"}'
     * {"code":0,"msg":"","data":{"originality_id":59}}
     * 视频
     * curl 'http://rock.advertiser.housead.com/v1/originality/update' -H 'Authorization: Bearer NCwxNDgyNzMzNjMzLDAxOTI5ZWUxYTFkNTI0NTg1YmJjYmMzYmMzZmExYTJlODA2ZGMxYmI=' -d 'advertiser_id=4' -d 'originality_id=59' -d 'originality_name=originality_name_hunter083' -d 'originality_conf_id=1' -d 'originality_status=1' -d 'originality_content={"icon":"4:4d7d24782ac1e2df763d3658a9209212","cross_img":"4:601690dfb3a9d5ccb367fc85f7ee2296","vertical_img":"4:9da12013a6a18271f97f50021a349387","video":"4:0a5da25e140c1f782fa3f4d04f4854b7","title":"testtitle","desc":"testdesc"}'
     * {"code":0,"msg":"","data":{"originality_id":59}}
     */
    public function updateAction() {
        $advertiser_id = $this->getPost('advertiser_id');
        if(empty($advertiser_id)){
            $this->sonaOutput(31000, 'advertiser_id:lack required parameters');
        }
        $this->checkSonaToken($advertiser_id);
        
        $deliveryConfig = Common::getConfig('deliveryConfig');
        
        //必填,创意id
        $originality_id = intval($this->getPost('originality_id'));
        if(empty($originality_id)){
            $this->sonaOutput(31001, 'originality_id:parameter value should not be empty');
        }
        $originalityInfo = Dedelivery_Service_OriginalityRelationModel::getBy(array('account_id'=>$advertiser_id, 'id'=>$originality_id));
        if(empty($originalityInfo)){
            $this->sonaOutput(31010, 'originality_id:object operated not exist');
        }
        
        //必填,创意名称
        $originality_name = trim($this->getPost('originality_name'));
        if(!$originality_name){
	        $this->sonaOutput(31001, 'originality_name:parameter value should not be empty');
	    }
	    if(Common::strLength($originality_name) >= 30){
	        $this->sonaOutput(31007, 'originality_name:string length is too long');
	    }
        $params = array();
	    $params['title'] = $originality_name;
	    $params['account_id'] = $advertiser_id;
        $params['id'] = array('!=',$originality_id);
	    $ret = Dedelivery_Service_OriginalityRelationModel::getBy($params);
	    if($ret){
	        $this->output(31013, 'originality_name:duplicate name is not allowed');
	    }
        
        //必填, 创意类型 1=>'IOS插页', 2=>'IOS视频', 3=>'安卓插页', 4=>'安卓视频'
        $originalityConfLlist = Advertiser_Service_OriginalityConfModel::getsBy(array('is_delete'=>Common_Service_Const::NOT_DELETE_FLAG));
        $originalityConfLlist = common::resetKey($originalityConfLlist, 'originality_type');
        $originality_conf_id = intval($this->getPost('originality_conf_id'));
        if(empty($originality_conf_id)){
            $this->sonaOutput(31001, 'originality_conf_id:parameter value should not be empty');
        }
        if(!isset($originalityConfLlist[$originality_conf_id])){
            $this->sonaOutput(31017, 'originality_type:param not in enumeration list');
        }
        
        $originality_type = $originalityConfLlist[$originality_conf_id]['originality_type'];
        
        //必填, 上传内容
        $originality_content_str = html_entity_decode($this->getPost('originality_content'));
        if(!$originality_content_str){
            $this->sonaOutput(31001, 'originality_content:parameter value should not be empty');
        }
        if(Common::is_json($originality_content_str)){
            $originality_content = json_decode($originality_content_str, TRUE);
        }else{
            $this->sonaOutput(31005, 'originality_content:invalid data format');
        }
        if(!is_array($originality_content)){
            $this->sonaOutput(31005, 'originality_content:invalid data format');
        }
        
        //originality_content结构体内参数, 验证icon
        if(isset($originality_content['icon'])){
            $icon = $originality_content['icon'];
        }else{
            $icon = '';
        }
        if(!$icon){
            $this->sonaOutput(31001, 'originality_content icon:parameter value should not be empty');
        }
        $iconInfo = Advertiser_Service_ImageModel::getByImageid($icon);
        if($iconInfo){
            if($iconInfo['width'] != 100){
                $this->sonaOutput(32520, 'icon width:image size do not meet the requirement ');
            }
            if($iconInfo['height'] != 100){
                $this->sonaOutput(32520, 'icon height:image size do not meet the requirement ');
            }
        }else{
            $this->sonaOutput(31010, 'icon:object operated not exist');
        }
        
	    //originality_content结构体内参数, 验证横屏图片
        if(isset($originality_content['cross_img'])){
            $cross_img = $originality_content['cross_img'];
        }else{
            $cross_img = '';
        }
        if(empty($cross_img)){
            $this->sonaOutput(31001, 'originality_content cross_img:parameter value should not be empty');
        }
        $crossimageInfo = Advertiser_Service_ImageModel::getByImageid($cross_img);
        if($crossimageInfo){
            if($crossimageInfo['width'] != 960){
                $this->sonaOutput(32520, 'cross_img width:image size do not meet the requirement ');
            }
            if($crossimageInfo['height'] != 640){
                $this->sonaOutput(32520, 'cross_img height:image size do not meet the requirement ');
            }
        }else{
            $this->sonaOutput(31010, 'cross_img:object operated not exist');
        }
        
        //originality_content结构体内参数, 验证竖屏图片
        if(isset($originality_content['vertical_img'])){
            $vertical_img = $originality_content['vertical_img'];
        }else{
            $vertical_img = '';
        }
        if(empty($vertical_img)){
            $this->sonaOutput(31001, 'originality_content vertical_img:parameter value should not be empty');
        }
        $verticalimageInfo = Advertiser_Service_ImageModel::getByImageid($vertical_img);
        if($verticalimageInfo){
            if($verticalimageInfo['width'] != 640){
                $this->sonaOutput(32520, 'vertical_img width:image size do not meet the requirement ');
            }
            if($verticalimageInfo['height'] != 960){
                $this->sonaOutput(32520, 'vertical_img height:image size do not meet the requirement ');
            }
        }else{
            $this->sonaOutput(31010, 'vertical_img:object operated not exist');
        }
        

        
	    if($originality_type == Common_Service_Const::VIDEO_AD_SUB_TYPE){
            //originality_content结构体内参数, 验证视频
            if(isset($originality_content['video'])){
                $video = $originality_content['video'];
            }else{
                $video = '';
            }
            if(empty($video)){
                $this->sonaOutput(31001, 'originality_content video:parameter value should not be empty');
            }
            
            if(isset($originality_content['title'])){
                $title = $originality_content['title'];
            }else{
                $title = '';
            }
            if(empty($title)){
                $this->sonaOutput(31001, 'originality_content title:parameter value should not be empty');
            }
            if(Common::strLength($title) >= 30){
                $this->sonaOutput(31007, 'originality_content title:string length is too long');
            }
            
            if(isset($originality_content['desc'])){
                $desc = $originality_content['desc'];
            }else{
                $desc = '';
            }
            if(empty($desc)){
                $this->sonaOutput(31001, 'originality_content desc:parameter value should not be empty');
            }
            if(Common::strLength($desc) >= 30){
                $this->sonaOutput(31007, 'originality_content desc:string length is too long');
            }
            
            
            $videoInfo = Advertiser_Service_VideoModel::getByVideoid($video);
            if($videoInfo){
                if(!in_array($videoInfo['file_format'], array('mp4','avi'))){
                    $this->sonaOutput(32507, 'video width:video type is error');
                }
                if($videoInfo['size'] > 3*1024*1024){
                    $this->sonaOutput(32508, 'video size:video size cannot match the require of the creative size');
                }
            }else{
                $this->sonaOutput(31010, 'video:object operated not exist');
            }
            
            $zipInfo = array();
            $attachPath = common::getAttachPath();
            $zipInfo['h5template'] = 2;//单图模式
            $zipInfo['mainpic'] = $attachPath . $verticalimageInfo['url'];
            $zipInfo['iconpic'] = $attachPath . $iconInfo['url'];
            $zipInfo['videotitle'] = $title;
            $zipInfo['videodesc'] = $desc;
            $zipInfo['commentnum'] = 454;
            $zipInfo['buttonvalue'] = '免费下载';
            $zipReturn = Common::createZip($zipInfo);
	    }
        
        //非必填, 投放状态 1投放2暂停;
        $originality_status = intval($this->getPost('originality_status'));
        if($originality_status && !isset($deliveryConfig['originalityStatus'][$originality_status])){
            $this->sonaOutput(31017, 'originality_status:param not in enumeration list');
        }
        
        $info = array();
        $info['originality_conf_id'] = $originality_conf_id;
        $info['ad_id'] = $ad_id;
        $info['unit_id'] = $unit_id;
        $info['title'] = $originality_name;
        $info['desc'] = '';
        $info['originality_type'] = $originality_type;
        $upload_content_arr = array();
        $upload_content_arr['isSona'] = 1;
        $upload_content_arr['icon'] = $iconInfo['url'];
        if($originality_type == Common_Service_Const::PIC_AD_SUB_TYPE){
            $upload_content_arr['cross_img'] = $crossimageInfo['url'];
            $upload_content_arr['vertical_img'] = $verticalimageInfo['url'];
            $upload_content = json_encode($upload_content_arr, true);
        }else if($originality_type == Common_Service_Const::VIDEO_AD_SUB_TYPE){
            $upload_content_arr['video'] = $videoInfo['url'];
            $upload_content_arr['h5'] = $zipReturn['h5'];
            $upload_content = json_encode($upload_content_arr, true);
        }
        $info['upload_content'] = $upload_content;
        $info['account_id'] = $advertiser_id;
        if($originality_status){
            $info['status'] = $originality_status==1?1:4;;
        }else{
            $info['status'] = 1;
        }
        $result = Dedelivery_Service_OriginalityRelationModel::updateBy($info, array('id'=>$originality_id, 'account_id'=>$advertiser_id));
	    if (!$result){
	        $this->sonaOutput(30000, 'unknown internal error, please try again later');
	    }
        
        /*操作日志start*/
        $logdata = array();
        $logdata['module'] = 'adver_delivery';
        $logdata['sub_module'] = 'edit_originality';
        $logdata['content'].=$originality_id.',title:'.$originality_name;
        $compare_result = common::compare_different($originalityInfo, $info, array('title', 'originality_conf_id', 'upload_content', 'status'));
        $old = $compare_result['left'];
        $new = $compare_result['right'];
        if($old || $new){
            $logdata['content'].=$adInfo['id'].', old:  '.$old.'   new:  '. $new;
        }else{
            $logdata['content'].=$adInfo['id'].', 无更新';
        }
        
        $this->addSonaOperatelog($advertiser_id, $logdata);
        /*操作日志end*/
        
	    $this->sonaOutput(0, '',array('originality_id'=>$originality_id));
    }
    
    /**
     * 4.获取创意列表
     * originality/select
     * method: get
     * curl -G 'http://rock.advertiser.housead.com/v1/originality/select' -H 'Authorization: Bearer NCwxNDgxNzcwNzY1LGM5MDEwMTIzMjkyYjgzOTdhMDkxNDI3MjM5YmIxNGY4ZjA3N2ExZTk=' -d 'advertiser_id=4' -d 'page=1' -d 'page_size=1'
     * {"code":0,"msg":"","data":{"list":[{"id":"47","originality_conf_id":"2","ad_id":"50","unit_id":"22","title":"originality_name_hunter05","desc":"","originality_type":"1","strategy":"0","upload_content":"{\"isSona\":1,\"icon\":\"icon111\",\"video\":\"video111\",\"h5\":\"h5111\"}","create_time":1481782503,"update_time":1481782507,"account_id":"4","status":"1","del":"0","filter_app_conf":null,"weight":"1.0","outer_originality_id":"5"}],"page_info":{"total_num":"6","total_page":6,"page_size":1,"page":1}}}
     */
    public function selectAction() {
        $advertiser_id = $this->getGet('advertiser_id');
        if(empty($advertiser_id)){
            $this->sonaOutput('31000', 'advertiser_id:lack required parameters');
        }
        $this->checkSonaToken($advertiser_id);
        
        $page = intval($this->getGet('page'));
        if($page < 0){
            $this->sonaOutput(31016, 'page:parameter value is too tiny');
        }
        if(empty($page)){
            $page = 1;
        }
        
        $page_size = intval($this->getGet('page_size'));
        if($page_size > $this->maxPageSize){
            $this->sonaOutput(31015, 'page_size:parameter value is too huge');
        }
        if($page_size < 0 ){
            $this->sonaOutput(31016, 'page_size:parameter value is too tiny');
        }
        if($page_size == 0){
            $page_size = $this->perpage;
        }
        
        $params = array();
        $params['account_id'] = $advertiser_id;
        $params['del'] = 0;
        list($total, $originalityList) = Dedelivery_Service_OriginalityRelationModel::getList($page, $page_size, $params);
        if($originalityList){
            foreach($originalityList as $key=>$ad){
                $originalityList[$key]['create_time'] = strtotime($ad['create_time']);
                $originalityList[$key]['update_time'] = strtotime($ad['update_time']);
            }
        }
        $page_info = array('total_num'=>$total, 'total_page'=>ceil($total*1.0/$page_size), 'page_size'=>$page_size, 'page'=>$page );
        
        $this->sonaOutput(0, '',array('list'=>$originalityList, 'page_info'=>$page_info));
    }
    
    /**
     * 5.删除创意
     * originality/delete
     * method: post
     * curl 'http://rock.advertiser.housead.com/v1/originality/delete' -H 'Authorization: Bearer NCwxNDgxNzcwNzY1LGM5MDEwMTIzMjkyYjgzOTdhMDkxNDI3MjM5YmIxNGY4ZjA3N2ExZTk=' -d 'advertiser_id=4' -d 'originality_id=47'
     * {"code":0,"msg":"","data":{"originality_id":47}}
     */
    public function deleteAction() {
        $advertiser_id = $this->getPost('advertiser_id');
        if(empty($advertiser_id)){
            $this->sonaOutput('31000', 'advertiser_id:lack required parameters');
        }
        $this->checkSonaToken($advertiser_id);
        
        //必填,投放单元id
        $originality_id = intval($this->getPost('originality_id'));
        if(empty($originality_id)){
            $this->sonaOutput(31001, 'originality_id:parameter value should not be empty ');
        }
        $originalityInfo = Dedelivery_Service_OriginalityRelationModel::getBy(array('account_id'=>$advertiser_id, 'id'=>$originality_id));
        if(empty($originalityInfo)){
            $this->sonaOutput(31010, 'originality_id:object operated not exist');
        }
        
        $result = Dedelivery_Service_OriginalityRelationModel::updateBy(array('del'=>1), array('id'=>$originality_id, 'account_id'=>$advertiser_id));
	    if (!$result){
	        $this->sonaOutput(30000, 'unknown internal error, please try again later');
	    }
        
        /*操作日志start*/
        $logdata = array();
        $logdata['module'] = 'adver_delivery';
        $logdata['sub_module'] = 'del_originality';
        $logdata['content'] = '';
        $old = '';
        $new = '';
        $old .= 'del:0'.';';
        $new .= 'del:1'.';';
        $logdata['content'].=$originalityInfo['id'].', name:'.$originalityInfo['ad_name']. ', old:  '.$old.'   new:  '. $new;
        $this->addSonaOperatelog($advertiser_id, $logdata);
        /*操作日志end*/
        
        $this->sonaOutput(0, '',array('originality_id'=>$originality_id));
    }
}
