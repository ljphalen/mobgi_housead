<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
/**
 * Created by PhpStorm.
 * User: kyle.ke
 * Date: 2018/3/12
 * Time: 15:38
 */
class Marketing_AdcreativesController extends Admin_MarketingController {

    public $resourceName = 'adcreatives';

    /**
     * 创建广告创意（adcreatives/add）
     */
    public function addAction(){
        $info = $this->getInput(array(
            'campaign_id', 'campaign_type', 'adcreative_template_id',
            'adcreative_elements', 'destination_url',
            'site_set', 'product_type', 'deep_link', 'product_refs_id', 'share_info',
        ));
        $data = $this->checkAddParam($info);
        $adcreativeElements = $_GET['adcreative_elements'];
        if(empty($adcreativeElements)){
            $this->output(-1, '创意元素不能为空');
        }
        # 循环创建创意
        $successNum = 0;
        $failedNum = 0;
        $failedMsg = [];
        # 校验创意名是否重复
        foreach($adcreativeElements as $key => $value){
            $elements = json_decode($value,TRUE);
            $adcreativeName = trim($elements['adcreative_name']);
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
        foreach($adcreativeElements as $value){
            $data['adcreative_elements'] = json_decode($value,TRUE);
            $data['adcreative_name'] = trim($data['adcreative_elements']['adcreative_name']);
            unset($data['adcreative_elements']['adcreative_name']);
            $result = $this->send($data, 'add');
            $resultArr = json_decode($result, TRUE);
            if($resultArr['code'] == 0){
                $successNum ++;
            }else{
                $failedNum ++;
                $failedMsg[] = $resultArr['message'];
            }
        }
        if($failedNum == 0){
            $this->output(0, $successNum.'个创建成功');
        }elseif($successNum == 0){
            $message = implode('; ', $failedMsg);
            $this->output(-1, '创建失败，原因：'.$message);
        }else{
            $message = implode('; ', $failedMsg);
            $this->output(0, $successNum.'个创建成功，'.$failedNum.'个创建失败，原因：'.$message);
        }
    }

    /**
     * 检查创建广告创意的参数
     * @param type $info
     * @return type
     */
    private function checkAddParam($info){
        $info['campaign_id'] = intval($info['campaign_id']);
        if(empty($info['campaign_id'])){
            $this->output(1, '推广计划 id不能为空');
        }
        if(empty($info['product_type'])){
            $this->output(1, '标的物类型 不能为空');
        }
        if(!array_key_exists($info['product_type'], $this->marketingConfig['PRODUCT_TYPE'])){
            $this->output(1, '标的物类型参数错误');
        }

        if(empty($info['site_set'])){
            $this->output(1, '投放站点 不能为空');
        }
        if(!array_key_exists($info['site_set'], $this->marketingConfig['SITE_SET'])){
            $this->output(1, '投放站点不存在');
        }
        $info['site_set'] = [ $info['site_set']];
        if(empty($info['adcreative_template_id'])){
            $this->output(1, '创意规格 id不能为空');
        }

        $info['destination_url'] = trim(html_entity_decode($info['destination_url']));
        $destination_url_length = strlen($info['destination_url']);
        if($destination_url_length<1 || $destination_url_length>1023){
            $this->output(1, '落地页 url 字段长度最小 1 字节，长度最大 1023 字节');
        }

        if(isset($info['deep_link']) && $info['deep_link']){
            $deep_link_length = strlen($info['deep_link']);
            if($deep_link_length<1 || $deep_link_length>2048){
                $this->output(1, '应用直达页 URL 字段长度最小 0 字节，长度最大 2048 字节');
            }
        }

        if(isset($info['product_refs_id']) && $info['product_refs_id']){
            $product_refs_id_length = strlen($info['product_refs_id']);
            if($product_refs_id_length<1 || $product_refs_id_length>128){
                $this->output(1, '标的物 id，详见 [标的物类型] 字段长度最小 0 字节，长度最大 128 字节');
            }
        }

        if($info['campaign_type'] == 'CAMPAIGN_TYPE_WECHAT_MOMENTS'){
            if($info['share_info']['share_title'] && $info['share_info']['share_description']){
                $share_title_length = strlen($info['share_info']['share_title']);
                if($share_title_length<1 || $share_title_length>14){
                    $this->output(1, '分享标题 字段长度最小 1 字节，长度最大 14 字节');
                }
                $share_description_length = strlen($info['share_info']['share_description']);
                if($share_description_length<1 || $share_description_length>20){
                    $this->output(1, '分享描述 字段长度最小 1 字节，长度最大 20 字节');
                }
            }else{
                unset($info['share_info']);
            }
        }else{
            unset($info['share_info']);
        }
        unset($info['campaign_type']);
        return $info;
    }

    /**
     * 更新广告创意（adgroups/update）
     */
    public function updateAction(){
        $info = $this->getInput(array(
            'adcreative_id', 'deep_link', 'share_info', 'destination_url'
        ));
        $info['destination_url'] = html_entity_decode($info['destination_url']);
        $data = $this->checkUpdateParam($info);

        $adcreativeElements = $_GET['adcreative_elements'];
        if(empty($adcreativeElements)){
            $this->output(-1, '创意元素不能为空');
        }
        # 循环修改创意
        $successNum = 0;
        $failedNum = 0;
        $failedMsg = [];
        # 校验创意名是否重复
        foreach($adcreativeElements as $key => $value){
            $elements = json_decode($value,TRUE);
            $adcreativeId = intval($elements['adcreative_id']);
            if(empty($adcreativeId)){
                $this->output(-1, '第'. ($key + 1) .'个创意 id为空');
            }
            $adcreativeName = trim($elements['adcreative_name']);
            if(empty($adcreativeName)){
                $this->output(-1, '第'. ($key + 1) .'个创意的创意名为空');
            }
            if($this->checkUnitName('adcreatives', 'adcreative_name', $adcreativeName, 'adcreative_id', $adcreativeId)){
                $this->output(-1, '第'. ($key + 1) .'个创意的创意名已被占用');
            }
        }
        foreach($adcreativeElements as $value){
            $data['adcreative_elements'] = json_decode($value,TRUE);
            $data['adcreative_id'] = intval($data['adcreative_elements']['adcreative_id']);
            $data['adcreative_name'] = trim($data['adcreative_elements']['adcreative_name']);
            unset($data['adcreative_elements']['adcreative_id']);
            unset($data['adcreative_elements']['adcreative_name']);
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
            $this->output(-1, '修改失败');
        }else{
            $message = implode('; ', $failedMsg);
            $this->output(0, $successNum.'个修改成功，'.$failedNum.'个修改失败，原因：'.$message);
        }
    }

    /**
     * 检查更新广告创意的参数
     * @param type $info
     * @return type
     */
    private function checkUpdateParam($info){
        $info['destination_url'] = html_entity_decode($info['destination_url']);
        if(isset($info['destination_url']) && $info['destination_url']){
            $destination_url_length = strlen($info['destination_url']);
            if($destination_url_length<1 || $destination_url_length>1023){
                $this->output(1, '落地页 url 字段长度最小 1 字节，长度最大 1023 字节');
            }
        }else{
            unset($info['destination_url']);
        }

        if(isset($info['deep_link']) && $info['deep_link']){
            $deep_link_length = strlen($info['deep_link']);
            if($deep_link_length<1 || $deep_link_length>2048){
                $this->output(1, '应用直达页 URL 字段长度最小 0 字节，长度最大 2048 字节');
            }
        }else{
            unset($info['deep_link']);
        }

        if($info['campaign_type'] == 'CAMPAIGN_TYPE_WECHAT_MOMENTS'){
            if($info['share_info']['share_title'] && $info['share_info']['share_description']){
                $share_title_length = strlen($info['share_info']['share_title']);
                if($share_title_length<1 || $share_title_length>14){
                    $this->output(1, '分享标题 字段长度最小 1 字节，长度最大 14 字节');
                }
                $share_description_length = strlen($info['share_info']['share_description']);
                if($share_description_length<1 || $share_description_length>20){
                    $this->output(1, '分享描述 字段长度最小 1 字节，长度最大 20 字节');
                }
            }else{
                unset($info['share_info']);
            }
        }else{
            unset($info['share_info']);
        }

        return $info;
    }


    /**
     * 获取广告创意（adcreatives/get）
     */
    public function getAction(){
        $info = $this->getInput(array('adcreative_id', 'page', 'page_size', 'adcreative_name', 'product_type', 'campaign_id', 'adcreative_template_id','show_image'));
        $data = $this->checkGetParam($info);

        $result = $this->send($data, 'get');
        $adcreativeResultArr = json_decode($result, TRUE);
        if($info['show_image'] == 1){ // 某些页面需要预览图
            $adcreativeResultArr = $this->formatImageUrl($adcreativeResultArr);
        }
        $adcreativeResultArr['data'] = $this->parseGdtList($adcreativeResultArr['data'], array('product_type'=>'PRODUCT_TYPE', ));

        $this->output($adcreativeResultArr['code'], $adcreativeResultArr['message'], $adcreativeResultArr['data']);
    }

    /**
     * 构造图片预览图
     * @param $data
     */
    private function formatImageUrl($data){
        foreach($data['data']['list'] as $key => $value){
            if(isset($value['adcreative_elements']['image'])){
                $result = $this->getFirstData([ 'image_id' => $value['adcreative_elements']['image']], 'get', 'images');
                $resultArr = json_decode($result, TRUE);
                if($resultArr['code'] == 0){
                    $data['data']['list'][$key]['adcreative_elements']['image_url'] = $result['preview_url'];
                }
            }
            if(isset($value['adcreative_elements']['element_story'])){
                $result = $this->getFirstData([ 'image_id' => $value['adcreative_elements']['element_story'][0]['image']], 'get', 'images');
                $resultArr = json_decode($result, TRUE);
                if($resultArr['code'] == 0){
                    $data['data']['list'][$key]['adcreative_elements']['image_url'] = $result['preview_url'];
                }
            }
        }
        return $data;
    }

    /**
     * 检查获取广告创意的参数
     * @param type $info
     * @return type
     */
    private function checkGetParam($info){

        if(isset($info['adcreative_id']) && $info['adcreative_id'] ){
            $info['adcreative_id'] = intval($info['adcreative_id']);
            if(empty($info['adcreative_id'])){
                $this->output(1, '广告创意id必须是整数');
            }
        }else{
            unset($info['adcreative_id']);
        }

        //接收推广计划参数
        if($info['campaign_id']){
            $info['campaign_id'] = intval($info['campaign_id']);
            if(empty($info['campaign_id'])){
                $this->output(1, '推广计划参数错误');
            }
            $filterArr = array(
                'field'=>'campaign_id',
                'operator'=>'EQUALS',
                'values'=>array($info['campaign_id']),
            );
            $info['filtering'][] = $filterArr;
            unset($info['campaign_id']);
        }

        //接收广告创意名称
        if($info['adcreative_name']){
            $adcreative_name_length = strlen($info['adcreative_name']);
            if($adcreative_name_length<1 || $adcreative_name_length>120){
                $this->output(1, '广告创意名称字段长度最小 1 字节，长度最大 120 字节');
            }
            $adcreative_name_arr = array(
                'field'=>'adcreative_name',
                'operator'=>'CONTAINS',
                'values'=>array($info['adcreative_name']),
            );
            $info['filtering'][] = $adcreative_name_arr;
            unset($info['adcreative_name']);
        }

        //接收标的物类型参数
        if($info['product_type']){
            if(!isset($this->marketingConfig['PRODUCT_TYPE'][$info['product_type']])){
                $this->output(1, '标的物类型不存在');
            }
            $filterArr = array(
                'field'=>'product_type',
                'operator'=>'EQUALS',
                'values'=>array($info['product_type']),
            );
            $info['filtering'][] = $filterArr;
            unset($info['product_type']);
        }

        //接收广告创意规格id
        if($info['adcreative_template_id']){
            $arr = array(
                'field'=>'adcreative_template_id',
                'operator'=>'EQUALS ',
                'values'=>array($info['adcreative_template_id']),
            );
            $info['filtering'][] = $arr;
            unset($info['adcreative_template_id']);
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
        unset($info['show_image']);

        return $info;
    }

    /**
     * 删除广告创意（adcreatives/delete）
     */
    public function deleteAction(){
        $adcreativeId = $this->getInput('adcreative_id');
        if(empty($adcreativeId)){
            $this->output(1, '广告组id不能为空');
        }
        $data = array(
            'adcreative_id'=>$adcreativeId,
        );
        $result = $this->send($data, 'delete');
        $resultArr = json_decode($result, TRUE);
        $this->output($resultArr['code'], $resultArr['message'], $resultArr['data']);
    }

    /**
     * 导出广告创意excel
     */
    public function exportAction(){
        $info = $this->getInput(array('adcreative_id', 'page', 'page_size', 'adcreative_name', 'campaign_id'));
        $data = $this->checkGetParam($info);

        $result = $this->send($data, 'get');
        $adcreativeResultArr = json_decode($result, TRUE);
        $adcreativeResultArr['data'] = $this->parseGdtList($adcreativeResultArr['data'], array('product_type'=>'PRODUCT_TYPE', ));

        if($adcreativeResultArr['code']){
            $this->output($adcreativeResultArr['code'], $adcreativeResultArr['message'], $adcreativeResultArr['data']);
        }else{
            $adcreatives = $adcreativeResultArr['data']['list'];
        }

        Yaf_loader::import("Util/PHPExcel/PHPExcel.php");
        $objPHPExcel = new PHPExcel();
        /*以下是一些设置，作者、标题啊之类*/
        $objPHPExcel->getProperties()->setCreator("marketing api")
            ->setLastModifiedBy("marketing api")
            ->setTitle("广告组EXCEL导出")
            ->setSubject("广告组EXCEL导出")
            ->setDescription("广告组列表")
            ->setKeywords("excel")
            ->setCategory("result file");
        /*以下就是对处理Excel里的数据，横着取数据*/

        $all_field =  array(
            "adcreative_id" => "广告创意 id",
            "adcreative_name" => "广告创意名称",
            "destination_url" => "落地页 url",
            "product_type" => "标的物类型",
            "product_refs_id" => "标的物 id",

            "created_time" => "创建时间",
            "last_modified_time" => "最后修改时间",
            "campaign_id" => "推广计划 id",
            "adcreative_elements" => "创意规格",
            "deep_link" => "应用直达页 URL",

            "adcreative_template_id" => "创意模板id",
            "site_set" => "投放站点集合",
        );
        $num = 1;
        $char = 'A';
        foreach($all_field as $field_key => $field_val){
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($char.$num, $field_val);
            $char ++;
        }
        foreach($adcreatives as $data_key => $data_val){
            $num ++;
            $char = 'A';
            foreach($data_val as $data_val_key =>$data_val_value){
                if(is_array($data_val_value)){
                    // 若是数组格式，则需要转化成字符串, 如：$data_val_key为site_set, adcreative_elements时需转成字符串
                    if($data_val_key == 'site_set'){
                        $data_val[$data_val_key] = implode(',', $data_val[$data_val_key]);
                    }
                    if($data_val_key == 'adcreative_elements'){
                        $tmpStr = '';
                        foreach($data_val_value as $k=>$v){
                            $tmpStr .=$k . ":". $v. "\n";
                        }
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
        $xml_name = "广告创意列表";
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
     * 创意
     * 批量删除
     */
    public function batchDeleteAction(){
        $params = $this->getInput(array('ids'));
        if(empty($params['ids'])){
            $this->output(-1, '请先选择要删除的创意');
        }
        # 批量修改
        $successNum = 0;
        $failedNum = 0;
        $failedMsg = [];
        foreach($params['ids'] as $value){
            $data['adcreative_id'] = $value;
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