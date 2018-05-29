<?php
/**
 * Created by PhpStorm.
 * User: kyle.ke
 * Date: 2017/3/13
 * Time: 16:25
 */

if (!defined('BASE_PATH')) exit('Access Denied!');

class Advertiser_Service_GdtCampaignModel{

    /**
     *
     * Enter description here ...
     * @param unknown_type $uid
     */
    public static function getCampaign($campaign_id) {
        if (!intval($campaign_id)) return false;
        $result =  self::_getDao()->getby(array('campaign_id'=>intval($campaign_id)));
        if($result){
            $result['config'] = json_decode($result['config'], true);
            $result['sync_response'] = json_decode($result['sync_response'], true);
        }
        return $result;
    }

    /**
     *
     * Enter description here ...
     * @param unknown_type $data
     * @param unknown_type $id
     */
    public static function updateCampaign($data, $id) {
        if (!is_array($data)) return false;
        $data['update_time'] = Common::getTime();
        $data = self::_cookData($data);
        return self::_getDao()->update($data, intval($id));
    }

    /**
     *
     * Enter description here ...
     * @param unknown_type $data
     * @param unknown_type $id
     */
    public static function updateCampaignByParams($data, $params) {
        if (!is_array($data)) return false;
        $data['update_time'] = Common::getTime();
        $data = self::_cookData($data);
        return self::_getDao()->updateBy($data, $params);
    }

    /**
     *
     * Enter description here ...
     * @param unknown_type $id
     */
    public static function deleteCampaign($id) {
        return self::_getDao()->delete(intval($id));
    }

    /**
     *
     * Enter description here ...
     * @param unknown_type $data
     */
    public static function addCampaign($data) {
        if (!is_array($data)) return false;
        $data['create_time'] = Common::getTime();
        $data['update_time'] = Common::getTime();
        $data = self::_cookData($data);
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
        if(isset($data['campaign_id'])) $tmp['campaign_id'] = $data['campaign_id'];
        if(isset($data['advertiser_uid'])) $tmp['advertiser_uid'] = $data['advertiser_uid'];
        if(isset($data['campaign_name'])) $tmp['campaign_name'] = $data['campaign_name'];
        if(isset($data['local_config'])) $tmp['local_config'] = json_encode ($data['local_config']);
        if(isset($data['config'])) $tmp['config'] = json_encode ($data['config']);
        if(isset($data['sync_status'])) $tmp['sync_status'] = $data['sync_status'];
        if(isset($data['sync_response'])) $tmp['sync_response'] = json_encode ($data['sync_response']);
        if(isset($data['del'])) $tmp['del'] = $data['del'];
        if(isset($data['create_time'])) $tmp['create_time'] = $data['create_time'];
        if(isset($data['update_time'])) $tmp['update_time'] = $data['update_time'];
        return $tmp;
    }

    /**
     *
     * Enter description here ...
     */
    public static function getCampaignByName($campaignname) {
        if (!$campaignname) return false;
        return self::_getDao()->getBy(array('campaign_name'=>$campaignname));
    }


    public static function getBy($params = array(),$orderBy = array('id'=>'DESC')){
        $ret = self::_getDao()->getBy($params, $orderBy);
        if(!$ret) return false;
        return $ret;

    }


    public static function getsBy($params = array(),$orderBy = array('id'=>'DESC')){
        $ret = self::_getDao()->getsBy($params, $orderBy);
        if(!$ret) return false;
        return $ret;

    }

    public static function getNameId($params = array()){
        $result = self::getsBy($params);
        $campaign_data = array();
        if($result){
            foreach($result as $key => $value){
                $campaign_data[$key]['campaign_id'] = $value['campaign_id'];
                $campaign_data[$key]['campaign_name'] = $value['campaign_name'];
            }
        }
        return $campaign_data;
    }

    /**
     *
     * @return Advertiser_Dao_DirectModel
     */
    private static function _getDao() {
        return Common::getDao("Advertiser_Dao_GdtCampaignModel");
    }

}
