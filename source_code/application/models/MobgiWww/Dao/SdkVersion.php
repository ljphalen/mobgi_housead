<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
/**
 * Created by PhpStorm.
 * User: kyle.ke
 * Date: 2017/8/11
 * Time: 19:20
 */
class MobgiWww_Dao_SdkVersionModel extends Common_Dao_Base{
    public  $adapter = 'mobgiWww';
    protected $_name = 'sdk_version';
    protected $_primary = 'id';


}