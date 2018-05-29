<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
/**
 * Created by PhpStorm.
 * User: kyle.ke
 * Date: 2018/3/18
 * Time: 19:21
 */
class Marketing_ImagesController extends Admin_MarketingController{

    public $resourceName = 'images';

    /**
     * 添加图片文件（images/add）
     */
    public function addAction(){
        $this->checkAddParam();
        $original = $_FILES['file']['tmp_name'];
        $signature = md5_file($original);
        $accountId = $this->getGdtAccountId();
        $saveName = $accountId . '_' . $signature;
        $imageData = MobgiMarket_Service_MaterialModel::getImageBySignature($accountId, $signature);
        $attachroot = Yaf_Application::app()->getConfig()->attachroot;
        $attachPath = Common::getConfig ( 'siteConfig', 'attachPath' );
        $attachUrl = sprintf ( '%s/%s', $attachroot, 'attachs' );
        if($imageData){
            $this->output(0, '该图片已经上传过', [ 'image_id'=> $imageData['image_id'], 'preview_url' =>  $attachUrl . $imageData['preview_url'] ]);
        }
        # 保存文件到服务器，待使用
        $filePath = sprintf ( '%s/%s/%s', 'marketing', 'image', date ( 'Ym' ) );
        $savePath = sprintf ( '%s/%s', $attachPath, $filePath );
        $uploader = new Util_Upload ( array('maxSize'=>2048,'allowFileType'=>array('gif','jpeg','jpg','png','bmp','swf')) );
        $ret = $uploader->upload('file', $saveName, $savePath);
        if(!$ret){
            $this->output(-1, '上传失败');
        }
        $fileSource = realpath($ret['source']);
        $cfile = new \CURLFile($fileSource);
        $data = array(
            'file'=> $cfile,
            'signature'=> $signature,
        );
        $result = $this->send($data, 'add', 'images', 'filepost');
        $resultArr = json_decode($result, TRUE);
        if($resultArr['code'] == 0){
            $params = array(
                'account_id' => $accountId,
                'image_id' => $resultArr['data']['image_id'],
                'signature' => $signature,
                'type' => $resultArr['data']['type'],
                'height' => $resultArr['data']['height'],
                'width' => $resultArr['data']['width'],
                'file_size' => $resultArr['data']['file_size'],
                'preview_url' => sprintf ( '/%s/%s', $filePath, $ret['newName'] ),
            );
            MobgiMarket_Service_MaterialModel::addImage($params);
            $resultArr['message'] = !empty($resultArr['message']) ? : '上传成功';
//            $resultArr['data']['preview_url'] = sprintf ( '%s/%s/%s', $attachUrl, $filePath, $ret['newName'] );
        }
        $this->output($resultArr['code'], $resultArr['message'], $resultArr['data']);
    }

    /**
     * 检查添加图片的参数
     * @return type
     */
    private function checkAddParam(){
        //检测文件格式
        $ext = strtolower(substr(strrchr($_FILES['file']['name'], '.'), 1));
        if(!in_array($ext, ['gif','jpeg','jpg','png','swf'])){
            $this->output(-1, '只支持gif、jpg、jpeg、png、swf 文件');
        }
        //检测宽高
        $imagesizeInfo =  getimagesize($_FILES['file']['tmp_name']);
        $params['width'] = $imagesizeInfo[0];
        $params['height'] = $imagesizeInfo[1];
//        $width = 1024;
//        $height = 1024;
//        if($imagewidth != $width){
//            $this->output(1, '图片宽度必须是'.$width);
//        }
//        if($imageheight != $height){
//            $this->output(1, '图片高度必须是'.$height);
//        }
        //检测大小
        $params['file_size'] = filesize($_FILES['file']['tmp_name']);
        $sizeMb = $params['file_size'] / 1024 /1024.0;
        if($sizeMb > 3){
            $this->output(1, '小于等于 3M');
        }
        return $params;
    }

    /**
     * 获取图片信息（images/get）
     */
    public function getAction(){
        $info = $this->getInput(array('image_id', 'page', 'page_size', 'image_width', 'image_height'));
        $params = $this->checkGetParam($info);

        $result = $this->send($params, 'get');
        $imageResultArr = json_decode($result, TRUE);
        $this->output($imageResultArr['code'], $imageResultArr['message'], $imageResultArr['data']);
    }


    /**
     * 检查获取图片的参数
     * @param type $info
     * @return type
     */
    private function checkGetParam($info){

        if(isset($info['image_id']) && $info['image_id'] ){
            $info['image_id'] = trim($info['image_id']);
            if(empty($info['image_id'])){
                $this->output(1, '图片id必须是整数');
            }
        }else{
            unset($info['image_id']);
        }
        // 图片尺寸
        if($info['image_width']){
            $info['image_width'] = intval($info['image_width']);
            $imageWidthArr = array(
                'field'=>'image_width',
                'operator'=>'EQUALS',
                'values'=>array($info['image_width']),
            );
            $info['filtering'][] = $imageWidthArr;
            unset($info['image_width']);
        }
        if($info['image_height']){
            $info['image_height'] = intval($info['image_height']);
            $imageWidthArr = array(
                'field'=>'image_height',
                'operator'=>'EQUALS',
                'values'=>array($info['image_height']),
            );
            $info['filtering'][] = $imageWidthArr;
            unset($info['image_height']);
        }
        if(empty($info['filtering'])){
            unset($info['filtering']);
        }
        return $info;
    }


}