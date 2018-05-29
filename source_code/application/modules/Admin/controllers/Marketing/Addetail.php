<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
/**
 * Created by PhpStorm.
 * User: kyle.ke
 * Date: 2018/4/8
 * Time: 14:18
 */
class Marketing_AddetailController extends Admin_MarketingController {

    /**
     * 创建广告整体 计划->广告组->广告->创意（addetail/add）
     */
    public function addAction(){
        $params = $_POST;
        $campaignState = $adgroupState = false; // 推广计划、广告组 默认不需要新建
        if(empty($params['campaign_id'])){
            $campaignState = true;
            $campaignData = $this->checkCampaignParams($params);
        }
        if(empty($params['adgroup_id'])){
            $adgroupState = true;
            $adgroupData = $this->checkAdgroupParams($params);
        }
        $adcreativeData = $this->checkAdcreativeParams($params);
        $adData = $this->checkAdParams($params);
        # 创建计划 && 获取计划
        if($campaignState) {
            $campaignResult = $this->addCampaign($campaignData);
            if ($campaignResult['code'] != 0) {
                $this->output($campaignResult['code'], '计划：'.$campaignResult['message']);
            }
            $campaignId = $campaignResult['data']['campaign_id'];
        }else{
            $campaignId = $params['campaign_id'];
        }
        if($adgroupState) {
            $adgroupData['campaign_id'] = $campaignId;
            # 创建广告组，失败回滚（删除计划）
            $adgroupResult = $this->addAdgroup($adgroupData);
            if($adgroupResult['code'] != 0){
                if($campaignState){ // 删除已经创建的计划
                    $this->deleteUnit('campaigns', 'campaign_id', $campaignId);
                }
                $this->output($adgroupResult['code'], '广告组：'.$adgroupResult['message']);
            }
            $adgroupId = $adgroupResult['data']['adgroup_id'];
        }else{
            $adgroupId = $params['adgroup_id'];
        }
        $adcreativeData['campaign_id'] = $campaignId;
        $adData['adgroup_id'] = $adgroupId;

        # 创建创意，失败回滚（删除计划、广告组、创建成功的创意）
        $adcreativeResult = $this->addAdcreative($adcreativeData);
        if($adcreativeResult['code'] != 0){
            if($campaignState){ // 删除已经创建的计划
                $this->deleteUnit('campaigns', 'campaign_id', $campaignId);
            }
            if($adgroupState){ // 删除已经创建的广告组
                $this->deleteUnit('adgroups', 'adgroup_id', $adgroupId);
            }
            $this->deleteUnit('adcreatives', 'adcreative_id', $adcreativeResult['data']['adcreative_ids']);
            $this->output($adcreativeResult['code'], '创意：'.$adcreativeResult['message']);
        }
        $adcreativeIds = $adcreativeResult['data']['adcreative_ids'];

        # 创建广告，失败回滚（删除计划、广告组、创意、创建成功的广告）
        $adResult = $this->addAd($adData, $adcreativeIds);
        if($adResult['code'] != 0){
            if($campaignState){ // 删除已经创建的计划
                $this->deleteUnit('campaigns', 'campaign_id', $campaignId);
            }
            if($adgroupState){ // 删除已经创建的广告组
                $this->deleteUnit('adgroups', 'adgroup_id', $adgroupId);
            }
            // 先删除对应广告、才能删除对应的创意
            $this->deleteUnit('ads', 'ad_id', $adResult['data']['ad_ids']);
            $this->deleteUnit('adcreatives', 'adcreative_id', $adcreativeIds);
            $this->output($adResult['code'], '广告：'.$adResult['message']);
        }
        $this->output(0, '新建成功！');
    }

