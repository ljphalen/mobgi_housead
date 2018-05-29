<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
/**
 * Created by PhpStorm.
 * User: kyle.ke
 * Date: 2018/3/29
 * Time: 11:41
 */
class Marketing_AdvertiserController extends Admin_MarketingController{

    public $resourceName = 'advertiser';

    /**
     * 添加服务商子客户（advertiser/add）
     */
    public function addAction(){
        $info = $this->getInput(array('corporation_name', 'certification_image_id', 'system_industry_id', 'customized_industry'));
//        $params = $this->checkAddParam($info);
        $params = [
            'corporation_name' => trim($info['corporation_name']),
            'certification_image_id' => trim($info['certification_image_id']),
            'system_industry_id' => intval($info['system_industry_id']),
        ];

        $result = $this->send($params, 'add');
        $imageResultArr = json_decode($result, TRUE);
        $this->output($imageResultArr['code'], $imageResultArr['message'], $imageResultArr['data']);
    }

    /**
     * 获取广告主信息（advertiser/get）
     */
    public function getAction(){
        $result = $this->send([], 'get');
        $imageResultArr = json_decode($result, TRUE);
        $this->output($imageResultArr['code'], $imageResultArr['message'], $imageResultArr['data']);
    }

    /**
     * 更新广告主信息 (advertiser/update)
     */
    public function updateAction(){
        $info = $this->getInput(array('daily_budget'));
        $data = $this->checkUpdateParam($info);
        $result = $this->send($data, 'update');
        $resultArr = json_decode($result, TRUE);
        if($resultArr['code'] == 0 && $resultArr['message'] == ''){
            $resultArr['message'] = '修改成功！';
        }
        $this->output($resultArr['code'], $resultArr['message'], $resultArr['data']);
    }

    /**
     * 检查更新广告主信息的参数
     * @param type $info
     * @return type
     */
    private function checkUpdateParam($info){

        if(isset($info['daily_budget']) && $info['daily_budget'] != ''){
            if(!$this->matchValue(1, $info['daily_budget'])){
                $this->output(-1, '日预算格式有误，只支持精确到小数点后两位的正数');
            }
            $info['daily_budget'] = floatval($info['daily_budget']) * 100;
            if($info['daily_budget'] <5000 || $info['daily_budget']>400000000){
                $this->output(-1, '日预算需介于 50 元-10,000,000 元');
            }
        }else{
            unset($info['daily_budget']);
        }
        return $info;
    }

}