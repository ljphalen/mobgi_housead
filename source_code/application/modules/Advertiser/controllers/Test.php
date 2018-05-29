<?php

/**
 * @Encoding      :   UTF-8
 * @Author       :   hunter.fang
 * @Email         :   782802112@qq.com
 * @Time          :   2016-10-18 20:22:19
 * $Id: Test.php 62100 2016-10-18 20:22:19Z hunter.fang $
 */

if (!defined('BASE_PATH')) exit('Access Denied!');
/**
 * 
 * Enter description here ...
 * @author rock.luo
 *
 */
class TestController extends Advertiser_BaseController {
    
    /**
	 * 
	 * Enter description here ...
	 */
	public function indexAction() {
        $name = $this->getInput('name');
        $action = $this->getInput('action');
        //http://rock.advertiser.housead.com/Advertiser/test/index?name=adgroup&action=select
        // http://rock.advertiser.housead.com/Advertiser/test/index?name=campaign&action=select 获取推广计划
        // http://rock.advertiser.housead.com/Advertiser/test/index?name=utility&action=get_creative_template_refs 获取创意元素
        // http://rock.advertiser.housead.com/Advertiser/test/index?name=creative&action=select 获取创意元素
        // 获取创意列表　
//        var_dump(Advertiser_Service_GdtdirectconfigModel::isGdtDirect($this->userInfo['advertiser_uid']));
//        var_dump(Advertiser_Service_GdtdirectconfigModel::getGdtDirectToken($this->userInfo['advertiser_uid']));
//        var_dump(time(), date("Y-m-d H:i:s"));
//        var_dump(Advertiser_Service_GdtdirectconfigModel::curl($this->userInfo['advertiser_uid'], 'advertiser', 'read', array('advertiser_id'=>'51957')));
//        var_dump(Advertiser_Service_GdtdirectconfigModel::curl($this->userInfo['advertiser_uid'], 'account', 'select', array('advertiser_id'=>'51957')));
//        echo json_encode(Advertiser_Service_GdtdirectconfigModel::curl($this->userInfo['advertiser_uid'], 'adgroup', 'select', array('advertiser_id'=>'51957')));
        if($name && $action){
//            echo json_encode(Advertiser_Service_GdtdirectconfigModel::curl($this->userInfo['advertiser_uid'], $name, $action, array()));
            echo json_encode(Advertiser_Service_GdtdirectconfigModel::curl($this->userInfo['advertiser_uid'], $name, $action, array('advertiser_id'=>'51957')));
//            echo json_encode(Advertiser_Service_GdtdirectconfigModel::curl($this->userInfo['advertiser_uid'], $name, $action, array('advertiser_id'=>'51957','site_set'=>'SITE_SET_MOBILE_INNER','product_type'=>'PRODUCT_TYPE_LINK')));
//            http://sandbox.api.e.qq.com/ads/v3/utility/get_creative_template_refs?token=NTE5NTksNTE5NTksMTQ3ODI0MjI3Nyw1MjBjMGU4MzhlMmE4MDdiNmNhZmE4YzI1ZDkyNDU1N2I2MTcxMWFl&site_set=[%22SITE_SET_MOBILE_INNER%22]&product_type=PRODUCT_TYPE_LINK
        }else{
//            echo json_encode(Advertiser_Service_GdtdirectconfigModel::curl($this->userInfo['advertiser_uid'], 'utility', 'get_app_category_list', array()));
            echo json_encode(Advertiser_Service_GdtdirectconfigModel::curl($this->userInfo['advertiser_uid'], 'image', 'create_by_url', array('advertiser_id'=>'51957', 'image_url'=>'http://dl2.gxpan.cn/ad/delivery/201610/580763dc2ed6e.jpg')));
        }
        //  http://rock.advertiser.housead.com/Advertiser/test/index?name=utility&action=get_app_category_list
        //  http://rock.advertiser.housead.com/Advertiser/test/index?name=utility&action=get_creative_template_refs
//        echo json_encode(Advertiser_Service_GdtdirectconfigModel::curl($this->userInfo['advertiser_uid'], 'utility', 'get_creative_template_refs', array()));
//        utility/get_creative_template_refs
        die();
	}
    
}