    /**
     * 检查创建推广计划的参数
     * @param type $params
     * @return type
     */
    private function checkCampaignParams($params){
        $data = [
            'campaign_name' => trim($params['campaign_name']),
            'campaign_type' => $params['campaign_type'],
            'product_type' => $params['product_type'],
            'speed_mode' => $params['speed_mode']
        ];
        if(empty($data['campaign_name'])){
            $this->output(-1, '推广计划名称不能为空');
        }

        $campaignNameLength = strlen($data['campaign_name']);
        if($campaignNameLength<1 || $campaignNameLength>120){
            $this->output(-1, '推广计划名称字段长度最小 1 字节，长度最大 120 字节');
        }
        if($this->checkUnitName('campaigns', 'campaign_name', $data['campaign_name'], 'campaign_id')){
            $this->output(-1, '该推广计划名称已被占用');
        }
        if(empty($data['product_type'])){
            $this->output(-1, '标的物类型不能为空');
        }
        if(!isset($this->marketingConfig['PRODUCT_TYPE'][$data['product_type']])){
            $this->output(-1, '标的物类型不存在');
        }
        if(empty($data['campaign_type'])){
            $this->output(-1, '推广计划类型不能为空');
        }
        if(!isset($this->marketingConfig['CAMPAIGN_TYPE'][$data['campaign_type']])){
            $this->output(-1, '推广计划类型不存在');
        }
        if($data['campaign_type'] != 'CAMPAIGN_TYPE_WECHAT_MOMENTS'){
            if(empty($params['daily_budget'])){
                $this->output(-1, '日预算不能为空值');
            }
            if(!$this->matchValue(1, $params['daily_budget'])){
                $this->output(-1, '日预算格式有误，只支持精确到小数点后两位的正数');
            }
            $data['daily_budget'] = floatval($params['daily_budget']) * 100;
            if($data['daily_budget'] <5000 || $data['daily_budget']>400000000){
                $this->output(-1, '日预算需介于 50 元-4,000,000 元，单位为人民币');
            }
        }
        if(isset($data['speed_mode'])){
            if(!isset($this->marketingConfig['SPEED_MODE'][$data['speed_mode']])){
                $this->output(-1, '投放速度不能为空');
            }
        }
        return $data;
    }

