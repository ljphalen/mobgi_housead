<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
/**
 * 
 * Enter description here ...
 * @author rock.luo
 *
 */
class MobgiApi_Service_AdDeverPosModel{
	const DEL_FLAG = -1;
	const NOT_DEL_FLAG = 1;
	const  OPEN_STATUS = 1;
	const  CLOSE_STATUS = 0;


	/**
	 * 
	 * Enter description here ...
	 */
	public static function getAll() {
		return array(self::_getDao()->count(), self::_getDao()->getAll());
	}
	
	public static function getCount($params){
		if (!is_array($params)) return false;
		return self::_getDao()->count($params);
	}

    /*
     * 获取创意关联的所有appkey
     */

    public static function getBlockId($params = array()) {
        $list = self::_getDao()->getFields('dever_pos_key,dever_pos_name');
        return $list;
    }

    public static function getFieldBy($field,$params){
        $ret = self::_getDao()->getAllByFields($field,$params);
        if(!$ret)return false;
        return $ret;
    }
	/**
	 *
	 * Enter description here ...
	 * @param unknown_type $params
	 * @param unknown_type $page
	 * @param unknown_type $limit
	 */
	public static function getList($page = 1, $limit = 10, $params = array(),$orderBy = array('id'=>'DESC')) {
	    if ($page < 1) $page = 1;
	    $start = ($page - 1) * $limit;
	    $ret = self::_getDao()->getList($start, $limit, $params, $orderBy);
	    $total = self::_getDao()->count($params);
	    return array($total, $ret);
	}
	

	
	
	/**
	 *
	 * Enter description here ...
	 * @param unknown_type $id
	 */
	public static function getByID($id) {
	    if (!intval($id)) return false;
	    return self::_getDao()->get(intval($id));
	}
	
	
	/**
	 *
	 * @param unknown_type $page
	 * @param unknown_type $limit
	 * @param unknown_type $params
	 * @return multitype:unknown multitype:
	 */
	
	public static function getBy($params = array(),$orderBy = array('id'=>'DESC')){
		if (!is_array($params)) return false;
	    $ret = self::_getDao()->getBy($params, $orderBy);
	   if(!$ret) return false;
	    return $ret;
	
	}
	
	/**
	 *
	 * @param unknown_type $page
	 * @param unknown_type $limit
	 * @param unknown_type $params
	 * @return multitype:unknown multitype:
	 */
	
	public static function getsBy($params = array(),$orderBy = array('id'=>'DESC')){
		if (!is_array($params)) return false;
	    $ret = self::_getDao()->getsBy($params, $orderBy);
	    if(!$ret) return false;
	    return $ret;
	}
	/**
	 *
	 * Enter description here ...
	 * @param unknown_type $data
	 * @param unknown_type $id
	 */
	public static function updateByID($data, $id) {
	    if (!is_array($data)) return false;
	    $data = self::_cookData($data);
	    return self::_getDao()->update($data, intval($id));
	}
	
	public static function updateBy($data, $params){
	    if (!is_array($data) || !is_array($params)) return false;
	    $data = self::_cookData($data);
	    return self::_getDao()->updateBy($data, $params);
	}
	/**
	 *
	 * @param unknown_type $data
	 * @param unknown_type $sorts
	 * @return boolean
	 */
	public static function sortAd($sorts) {
	    foreach($sorts as $key=>$value) {
	        self::_getDao()->update(array('sort'=>$value), $key);
	    }
	    return true;
	}
	
	/**
	 *
	 * @param unknown_type $data
	 * @return boolean
	 */
	public static function deleteGameAd($data) {
	    foreach($data as $key=>$value) {
	        $v = explode('|', $value);
	        self::_getDao()->deleteBy(array('id'=>$v[0]));
	    }
	    return true;
	}
	/**
	 *
	 * Enter description here ...
	 * @param unknown_type $id
	 */
	public static function deleteById($id) {
	    return self::_getDao()->delete(intval($id));
	}
	
	
	public static function deleteBy($params) {
	    return self::_getDao()->deleteBy($params);
	}
	
	/**
	 *
	 * Enter description here ...
	 * @param unknown_type $data
	 */
	public static function add($data) {
	    if (!is_array($data)) return false;
	    $data = self::_cookData($data);
	    $data['create_time'] = Common::getTime();
	    $ret = self::_getDao()->insert($data);
	    if (!$ret) return $ret;
	    return self::_getDao()->getLastInsertId();
	}
	

	

	/**
	 * 
	 * Enter description here ...
	 * @param unknown_type $data
	 */
	private static function _cookData($data) {
	    $tmp = array();
	    if(isset($data['id'])) $tmp['id'] = intval($data['id']);
	    if(isset($data['pos_key_type'])) $tmp['pos_key_type'] = $data['pos_key_type'];
	    if(isset($data['dever_pos_key'])) $tmp['dever_pos_key'] = $data['dever_pos_key'];
	    if(isset($data['ad_sub_type'])) $tmp['ad_sub_type'] = $data['ad_sub_type'];
        if(isset($data['size'])) $tmp['size'] = $data['size'];
	    if(isset($data['dever_pos_name'])) $tmp['dever_pos_name'] = $data['dever_pos_name'];
	    if(isset($data['pos_desc'])) $tmp['pos_desc'] = $data['pos_desc'];
	    if(isset($data['state'])) $tmp['state'] = $data['state'];
	    if(isset($data['app_id'])) $tmp['app_id'] = $data['app_id'];
	    if(isset($data['dev_id'])) $tmp['dev_id'] = $data['dev_id'];
	    if(isset($data['rate'])) $tmp['rate'] = $data['rate'];
	    if(isset($data['limit_num'])) $tmp['limit_num'] = $data['limit_num'];
	    if(isset($data['acounting_method'])) $tmp['acounting_method'] = $data['acounting_method'];
	    if(isset($data['denominated'])) $tmp['denominated'] = $data['denominated'];
	    if(isset($data['del'])) $tmp['del'] = $data['del'];
	    if(isset($data['create_time'])) $tmp['create_time'] = $data['create_time'];
	    $tmp['update_time'] = Common::getTime();
	    return $tmp;
	}
	
	/**
	 * 
	 * @return MobgiApi_Dao_AdDeverPosModel
	 */
	private static function _getDao() {
		return Common::getDao("MobgiApi_Dao_AdDeverPosModel");
	}
}
