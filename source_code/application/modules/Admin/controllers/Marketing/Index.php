<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
/**
 * Created by PhpStorm.
 * User: kyle.ke
 * Date: 2018/3/14
 * Time: 15:01
 */
class Marketing_IndexController extends Admin_MarketingController{

    public $actions = [
        'indexUrl' => '/Admin/Marketing_Index/index',
    ];

    public function indexAction(){
        $this->redirect('/marketing/');
    }
}