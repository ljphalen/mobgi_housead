<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
/**
 * Created by PhpStorm.
 * User: kyle.ke
 * Date: 2018/3/12
 * Time: 15:39
 */
class Marketing_AdcreativetemplateinfoController extends Admin_MarketingController {

    public $resourceName = 'adcreative_template_info';

    /**
     * 获取创意规格信息（adcreative_template_info/get）
     */
    public function getAction(){
        $info = $this->getInput(array('adcreative_template_id', 'site_set', 'product_type', 'page', 'page_size'));
        $data = $this->checkGetParam($info);

        $result = $this->send($data, 'get');
        $resultArr = json_decode($result, TRUE);
        $resultArr = $this->formatTemplateInfo($resultArr);
//        $adcreativeResultArr['data'] = $this->parseGdtList($adcreativeResultArr['data'], array('product_type'=>'PRODUCT_TYPE', ));

        $this->output($resultArr['code'], $resultArr['message'], $resultArr['data']);
    }

    /**
     * 检查获取广告创意的参数
     * @param type $info
     * @return type
     */
    private function checkGetParam($info){

        if(isset($info['adcreative_template_id']) && $info['adcreative_template_id'] ){
            $info['adcreative_template_id'] = intval($info['adcreative_template_id']);
            if(empty($info['adcreative_template_id'])){
                $this->output(1, '创意规格 id必须是整数');
            }
            $adcreative_template_id = array(
                'field'=>'adcreative_template_id',
                'operator'=>'EQUALS',
                'values'=>array($info['adcreative_template_id']),
            );
            $info['filtering'][] = $adcreative_template_id;
        }else{
            unset($info['adcreative_template_id']);
        }

        if(isset($info['site_set']) && $info['site_set'] ){
            if(!isset($this->marketingConfig['SITE_SET'][$info['site_set']])){
                $this->output(1, '投放站点参数错误: '. $info['site_set']);
            }
            $site_set = array(
                'field'=>'site_set',
                'operator'=>'EQUALS',
                'values'=>array($info['site_set']),
            );
            $info['filtering'][] = $site_set;
        }else{
            unset($info['site_set']);
        }

        if(isset($info['product_type']) && $info['product_type'] ){
            if(!isset($this->marketingConfig['PRODUCT_TYPE'][$info['product_type']])){
                $this->output(1, '标的物类型参数错误: '. $info['product_type']);
            }
            $product_type = array(
                'field'=>'product_type',
                'operator'=>'EQUALS',
                'values'=>array($info['product_type']),
            );
            $info['filtering'][] = $product_type;
        }else{
            unset($info['product_type']);
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
            $info['page'] = 1;
        }

        if(isset($info['page_size']) && $info['page_size']){
            $info['page_size'] = intval($info['page_size']);
            if($info['page']<1 || $info['page']>99999){
                $this->output(1, '每页显示的数据条数最小值 1，最大值 100');
            }
        }else{
            $info['page_size'] = 100;
        }
        return $info;
    }

