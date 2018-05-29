<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
/**
 * Created by PhpStorm.
 * User: kyle.ke
 * Date: 2017/12/4
 * Time: 15:08
 */

class Spm_AdvertiserController extends Admin_BaseController{

    public function init() {
        parent::init();
        $module = $this->getTopModule();
        $this->assign('module', $module);
    }

    public $actions = [
        'reportUrl' => '/Admin/Spm_Advertiser/report',
        'androidReportUrl' => '/Admin/Spm_Advertiser/androidReport',
    ];

    public function reportAction(){

    }

    public function androidReportAction(){

    }

}