<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
/**
 * Created by PhpStorm.
 * User: kyle.ke
 * Date: 2017/8/11
 * Time: 15:35
 */
class MobgiWww_Dao_SdkInfoModel extends Common_Dao_Base{
    public  $adapter = 'mobgiWww';
    protected $_name = 'sdk_info';
    protected $_primary = 'id';


}