    /**
     * 检查创建广告组的参数
     * @param type $params
     * @return type
     */
    private function checkAdgroupParams($params){
        $data = [
            'adgroup_name' => trim($params['adgroup_name']),
            'product_type' => $params['product_type'],
            'billing_event' => $params['billing_event'],
            'optimization_goal' => $params['optimization_goal'],
        ];

        $adgroupNameLength = strlen($data['adgroup_name']);
        if($adgroupNameLength<1 || $adgroupNameLength>120){
            $this->output(-1, '广告组名称字段长度最小 1 字节，长度最大 120 字节');
        }
        if($this->checkUnitName('adgroups', 'adgroup_name', $data['adgroup_name'], 'adgroup_id')){
            $this->output(-1, '该广告组名称已被占用');
        }
        if(!isset($this->marketingConfig['SITE_SET'][$params['site_set']])){
            $this->output(-1, '请选择投放站点');
        }
        $data['site_set'][] = $params['site_set'];
        if(empty($data['product_type'])){
            $this->output(-1, '标的物类型不能为空');
        }
        if(!isset($this->marketingConfig['PRODUCT_TYPE'][$data['product_type']])){
            $this->output(-1, '标的物类型参数错误');
        }
        $beginDateTime = strtotime($params['begin_date']);
        $endDateTime = strtotime($params['end_date']);
        if($beginDateTime > $endDateTime){
            $this->output(-1, '开始投放日期要小于等于结束投放日期');
        }
        if($endDateTime < strtotime(date('Y-m-d'))){
            $this->output(-1, '结束投放日期要大于等于今天');
        }
        $data['begin_date'] = date('Y-m-d',$beginDateTime);
        $data['end_date'] = date('Y-m-d',$endDateTime);

        if(!isset($this->marketingConfig['BILLINGEVENT'][$data['billing_event']])){
            $this->output(-1, '请选择计费类型');
        }
        if(empty($params['bid_amount'])){
            $this->output(-1, '广告出价不能为空');
        }
        if(!$this->matchValue(1, $params['bid_amount'])){
            $this->output(-1, '出价格式有误，只支持精确到小数点后两位的正数');
        }
        $data['bid_amount'] = floatval($params['bid_amount']) * 100;
        //todo: (1)根据 campaign_id 获取推广计划的信息，得到campaign_type
        //      (2)根据广告组出价规则和campaign_type做不同的处理逻辑校验
        //
        //广告组出价规则：
        //    对于普通展示广告（campaign_type = CAMPAIGN_TYPE_NORMAL）的CPC出价而言：
        //        出价需介于10分-10,000分之间（0.1元-100元，单位为人民币）；
        //        出价不得高于所属推广计划或帐号日预算的50%。
        //    对于微信公众号广告（campaign_type = CAMPAIGN_TYPE_WECHAT_OFFICIAL_ACCOUNTS）的CPC出价而言：
        //        出价需介于50分-2,000分之间（0.5元-20元，单位为人民币）；
        //        出价不得高于所属推广计划或帐号日预算的50%。
        //    对于普通展示广告（campaign_type = CAMPAIGN_TYPE_NORMAL）的CPM出价，要求介于150分-100,000分之间（1.5元-1000元，单位为人民币）；
        //    对于微信朋友圈广告（campaign_type = CAMPAIGN_TYPE_WECHAT_MOMENTS ）的CPM出价，基于不同的地域定向有不同限制，详见 朋友圈广告地域定向及价格约束；
        //    对于某些特殊流量的CPM出价可能还会有更详细的要求，具体信息可以联系您的运营接口人。

        if(!isset($this->marketingConfig['OPTIMIZATION_GOAL'][$data['optimization_goal']])){
            $this->output(-1, '请选择广告优化目标类');
        }
        if($params['campaign_type'] == 'CAMPAIGN_TYPE_WECHAT_MOMENTS'){
            if(empty($params['daily_budget'])){
                $this->output(-1, '日预算不能为空值');
            }
            if(!$this->matchValue(1, $params['daily_budget'])){
                $this->output(-1, '日预算格式有误，只支持精确到小数点后两位的正数');
            }
            if($data['billing_event'] != 'BILLINGEVENT_IMPRESSION'){
                $this->output(-1, '微信朋友圈广告类型推广计划只能使用 CPM 的计费方式');
            }
            $data['daily_budget'] = floatval($params['daily_budget']) * 100;
            if($data['daily_budget'] <100000 || $data['daily_budget']>1000000000){
                $this->output(-1, '日预算要求介于 1,000 元-10,000,000 元，单位为人民币');
            }
        }
        if($this->marketingConfig['PRODUCT_TYPE'][$data['product_type']]['product_refs_id'] == 1){
            if(empty($params['product_refs_id'])){
                $this->output(-1, '请选择标的物');
            }
            $data['product_refs_id'] = $params['product_refs_id'];
            $data['sub_product_refs_id'] = $params['sub_product_refs_id'];
        }
        //todo sub_product_refs_id校验 (可不校验）

        //todo targeting_id (可不校验）

        if(empty($params['target']['targeting_id'])){ // 定向详细设置，存放所有定向条件。与 targeting_id 不能同时填写且不能同时为空，仅微信流量的广告（朋友圈和公众号广告）可使用
            if($params['campaign_type'] == 'CAMPAIGN_TYPE_NORMAL'){
                $this->output( -1, '非微信流量的广告只能使用已保存的定向包！');
            }
            $targeting = [];
            foreach ($params['target']['targeting'] as $key => $val) {
                if (!empty($val)) {
                    $targeting[$key] = in_array($key, ['gender', 'app_install_status']) ? [$val] : $val;
                }
            }
            $data['targeting'] = json_encode($targeting);
        }else{
            if($params['campaign_type'] != 'CAMPAIGN_TYPE_NORMAL'){
                $this->output( -1, '微信流量的广告只能使用定向详细设置，不能使用定向包！');
            }
            $data['targeting_id'] = $params['target']['targeting_id'];
        }
        //todo time_series (可不校验）
        if($params['time_range'] == 0){
            $data['time_series'] = Common::get_week_time_series(0, 24);
        }else{
            if($params['time_senior_type'] == 0){
                $data['time_series'] = Common::get_week_time_series(intval($params['start_time']), intval($params['end_time']));
            }else{
                // 时间区段不满336位，自动填充0
                $data['time_series'] = $params['time_series'];
            }
        }

        if($params['customized_category']){
            $customizedCategoryLength = strlen($params['customized_category']);
            if($customizedCategoryLength<1 || $customizedCategoryLength>200){
                $this->output(-1, '自定义分类字段长度最小 0 字节，长度最大 200 字节');
            }
        }

        //todo frequency_capping(可不校验）

        return $data;
    }

