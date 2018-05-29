<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
/**
 * Created by PhpStorm.
 * User: kyle.ke
 * Date: 2018/3/12
 * Time: 15:15
 */
class Marketing_AdsController extends Admin_MarketingController {

    public $resourceName = 'ads';

    /**
     * 创建广告（ads/add）
     */
    public function addAction(){
        $info = $this->getInput(array(
            'adgroup_id', 'adcreative_id', 'ad_name', 'configured_status', 'impression_tracking_url', 'click_tracking_url', 'feeds_interaction_enabled',
        ));
        $info = $this->checkAddParam($info);
        $data = $info;

        $result = $this->send($data, 'add');
        $resultArr = json_decode($result, TRUE);
        if($resultArr['code'] == 0 && $resultArr['message'] == ''){
            $resultArr['message'] = '新建成功！';
        }
        $this->output($resultArr['code'], $resultArr['message'], $resultArr['data']);
    }

    /**
     * 检查创建广告的参数
     * @param type $info
     * @return type
     */
    private function checkAddParam($info){
        $info['adgroup_id'] = intval($info['adgroup_id']);
        if(empty($info['adgroup_id'])){
            $this->output(1, '请选择广告组');
        }

        $info['adcreative_id'] = intval($info['adcreative_id']);
        if(empty($info['adcreative_id'])){
            $this->output(1, '请选择创意');
        }

        $ad_name_length = strlen($info['ad_name']);
        if($ad_name_length<1 || $ad_name_length>120){
            $this->output(1, '广告名称字段长度最小 1 字节，长度最大 120 字节');
        }
        if($this->checkUnitName('ads', 'ad_name', $info['ad_name'], 'ad_id')){
            $this->output(-1, '该广告名已被占用');
        }
        if($info['configured_status']){
            if(!isset($this->marketingConfig['AD_STATUS'][$info['configured_status']])){
                $this->output(1, '客户设置的状态参数错误');
            }
        }

        if($info['impression_tracking_url']){
            $impression_tracking_url_length = strlen($info['impression_tracking_url']);
            if($impression_tracking_url_length<1 || $impression_tracking_url_length>1023){
                $this->output(1, '曝光监控地址 字段长度最小 1 字节，长度最大 1023 字节');
            }
        }else{
            unset($info['impression_tracking_url']);
        }

        if($info['click_tracking_url']){
            $click_tracking_url_length = strlen($info['click_tracking_url']);
            if($click_tracking_url_length<1 || $click_tracking_url_length>1023){
                $this->output(1, '点击监控地址 字段长度最小 1 字节，长度最大 1023 字节');
            }
        }else{
            unset($info['click_tracking_url']);
        }

        if(isset($info['feeds_interaction_enabled']) && $info['feeds_interaction_enabled']){
            if(!isset($this->marketingConfig['INTERACTION'][$info['feeds_interaction_enabled']])){
                $this->output(1, '是否支持赞转评参数错误');
            }
        }else{
            unset($info['feeds_interaction_enabled']);
        }

        return $info;
    }

    /**
     * 更新广告创意（ads/update）
     */
    public function updateAction(){
        $info = $this->getInput(array(
            'ad_id', 'ad_name', 'configured_status', 'impression_tracking_url', 'click_tracking_url', 'feeds_interaction_enabled',
        ));
        $data = $this->checkUpdateParam($info);

        $result = $this->send($data, 'update');
        $resultArr = json_decode($result, TRUE);
        if($resultArr['code'] == 0 && $resultArr['message'] == ''){
            $resultArr['message'] = '修改成功！';
        }
        $this->output($resultArr['code'], $resultArr['message'], $resultArr['data']);
    }

