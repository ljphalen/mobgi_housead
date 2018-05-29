<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
/**
 * Created by PhpStorm.
 * User: kyle.ke
 * Date: 2018/3/12
 * Time: 15:40
 */
class Marketing_EstimationController extends Admin_MarketingController {

    public $resourceName = 'estimation';

    /**
     * 预估覆盖人数（estimation/get）
     */
    public function getAction(){
        $info = $_POST;
        $params = $this->checkGetParam($info);

        $result = $this->send($params, 'get', 'estimation', 'post');
        $resultArr = json_decode($result, TRUE);
        $this->output($resultArr['code'], $resultArr['message'], $resultArr['data']);
    }

    /**
     * 检查预估覆盖人数的参数
     * @param type $info
     * @return type
     */
    private function checkGetParam($info){
        $params = [];
        if($info['adgroup']){ // 广告组参数
            $adgroup = $info['adgroup'];
            if($adgroup['site_set']){ // 投放站点
                $params['adgroup']['site_set'] = [$adgroup['site_set']];
            }
            if($adgroup['product_type']){ // 标的物类型
                $params['adgroup']['product_type'] = $adgroup['product_type'];
            }
            if($adgroup['billing_event'] && $adgroup['optimization_goal']){ //计费类型 和 	广告优化目标类型（必须同时填写）
                if($adgroup['billing_event'] == 'BILLINGEVENT_OCPA'){ // oCPA 也是 按CPC来计费的
                    $adgroup['billing_event'] = 'BILLINGEVENT_CLICK';
                }
                $params['adgroup']['billing_event'] = $adgroup['billing_event'];
                $params['adgroup']['optimization_goal'] = $adgroup['optimization_goal'];
            }
            if($adgroup['product_refs_id']){ // 标的物 id
                $params['adgroup']['product_refs_id'] = $adgroup['product_refs_id'];
            }
            if(isset($adgroup['time_range'])){ // 投放时间段
                if($adgroup['time_range'] == 0){
                    $params['adgroup']['time_series'] = Common::get_week_time_series(0, 24);
                }else{
                    if($adgroup['time_senior_type'] == 0){
                        $params['adgroup']['time_series'] = Common::get_week_time_series(intval($adgroup['start_time']), intval($adgroup['end_time']));
                    }else{
                        // 时间区段不满336位，自动填充0
                        $params['adgroup']['time_series'] = $adgroup['time_series'];
                    }
                }
            }
            if($adgroup['target']){ // 定向详细设置
                if(empty($adgroup['target']['targeting_id'])){
                    $targeting = [];
                    foreach ($adgroup['target']['targeting'] as $key => $val) {
                        if (!empty($val)) {
                            $targeting[$key] = in_array($key, ['gender', 'app_install_status']) ? [$val] : $val;
                        }
                    }
                    $params['targeting'] = json_encode($targeting);
                }else{
                    # 根据定向id获取定向详细数据
                    $result = $this->getFirstData(['targeting_id' => $adgroup['target']['targeting_id']], 'get', 'targetings', 'get');
                    if(!empty($result)){
                        $params['targeting'] = json_encode($result['targeting']);
                    }
                }
            }
        }
        if($info['adcreative']){
            $adcreative = $info['adcreative'];
            if($adcreative['adcreative_template_id']){ // 创意规格 id
                $params['adcreative'][] = ['adcreative_template_id' => $adcreative['adcreative_template_id']];
            }
        }
        if($info['targeting']){ // 定向详细设置
            $params['targeting'] = $info['targeting'];
        }
        return $params;
    }


}