    /**
     * 检查创建广告创意的参数
     * @param type $params
     * @return type
     */
    private function checkAdcreativeParams($params){
        $data = [
            'product_type' => $params['product_type'],
            'adcreative_template_id' => $params['adcreative_template_id'],
            'destination_url' => trim(html_entity_decode($params['destination_url'])),
            'adcreative_elements' => $params['adcreative_elements'],
        ];
        if(empty($data['product_type'])){
            $this->output(-1, '请选择标的物类型');
        }
        if(!isset($this->marketingConfig['PRODUCT_TYPE'][$data['product_type']])){
            $this->output(-1, '标的物类型参数错误');
        }
        if(empty($params['site_set'])){
            $this->output(-1, '请选择投放站点');
        }
        if(!isset($this->marketingConfig['SITE_SET'][$params['site_set']])){
            $this->output(-1, '投放站点不存在');
        }
        $data['site_set'][] = $params['site_set'];
        if(empty($data['adcreative_template_id'])){
            $this->output(-1, '创意规格 id不能为空');
        }

        $destinationUrlLength = strlen($data['destination_url']);
        if($destinationUrlLength<1 || $destinationUrlLength>1023){
            $this->output(-1, '落地页 url 字段长度最小 1 字节，长度最大 1023 字节');
        }

        if(isset($params['deep_link']) && $params['deep_link']){
            $deepLinkLength = strlen($params['deep_link']);
            if($deepLinkLength<1 || $deepLinkLength>2048){
                $this->output(-1, '应用直达页 URL 字段长度最小 0 字节，长度最大 2048 字节');
            }
            $data['deep_link'] = $params['deep_link'];
        }

        if(isset($params['product_refs_id']) && $params['product_refs_id']){
            $productRefsIdLength = strlen($params['product_refs_id']);
            if($productRefsIdLength<1 || $productRefsIdLength>128){
                $this->output(-1, '标的物 id，详见 [标的物类型] 字段长度最小 0 字节，长度最大 128 字节');
            }
            $data['product_refs_id'] = $params['product_refs_id'];
        }

        if($params['campaign_type'] == 'CAMPAIGN_TYPE_WECHAT_MOMENTS'){
            if($params['share_info']['share_title'] && $params['share_info']['share_description']){
                $shareTitleLength = strlen($params['share_info']['share_title']);
                if($shareTitleLength<1 || $shareTitleLength>14){
                    $this->output(-1, '分享标题 字段长度最小 1 字节，长度最大 14 字节');
                }
                $shareDescriptionLength = strlen($params['share_info']['share_description']);
                if($shareDescriptionLength<1 || $shareDescriptionLength>20){
                    $this->output(-1, '分享描述 字段长度最小 1 字节，长度最大 20 字节');
                }
                $data['share_info'] = $params['share_info'];
            }
        }
        if(empty($data['adcreative_elements'])){
            $this->output(-1, '创意元素不能为空');
        }
        # 校验创意名是否重复
        foreach($data['adcreative_elements'] as $key => $value){
            unset($data['adcreative_elements']['ad_name']);
            $adcreativeName = trim($value['adcreative_name']);
            if(empty($adcreativeName)){
                $this->output(-1, '第'. ($key + 1) .'个创意的创意名为空');
            }
            if($this->checkUnitName('adcreatives', 'adcreative_name', $adcreativeName, 'adcreative_id')){
                $this->output(-1, '第'. ($key + 1) .'个创意的创意名已被占用');
            }
            # 如果有视频素材，需要校验是否已经转码成功
            if(isset($elements['video'])){
                $videoResult = $this->send(['video_id'=>$elements['video']], 'get', 'videos');
                $videoArr = json_decode($videoResult, TRUE);
                if($videoArr['code'] != 0){
                    $this->output(-1, '第'. ($key + 1) .'个创意的视频：'.$videoArr['message']);
                }
                if($videoArr['data']['system_status'] != 'MEDIA_STATUS_VALID'){
                    $this->output(-1, '第'. ($key + 1) .'个创意的视频正在转码中，请过会后再提交');
                }
            }
        }
        return $data;
    }

