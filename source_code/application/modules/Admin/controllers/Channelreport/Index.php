<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
/**
 * Created by PhpStorm.
 * User: kyle.ke
 * Date: 2018/2/26
 * Time: 18:05
 */
class Channelreport_IndexController extends Admin_BaseController{

    public $actions = [
        'indexUrl' => '/Admin/Channelreport_Index/index',
    ];

    public function indexAction(){
        $this->redirect('/channelreport/');
    }
}