    private function formatTemplateInfo($data){
        $mkAdcreativeTemplate = Common::getConfig('mkAdcreativeTemplate');
        foreach($data['data']['list'] as $key => $value){
            if(isset($mkAdcreativeTemplate[$value['adcreative_template_id']])){
                $data['data']['list'][$key]['adcreative_template_name'] = $mkAdcreativeTemplate[$value['adcreative_template_id']]['adcreative_template_name'];
                $data['data']['list'][$key]['adcreative_template_style'] = $mkAdcreativeTemplate[$value['adcreative_template_id']]['adcreative_template_style'];
                $data['data']['list'][$key]['adcreative_template_size'] = $mkAdcreativeTemplate[$value['adcreative_template_id']]['adcreative_template_size'];
                $data['data']['list'][$key]['adcreative_template_desc'] = $mkAdcreativeTemplate[$value['adcreative_template_id']]['adcreative_template_desc'];
            }else{
                $data['data']['list'][$key]['adcreative_template_name'] = '未知';
                $data['data']['list'][$key]['adcreative_template_style'] = '未知';
                $data['data']['list'][$key]['adcreative_template_size'] = '未知';
                $data['data']['list'][$key]['adcreative_template_desc'] = $data['data']['list'][$key]['adcreative_sample_image']['name'];
            }
            // 获取创意规格api的 自动优化图片排序 参数错误
            foreach($value['adcreative_elements'] as $eleKey => $eleVal){
                if($eleVal['name'] == 'sortable'){
                    $data['data']['list'][$key]['adcreative_elements'][$eleKey]['name'] = 'multi_share_optimized';
                    $data['data']['list'][$key]['adcreative_elements'][$eleKey]['enum_property'] =[
                        'default' => 'NO',
                        'enumeration' => [['value'=>'YES','description'=>'自动优化'],['value'=>'NO','description'=>'顺序播放']]
                    ];
                }
            }
        }
//        foreach($data['data']['list'] as $key => $value){
//            $elementDetail = [ 'text_num' => 0, 'text_msg' => [], 'image_num' => 0, 'video_num' => 0, 'enum_num' => 0];
//            foreach($value['adcreative_elements'] as $element){
//                switch ($element['element_type']) {
//                    case 'ELEMENT_TYPE_TEXT':
//                        $elementDetail['text_num'] ++;
//                        $elementDetail['text_msg'][] = $element['restriction']['text_restriction']['max_length'] .'字';
//                        break;
//                    case 'ELEMENT_TYPE_IMAGE':
//                        $elementDetail['image_num'] ++;
//                        $width = isset($element['restriction']['image_restriction']['width']) ? $element['restriction']['image_restriction']['width'] :  $element['restriction']['image_restriction']['min_width'];
//                        $height = isset($element['restriction']['image_restriction']['height']) ? $element['restriction']['image_restriction']['height'] :  $element['restriction']['image_restriction']['min_height'];
//                        $elementDetail['image_msg'] = $width . '×' . $height;
//                        break;
//                    case 'ELEMENT_TYPE_VIDEO':
//                        $elementDetail['video_num'] ++;
//                        $width = isset($element['restriction']['video_restriction']['width']) ? $element['restriction']['video_restriction']['width'] :  $element['restriction']['video_restriction']['min_width'];
//                        $height = isset($element['restriction']['video_restriction']['height']) ? $element['restriction']['video_restriction']['height'] :  $element['restriction']['video_restriction']['min_height'];
//                        $elementDetail['video_msg'] = $width . '×' . $height;
//                        break;
//                    case 'ELEMENT_TYPE_ENUM':
//                        $elementDetail['enum_num'] ++;
//                        break;
//                }
//            }
//            if($elementDetail['enum_num']){
//                $data['data']['list'][$key]['crt_style'] = '随心互动';
//                $data['data']['list'][$key]['crt_limit'] = $elementDetail['image_msg'];
//            }elseif($elementDetail['video_num']){
//                $data['data']['list'][$key]['crt_style'] = '视频';
//                $data['data']['list'][$key]['crt_limit'] = $elementDetail['video_msg'];
//            }elseif($elementDetail['image_num'] == 1){
//                $data['data']['list'][$key]['crt_style'] = $elementDetail['text_num'] ? '单图(文)' : '单图';
//                $data['data']['list'][$key]['crt_limit'] = $elementDetail['image_msg'];
//            }elseif($elementDetail['image_num'] > 1){
//                $data['data']['list'][$key]['crt_style'] = $elementDetail['text_num'] ? '多图(文)' : '多图轮播';
//                $data['data']['list'][$key]['crt_limit'] = $elementDetail['image_msg'];
//            }elseif($elementDetail['text_num']){
//                $data['data']['list'][$key]['crt_style'] = '文字链';
//                $textMsg = implode('+', $elementDetail['text_msg']);
//                $data['data']['list'][$key]['crt_limit'] = $textMsg;
//            }
//        }
        return $data;
    }

}