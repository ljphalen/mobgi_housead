<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
/**
 * Created by PhpStorm.
 * User: kyle.ke
 * Date: 2017/12/4
 * Time: 15:05
 */

class Spm_AntiCheatController extends Admin_BaseController{

    public function init() {
        parent::init();
        $module = $this->getTopModule();
        $this->assign('module', $module);
    }

    public $actions = [
        'configUrl' => '/Admin/Spm_AntiCheat/config',
        'reportUrl' => '/Admin/Spm_AntiCheat/report',
        'whiteListUrl' => '/Admin/Spm_AntiCheat/whiteList',
    ];

    public function configAction(){

    }

    public function reportAction(){

    }

    public function whiteListAction(){

    }

}