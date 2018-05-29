<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
/**
 * Created by PhpStorm.
 * User: kyle.ke
 * Date: 2018/3/12
 * Time: 15:40
 */
class Marketing_CommonController extends Admin_MarketingController {

    /**
     * 获取mkt api所有枚举类型
     */
    public function getEnumAction(){
        $this->output(0, '', $this->marketingConfig);
    }

}