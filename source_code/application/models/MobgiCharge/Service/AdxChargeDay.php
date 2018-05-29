<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
/**
 * 
 * Enter description here ...
 * @author rock.luo
 *
 */


class MobgiCharge_Service_AdxChargeDayModel{
    
    
    const IS_CHARGE = 1;
    const IS_NOT_CHARGE = 1;
    const DEFAULT_VIEWS = 1000;


    
    /**
     * 获取之前日期的结果集
     * @param unknown $params
     * @return boolean|unknown
     */
    public static function getPreDaysData($params) {
        $ret = self::_getDao()->getPreDaysTotal($params);
        if(!$ret) return false;
        return $ret;
    }
    
	/**
	 * 
	 * Enter description here ...
	 */
	public static function getAll() {
		return array(self::_getDao()->count(), self::_getDao()->getAll());
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
	    $ret = self::_getDao()->insert($data);
	    if (!$ret) return $ret;
	    return self::_getDao()->getLastInsertId();
	}
	



	/**
	 *
	 * Enter description here ...
	 * @param unknown_type $data
	 */
	public static function _cookData($data) {
	    $tmp = array();
	    if(isset($data['ad_unit_id'])) $tmp['ad_unit_id'] = intval($data['ad_unit_id']);
	    if(isset($data['ad_id'])) $tmp['ad_id'] = intval($data['ad_id']);
	    if(isset($data['originality_id'])) $tmp['originality_id'] = $data['originality_id'];
	    if(isset($data['day'])) $tmp['day'] = $data['day'];
	    if(isset($data['clicks'])) $tmp['clicks'] = $data['clicks'];
	    if(isset($data['views'])) $tmp['views'] = $data['views'];
        if(isset($data['actives'])) $tmp['actives'] = $data['actives'];
	    if(isset($data['amount'])) $tmp['amount'] = $data['amount'];
	    if(isset($data['dau'])) $tmp['dau'] = $data['dau'];
	    if(isset($data['dsp_id'])) $tmp['dsp_id'] = $data['dsp_id'];
	    return $tmp;
	}
	
	/**
	 * 
	 * @return MobgiCharge_Dao_AdxChargeDayModel
	 */
	private static function _getDao() {
		return Common::getDao("MobgiCharge_Dao_AdxChargeDayModel");
	}

    /*
     * API专用
     */
    public static function getApiDataList($params) {
        // 用户权限
        $fields = implode(',',$params['theader']);
        $groupBy = implode(',',$params['dims']);
        $orderBy = 'days desc';
        $where = array(
            'days'=>array(array('>=', $params['sdate']), array('<=', $params['edate']))
        );
        if($params['data_type'] == 'mobgi'){
            $dataTypewhere = array('ads_id'=>'Mobgi');
        }else{
            $dataTypewhere = array('ads_id'=>'Housead_DSP');
        }
        $where = array_merge($where,$dataTypewhere);
        $list = self::_getDao()->getApiData($fields,$where,$groupBy,$orderBy);
        return $list;
    }
    
   public static function getFormatDate($d = 1) {
        return date ( 'Y-m-d', strtotime ( "-$d day" ) );
    }
    
    
    public static function getDspCTR($dspId, $day) {
        if (empty ( $dspId )) {
            return false;
        }
        $params ['ads_id'] = $dspId;
        $params ['days'] = array(array ('>=',$day ),array('<',date('Y-m-d')));
        $daysData = MobgiCharge_Service_AdxChargeDayModel::getPreDaysData ( $params );
        $tmpCTR = array ();
        $CTR = 0;
        if ($daysData) {
            if (isset ( $daysData [0] ['views'] ) && $daysData [0]  ['views'] > self::DEFAULT_VIEWS) {
                if ($daysData [0] ['views']) {
                    $CTR = $daysData [0] ['clicks'] / $daysData [0] ['views'];
                }
            }
        }
        return $CTR;
    }

	public static function getDeafaultCTR($originalityType){
		if($originalityType == Common_Service_Const::VIDEO_AD_SUB_TYPE){
			return Common_Service_Const::DEFAULT_CPC_VIDEO_CTR;
		}
		if($originalityType == Common_Service_Const::PIC_AD_SUB_TYPE){
			return Common_Service_Const::DEFAULT_CPC_PIC_CTR;
		}
		if($originalityType == Common_Service_Const::CUSTOME_AD_SUB_TYPE){
			return Common_Service_Const::DEFAULT_CPC_CUSTOME_CTR;
		}
		if($originalityType == Common_Service_Const::SPLASH_AD_SUB_TYPE){
			return Common_Service_Const::DEFAULT_CPC_SLASH_CTR;
		}
		if($originalityType == Common_Service_Const::ENBED_AD_SUB_TYPE){
			return Common_Service_Const::DEFAULT_CPC_ENBED_CTR;
		}
		return 0.05;
	}
    