    /**
     * 检查更新广告创意的参数
     * @param type $info
     * @return type
     */
    private function checkUpdateParam($info){
        if(empty($info['ad_id'])){
            $this->output(1, '广告 id不能为空');
        }

        if(isset($info['ad_name'])  && $info['ad_name'] ){
            $ad_name_length = strlen($info['ad_name']);
            if($ad_name_length<1 || $ad_name_length>120){
                $this->output(1, '广告名称字段长度最小 1 字节，长度最大 120 字节');
            }
        }else{
            unset($info['ad_name']);
        }

        if($info['configured_status']){
            if(!isset($this->marketingConfig['AD_STATUS'][$info['configured_status']])){
                $this->output(1, '客户设置的状态参数错误');
            }
        }

        if($info['impression_tracking_url']){
            $impression_tracking_url_length = strlen($info['impression_tracking_url']);
            if($impression_tracking_url_length<1 || $impression_tracking_url_length>1023){
                $this->output(1, '曝光监控地址 字段长度最小 1 字节，长度最大 1023 字节');
            }
        }else{
            unset($info['impression_tracking_url']);
        }

        if($info['click_tracking_url']){
            $click_tracking_url_length = strlen($info['click_tracking_url']);
            if($click_tracking_url_length<1 || $click_tracking_url_length>1023){
                $this->output(1, '点击监控地址 字段长度最小 1 字节，长度最大 1023 字节');
            }
        }else{
            unset($info['click_tracking_url']);
        }

        if(isset($info['feeds_interaction_enabled']) && $info['feeds_interaction_enabled']){
            if(!isset($this->marketingConfig['INTERACTION'][$info['feeds_interaction_enabled']])){
                $this->output(1, '是否支持赞转评参数错误');
            }
        }else{
            unset($info['feeds_interaction_enabled']);
        }

        return $info;
    }

    /**
     * 获取广告（ads/get）
     */
    public function getAction(){
        $info = $this->getInput(array('ad_id', 'page', 'page_size', 'configured_status', 'system_status', 'ad_name', 'campaign_id', 'adgroup_id'));
        $params = $this->checkGetParam($info);

        $result = $this->send($params, 'get');
        $resultArr = json_decode($result, TRUE);
//        $resultArr['data'] = $this->parseGdtList($resultArr['data'], array('site_set[]'=>'SITE_SET', 'product_type'=>'PRODUCT_TYPE', ));

        $report = intval($this->getInput('report'));
        if($report) {
            # 获取曝光量，点击量，点击率，点击均价，价格
            $resultArr = $this->getDailyReports($resultArr, [
                'level' => 'AD',
                'start_date' => $params['start_date'],
                'end_date' => $params['end_date'],
                'page' => $params['page'],
                'page_size' => $params['page_size'],
                'field' => 'ad_id',
            ]);
        }
        $this->output($resultArr['code'], $resultArr['message'], $resultArr['data']);
    }

    /**
     * 检查获取广告创意的参数
     * @param type $info
     * @return type
     */
    private function checkGetParam($info){

        if(isset($info['ad_id']) && $info['ad_id'] ){
            $info['ad_id'] = intval($info['ad_id']);
            if(empty($info['ad_id'])){
                $this->output(1, '广告创意id必须是整数');
            }
        }else{
            unset($info['ad_id']);
        }

        //客户设置的状态
        if($info['configured_status']){
            if(!isset($this->marketingConfig['AD_STATUS'][$info['configured_status']])){
                $this->output(1, '客户设置的状态参数错误');
            }
            $configured_status_arr = array(
                'field'=>'configured_status',
                'operator'=>'EQUALS',
                'values'=>array($info['configured_status']),
            );
            $info['filtering'][] = $configured_status_arr;
            unset($info['configured_status']);
        }

        //系统状态
        if($info['system_status']){
            if(!isset($this->marketingConfig['AD_SYSTEM_STATUS'][$info['system_status']])){
                $this->output(1, '系统状态参数错误');
            }
            $system_status_arr = array(
                'field'=>'system_status',
                'operator'=>'EQUALS',
                'values'=>array($info['system_status']),
            );

            $info['filtering'][] = $system_status_arr;
            unset($info['system_status']);
        }

        //接收广告创意名称
        if($info['ad_name']){
            $ad_name_length = strlen($info['ad_name']);
            if($ad_name_length<1 || $ad_name_length>120){
                $this->output(1, '广告创意名称字段长度最小 1 字节，长度最大 120 字节');
            }
            $ad_name_arr = array(
                'field'=>'ad_name',
                'operator'=>'CONTAINS',
                'values'=>array($info['ad_name']),
            );
            $info['filtering'][] = $ad_name_arr;
            unset($info['ad_name']);
        }

        //推广计划id
        if($info['campaign_id']){
            $info['campaign_id'] = intval($info['campaign_id']);
            if(empty($info['campaign_id'])){
                $this->output(1, '推广计划id参数错误');
            }
            $campaign_id_arr = array(
                'field'=>'campaign_id',
                'operator'=>'EQUALS',
                'values'=>array($info['campaign_id']),
            );
            $info['filtering'][] = $campaign_id_arr;
            unset($info['campaign_id']);
        }

        //推广计划id
        if($info['adgroup_id']){
            $info['adgroup_id'] = intval($info['adgroup_id']);
            if(empty($info['adgroup_id'])){
                $this->output(1, '推广计划id参数错误');
            }
            $adgroup_id_arr = array(
                'field'=>'adgroup_id',
                'operator'=>'EQUALS',
                'values'=>array($info['adgroup_id']),
            );
            $info['filtering'][] = $adgroup_id_arr;
            unset($info['adgroup_id']);
        }

        if(empty($info['filtering'])){
            unset($info['filtering']);
        }

        if(isset($info['page']) && $info['page']){
            $info['page'] = intval($info['page']);
            if($info['page']<1 || $info['page']>99999){
                $this->output(1, '页码最小值 1，最大值 99999');
            }
        }else{
            unset($info['page']);
        }

        if(isset($info['page_size']) && $info['page_size']){
            $info['page_size'] = intval($info['page_size']);
            if($info['page_size']<1 || $info['page_size']>100){
                $this->output(1, '每页显示的数据条数最小值 1，最大值 100');
            }
        }else{
            unset($info['page_size']);
        }

        return $info;
    }

