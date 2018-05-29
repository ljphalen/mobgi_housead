<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
/**
 * Created by PhpStorm.
 * User: kyle.ke
 * Date: 2017/8/13
 * Time: 17:02
 */
class MobgiWww_Dao_RepositoryMenuModel extends Common_Dao_Base{
    public  $adapter = 'mobgiWww';
    protected $_name = 'repository_menu';
    protected $_primary = 'id';


}