    public static function getSomedayCTR($adInfoList, $originalityIds, $thirtyDay) {
        if (empty ( $originalityIds )) {
            return false;
        }
        $params ['originality_id'] = array ('IN', $originalityIds );
        $params ['days'] = array(array ('>=',$thirtyDay ),array('<',date('Y-m-d')));
        $daysData = MobgiCharge_Service_AdxChargeDayModel::getPreDaysData ( $params );
        $tmpCTR = array ();
        if ($daysData) {
            $daysData = Common::resetKey ( $daysData, 'originality_id' );
            foreach ( $originalityIds as $key => $val ) {
                    if (isset ( $daysData [$val] ['views']) && $daysData [$val] ['views'] && ($daysData [$val] ['views']>self::DEFAULT_VIEWS) ) {
                        $tmpCTR [$val] ['CTR'] = $daysData [$val] ['clicks'] / $daysData [$val] ['views'];
                    } else {
						$tmpCTR [$val] ['CTR'] = self::getDeafaultCTR($adInfoList [$val] ['originality_type']);
                    }
            }
        }
        return $tmpCTR;
    }
    
    
    public static function getTodayCTR($adInfoList, $originalityIds) {
        if (empty ( $originalityIds )) {
            return false;
        }
        $daysData = array ();
        foreach ( $originalityIds as $val ) {
            $ret = MobgiCharge_Service_AdxChargeMinuteModel::getTodayOriginalityDetailFromCache ( $val );
            if ($ret) {
                $daysData [$val] = $ret;
            } else {
                $daysData [$val] = array (
                        'views' => 0,
                        'clicks' => 0
                );
            }
        }
        $tmpCTR = array ();
        if ($daysData) {
            $daysData = Common::resetKey ( $daysData, 'originality_id' );
            foreach ( $originalityIds as $key => $val ) {
				if ( isset ( $daysData [$val] ['views'] ) && $daysData [$val] ['views'] &&  ($daysData [$val] ['views']>self::DEFAULT_VIEWS) ) {
					$tmpCTR [$val] ['CTR'] = $daysData [$val] ['clicks'] / $daysData [$val] ['views'];
				} else {
					$tmpCTR [$val] ['CTR'] = self::getDeafaultCTR($adInfoList [$val] ['originality_type']);
				}

            }
        }
        return $tmpCTR;
    }
    
    public static function calDspCTR($dspId, $adType, $originalityId) {
        // 获取30天的CTR
        $thirtyDay = self::getFormatDate ( Common_Service_Const::THIRTY_DAY );
        $thirtyDayCTR = self::getDspCTR ( $dspId, $thirtyDay );
        // 七天
        $sevenDay = self::getFormatDate ( Common_Service_Const::SEVEN_DAY );
        $sevenDayCTR =  self::getDspCTR ( $dspId, $sevenDay );
        // 三天
        $threeDay = self::getFormatDate ( Common_Service_Const::THREE_DAY );
        $threeDayCTR =  self::getDspCTR ( $dspId, $threeDay );
        // 昨天
        $oneDay = self::getFormatDate ( Common_Service_Const::ONE_DAY );
        $yesterdayCTR = self::getDspCTR ( $dspId, $oneDay );
        $originalityId = (strtolower ( $dspId ) == strtolower(Common_Service_Const::HOUSEAD_DSP_ID)) ? $originalityId : 0;
        // 今天实时的
        $todayCTR =  self::getDspTodayCTR ( $originalityId, $dspId );
        $pCTR = $thirtyDayCTR * 0.03 + $sevenDayCTR * 0.07 + $threeDayCTR * 0.1 + $yesterdayCTR * 0.4 + $todayCTR * 0.4;
        var_dump($pCTR);
        if (! $pCTR) {
            $pCTR = 0.05 ;
            //($adType == 1) ? 0.05 : 0.08;
        }
        return $pCTR;
    }
    

    public  static function getDspTodayCTR($originalityId, $dspId) {
        $daysData = MobgiCharge_Service_AdxChargeMinuteModel::getTodayOriginalityDetailFromCache ( $originalityId, $dspId );
        $CTR = 0;
        if ($daysData) {
            if (isset ( $daysData ['views'] )) {
                if ($daysData ['views'] &&  $daysData ['views']>self::DEFAULT_VIEWS) {
                    $CTR = $daysData ['clicks'] / $daysData ['views'];
                }
            }
        }
        return $CTR;
    }
    
    
     public  static  function getSomedayAverageExposureRate($adInfoList, $originalityIds, $thirtyDay) {
        if (empty ( $originalityIds )) {
            return false;
        }
        $params ['originality_id'] = array ('IN',$originalityIds );
        $params ['days'] = array(array ('>=',$thirtyDay ),array('<',date('Y-m-d')));
        $daysData = MobgiCharge_Service_AdxChargeDayModel::getPreDaysData ( $params );
        $tmpCTR = array ();
        if ($daysData) {
            $daysData = Common::resetKey ( $daysData, 'originality_id' );
            foreach ( $originalityIds as $key => $val ) {
				//大于1000防止测试数据拉高价格
				if (isset ( $daysData [$val] ['views']) && $daysData [$val] ['views'] && ($daysData [$val] ['views'] > self::DEFAULT_VIEWS)){
					$tmpCTR [$val] ['AER'] = $daysData [$val] ['clicks'] / $daysData [$val] ['views'];
				} else {
					$tmpCTR [$val] ['AER'] = 0;
				}

            }
        }
        return $tmpCTR;
    }
    

}