    /**
     * 删除广告（ads/delete）
     */
    public function deleteAction(){
        $adId = $this->getInput('ad_id');
        if(empty($adId)){
            $this->output(1, '广告id不能为空');
        }
        $data = array(
            'ad_id'=>$adId,
        );
        $result = $this->send($data, 'delete');
        $resultArr = json_decode($result, TRUE);
        $this->output($resultArr['code'], $resultArr['message'], $resultArr['data']);
    }

    /**
     * 获取广告（ads/export）
     */
    public function exportAction(){
        $info = $this->getInput(array('ad_id', 'page', 'page_size', 'filtering', 'configured_status', 'system_status', 'ad_name', 'campaign_id', 'adgroup_id'));
        $params = $this->checkGetParam($info);

        $result = $this->send($params, 'get');
        $adResultArr = json_decode($result, TRUE);
        $adResultArr['data'] = $this->parseGdtList($adResultArr['data'], array(
            'configured_status'=>'AD_STATUS',
            'system_status'=>'AD_SYSTEM_STATUS'));

        # 获取曝光量，点击量，点击率，点击均价，价格
        $adResultArr = $this->getDailyReports($adResultArr, [
            'level' => 'AD',
            'start_date' => $params['start_date'],
            'end_date' => $params['end_date'],
            'page' => $params['page'],
            'page_size' => $params['page_size'],
            'field' => 'ad_id',
        ]);

        if($adResultArr['code']){
            $this->output($adResultArr['code'], $adResultArr['message'], $adResultArr['data']);
        }else{
            $ads = $adResultArr['data']['list'];
        }

        Yaf_loader::import("Util/PHPExcel/PHPExcel.php");
        $objPHPExcel = new PHPExcel();
        /*以下是一些设置，作者、标题啊之类*/
        $objPHPExcel->getProperties()->setCreator("marketing api")
            ->setLastModifiedBy("marketing api")
            ->setTitle("广告EXCEL导出")
            ->setSubject("广告EXCEL导出")
            ->setDescription("广告列表")
            ->setKeywords("excel")
            ->setCategory("result file");
        /*以下就是对处理Excel里的数据，横着取数据*/

        $all_field =  array(
//            "reject_message" => "审核消息",
//            "feeds_interaction_enabled" => "是否支持赞转评",

            "ad_id" => "广告id",
            "ad_name" => "广告名称",
            "campaign_id" => "推广计划 id",
            "adgroup_id" => "广告组 id",
            "configured_status_name" => "客户设置的状态",
            "system_status_name" => "系统状态",
            "impression_tracking_url" => "曝光监控地址",
            "click_tracking_url" => "点击监控地址",
//            "adcreative" => "创意",

            "created_time" => "创建时间",
            "last_modified_time" => "最后修改时间",

            "impression" => "曝光",
            "click" => "点击",
            "click_rate" => "点击率",
            "cost_per_click" => "点击均价",
            "cost" => "价格",
        );
        $num = 1;
        $char = 'A';
        foreach($all_field as $field_key => $field_val){
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($char.$num, $field_val);
            $char ++;
        }
        foreach($ads as $data_key => $data_val){
            $num ++;
            $char = 'A';
            foreach($data_val as $data_val_key =>$data_val_value){
                if(is_array($data_val_value)){
                    // 若是数组格式，则需要转化成字符串, 如：$data_val_key为site_set, adcreative_elements时需转成字符串
                    if($data_val_key == 'site_set'){
                        $data_val[$data_val_key] = implode(',', $data_val[$data_val_key]);
                    }
                    if($data_val_key == 'adcreative'){
                        $tmpStr = "adcreative：\n";
                        foreach($data_val_value as $k=>$v){
                            if($k=='adcreative_elements'){
                                $tmpStr2 = "adcreative_elements:\n";
                                foreach($v as $k2=>$v2){
                                    $tmpStr2 .="  ".$k2 . ":". $v2. "\n";
                                }
                                $tmpStr2.="\n";
                                $tmpStr .= $tmpStr2;
                            }else if ($k=='site_set'){
                                $tmpStr2 = "site_set:\n";
                                $tmpStr2 .= "  ".implode(',', $data_val[$data_val_key][$k]);
                                $tmpStr2.="\n";
                                $tmpStr .= $tmpStr2;
                            }else{
                                $tmpStr .=$k . ":". $v. "\n";
                            }
                        }
                        $tmpStr .= "\n";
                        $data_val[$data_val_key] = $tmpStr;
                    }
                }
            }
            foreach($all_field as $field_key => $field_val){
                $objPHPExcel->setActiveSheetIndex(0)->setCellValue($char.$num, $data_val[$field_key]);
                $char ++;
            }
        }
        // 开始组合头
        $xml_name = "广告列表";
        $objPHPExcel->getActiveSheet()->setTitle('User');
        $objPHPExcel->setActiveSheetIndex(0);
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="'.$xml_name.'.xls"');
        header('Cache-Control: max-age=0');
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
        exit;

    }


