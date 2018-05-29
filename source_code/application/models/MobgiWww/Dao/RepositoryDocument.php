<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
/**
 * Created by PhpStorm.
 * User: kyle.ke
 * Date: 2017/8/13
 * Time: 17:57
 */
class MobgiWww_Dao_RepositoryDocumentModel extends Common_Dao_Base{
    public  $adapter = 'mobgiWww';
    protected $_name = 'repository_document';
    protected $_primary = 'id';


}