<?php
if (! defined ( 'BASE_PATH' )) exit ( 'Access Denied!' );

/**
 *
 * Enter description here ...
 * 
 * @author rock.luo
 *        
 */
class MobgiApi_Service_AbFlowAdTypeRelModel extends Common_Service_Base {



    /**
     *
     * Enter description here ...
     * 
     * @param unknown_type $params            
     * @param unknown_type $page            
     * @param unknown_type $limit            
     */
    public static function getList($page = 1, $limit = 10, $params = array(), $orderBy = array('id'=>'DESC')) {
        if ($page < 1) $page = 1;
        $start = ($page - 1) * $limit;
        $ret = self::_getDao ()->getList ( $start, $limit, $params, $orderBy );
        $total = self::_getDao ()->count ( $params );
        return array ( $total, $ret );
    }

 /**
  * 
  * @param unknown $id
  * @return boolean|mixed
  */
    public static function getByID($id) {
        if (! intval ( $id )) return false;
        return self::_getDao ()->get ( intval ( $id ) );
    }

    /**
     * 
     * @param array $params
     * @param array $orderBy
     */
    public static function getBy($params = array(), $orderBy = array('id'=>'DESC')) {
        $ret = self::_getDao ()->getBy ( $params, $orderBy );
        if (! $ret) return false;
        return $ret;
    }

    /**
     * 
     * @param array $params
     * @param array $orderBy
     */
    public static function getsBy($params = array(), $orderBy = array('id'=>'DESC')) {
        $ret = self::_getDao ()->getsBy ( $params, $orderBy );
        if (! $ret) return false;
        return $ret;
    }

    /**
     * 
     * @param unknown $data
     * @param unknown $id
     * @return boolean|boolean|number
     */
    public static function updateByID($data, $id) {
        if (! is_array ( $data )) return false;
        $data = self::_cookData ( $data );
        return self::_getDao ()->update ( $data, intval ( $id ) );
    }

    /**
     * 
     * @param unknown $data
     * @param unknown $params
     * @return boolean
     */
    public static function updateBy($data, $params) {
        if (! is_array ( $data ) || ! is_array ( $params )) return false;
        $data = self::_cookData ( $data );
        return self::_getDao ()->updateBy ( $data, $params );
    }

    /**
     * 
     * @param unknown $id
     * @return boolean|number
     */
    public static function deleteById($id) {
        return self::_getDao ()->delete ( intval ( $id ) );
    }

    /**
     * 
     * @param unknown $params
     * @return boolean
     */
    public static function deleteBy($params) {
        if (!is_array($params)) return false;
        return self::_getDao ()->deleteBy ( $params );
    }
    
    public static function mutiFieldInsert($data) {
        if (!is_array($data)) return false;
        return self::_getDao()->mutiFieldInsert($data);
    }


    /**
     * 
     * @param unknown $data
     * @return boolean|boolean|number|string
     */
    public static function add($data) {
        if (! is_array ( $data )) return false;
        $data = self::_cookData ( $data );
        $ret = self::_getDao ()->insert ( $data );
        if (! $ret)  return $ret;
        return self::_getDao ()->getLastInsertId ();
    }

    /**
     * 
     * @param unknown $data
     */
    private static function _cookData($data) {
        $tmp = array ();
        if (isset ( $data ['id'] )) $tmp ['id'] = intval ( $data ['id'] );
        if (isset ( $data ['flow_id'] ))$tmp ['flow_id'] = $data ['flow_id'];
        if (isset ( $data ['ad_type'] ))$tmp ['ad_type'] = $data ['ad_type'];
        if (isset ( $data ['price'] )) $tmp ['price'] = $data ['price'];
        if (isset ( $data ['status'] )) $tmp ['status'] = $data ['status'];
        if (isset ( $data ['is_priority'] )) $tmp ['is_priority'] = $data ['is_priority'];
        if (isset ( $data ['is_use_dsp'] )) $tmp ['is_use_dsp'] = $data ['is_use_dsp'];
        if (isset ( $data ['is_delay'] )) $tmp ['is_delay'] = $data ['is_delay'];
        if (isset ( $data ['time'] )) $tmp ['time'] = $data ['time'];
        if (isset ( $data ['is_default'] )) $tmp ['is_default'] = $data ['is_default'];
        if (isset ( $data ['del'] ))$tmp ['del'] = $data ['del'];
        $tmp ['update_time'] = date ( 'Y-m-d H:i:s' );
        return $tmp;
    }

    /**
     *
     * @return MobgiApi_Dao_AbFlowAdTypeRelModel
     */
    private static function _getDao() {
        return Common::getDao ( "MobgiApi_Dao_AbFlowAdTypeRelModel" );
    }
}