    /**
     * 广告
     * 批量修改
     */
    public function batchChangeAction(){
        $params = $this->getInput(array('ids', 'configured_status'));
        if(empty($params['ids'])){
            $this->output(-1, '请先选择要操作的广告');
        }
        $data = $this->checkBatchChangeParam($params);
        if(empty($data)){
            $this->output(-1, '请选择要操作的内容');
        }
        # 批量修改
        $successNum = 0;
        $failedNum = 0;
        $failedMsg = [];
        foreach($params['ids'] as $value){
            $data['ad_id'] = $value;
            $result = $this->send($data, 'update');
            $resultArr = json_decode($result, TRUE);
            if($resultArr['code'] == 0){
                $successNum ++;
            }else{
                $failedNum ++;
                $failedMsg[] = $resultArr['message'];
            }
        }
        if($failedNum == 0){
            $this->output(0, $successNum.'个修改成功');
        }elseif($successNum == 0){
            $message = implode('; ', $failedMsg);
            $this->output(-1, '修改失败，原因：'.$message);
        }else{
            $message = implode('; ', $failedMsg);
            $this->output(0, $successNum.'个修改成功，'.$failedNum.'个修改失败，原因：'.$message);
        }

    }

    /**
     * 校验批量编辑参数
     * @param $params
     * @return array
     */
    private function checkBatchChangeParam($params){
        $data = [];
        if(isset($params['configured_status'])){
            if(!isset($this->marketingConfig['AD_STATUS'][$params['configured_status']])){
                $this->output(-1, '状态参数错误');
            }
            $data['configured_status'] = $params['configured_status'];
        }
        return $data;
    }

    /**
     * 广告组
     * 批量删除
     */
    public function batchDeleteAction(){
        $params = $this->getInput(array('ids'));
        if(empty($params['ids'])){
            $this->output(-1, '请先选择要删除的广告');
        }
        # 批量修改
        $successNum = 0;
        $failedNum = 0;
        $failedMsg = [];
        foreach($params['ids'] as $value){
            $data['ad_id'] = $value;
            $result = $this->send($data, 'delete');
            $resultArr = json_decode($result, TRUE);
            if($resultArr['code'] == 0){
                $successNum ++;
            }else{
                $failedNum ++;
                $failedMsg[] = $resultArr['message'];
            }
        }
        if($failedNum == 0){
            $this->output(0, $successNum.'个删除成功');
        }elseif($successNum == 0){
            $message = implode('; ', $failedMsg);
            $this->output(-1, '删除失败，原因：'.$message);
        }else{
            $message = implode('; ', $failedMsg);
            $this->output(0, $successNum.'个删除成功，'.$failedNum.'个删除失败，原因：'.$message);
        }
    }
}