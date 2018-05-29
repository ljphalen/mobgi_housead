<?php

/**
 * @Encoding      :   UTF-8
 * @Author       :   hunter.fang
 * @Email         :   782802112@qq.com
 * @Time          :   2017-9-21 14:26:02
 * $Id: landingpage.php 62100 2017-9-21 14:26:02Z hunter.fang $
 */

if (!defined('BASE_PATH')) exit('Access Denied!');

class LandingpageController extends Api_BaseController {
    
    /**
     * 拉取模板信息
     */
    public function getTemplateDataAction(){
        $id = intval($_GET['id']);
        $landingpageInfo = Advertiser_Service_LandingpageModel::getBy(array('id'=>$id));
        $outputArr = array();
        $outputArr['url'] = $landingpageInfo['url'];
        $outputArr['template_data'] = json_decode($landingpageInfo['template_data']);
        $outputArr['template_data'] = html_entity_decode($outputArr['template_data']);
        $callback = $_GET['callback'];
        echo $callback.'('.json_encode($outputArr).')';
        exit;
    }
    
    
}