    /**
     * 检查创建广告的参数
     * @param type $params
     * @return type
     */
    private function checkAdParams($params){
        $data = [
            'ad_elements' => $params['adcreative_elements'],
        ];
        if(empty($data['ad_elements'])){
            $this->output(-1, '请填写广告信息');
        }
        # 校验广告名是否重复
        foreach($data['ad_elements'] as $key => $value){
            $adName = trim($value['ad_name']);
            if(empty($adName)){
                $this->output(-1, '第'. ($key + 1) .'个广告的广告名为空');
            }
            if($this->checkUnitName('ads', 'ad_name', $adName, 'ad_id')){
                $this->output(-1, '第'. ($key + 1) .'个广告的广告名已被占用');
            }
        }
        if($params['impression_tracking_url']){
            $impressionTrackingUrlLength = strlen($params['impression_tracking_url']);
            if($impressionTrackingUrlLength<1 || $impressionTrackingUrlLength>1023){
                $this->output(-1, '曝光监控地址 字段长度最小 1 字节，长度最大 1023 字节');
            }
            $data['impression_tracking_url'] = $params['impression_tracking_url'];
        }

        if($params['click_tracking_url']){
            $clickTrackingUrlLength = strlen($params['click_tracking_url']);
            if($clickTrackingUrlLength<1 || $clickTrackingUrlLength>1023){
                $this->output(-1, '点击监控地址 字段长度最小 1 字节，长度最大 1023 字节');
            }
            $data['click_tracking_url'] = $params['click_tracking_url'];
        }

        if($params['feeds_interaction_enabled']){
            if(!isset($this->marketingConfig['INTERACTION'][$params['feeds_interaction_enabled']])){
                $this->output(-1, '是否支持赞转评参数错误');
            }
            $data['feeds_interaction_enabled'] = $params['feeds_interaction_enabled'];
        }
        return $data;
    }

    /**
     * 创建推广计划
     * @param $data
     * @return mixed
     */
    private function addCampaign($data){
        $result = $this->send($data, 'add', 'campaigns');
        $resultArr = json_decode($result, TRUE);
        return $resultArr;
    }

    /**
     * 创建广告组
     * @param $data
     * @return mixed
     */
    private function addAdgroup($data){
        $result = $this->send($data, 'add', 'adgroups');
        $resultArr = json_decode($result, TRUE);
        return $resultArr;
    }

    /**
     * 创建创意（多）
     * @param $data
     * @return mixed
     */
    private function addAdcreative($data){
        $adcreativeElements = $data['adcreative_elements'];
        unset($data['adcreative_elements']);
        $ids = [];
        foreach($adcreativeElements as $key => $value){
            $data['adcreative_elements'] = $value;
            $data['adcreative_name'] = trim($data['adcreative_elements']['adcreative_name']);
            unset($data['adcreative_elements']['adcreative_name']);
            $result = $this->send($data, 'add', 'adcreatives');
            $resultArr = json_decode($result, TRUE);
            if($resultArr['code'] == 0){
                $ids[] = $resultArr['data']['adcreative_id'];
            }else{
                $resultArr['message'] = '第'. ($key + 1) .'个创意创建失败：'.$resultArr['message'];
                $resultArr['data']['adcreative_ids'] = $ids;
                return $resultArr;
            }
        }
        $resultArr['data']['adcreative_ids'] = $ids;
        return $resultArr;
    }

    /**
     * 创建广告（多）
     * @param $data
     * @param $adcreativeIds
     * @return mixed
     */
    private function addAd($data, $adcreativeIds){
        $adElements = $data['ad_elements'];
        unset($data['ad_elements']);
        $ids = [];
        foreach($adElements as $key => $value){
            $data['ad_name'] = trim($value['ad_name']);
            $data['adcreative_id'] = $adcreativeIds[$key];
            $result = $this->send($data, 'add', 'ads');
            $resultArr = json_decode($result, TRUE);
            if($resultArr['code'] == 0){
                $ids[] = $resultArr['data']['ads'];
            }else{
                $resultArr['message'] = '第'. ($key + 1) .'个广告创建失败：'.$resultArr['message'];
                $resultArr['data']['ad_ids'] = $ids;
                return $resultArr;
            }
        }
        $resultArr['data']['ad_ids'] = $ids;
        return $resultArr;
    }

}