<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
/**
 * Created by PhpStorm.
 * User: kyle.ke
 * Date: 2018/3/18
 * Time: 19:22
 */
class Marketing_VideosController extends Admin_MarketingController{

    public $resourceName = 'videos';

    /**
     * 添加视频文件（videos/add）
     */
    public function addAction(){
        $info = $this->getInput(array('description'));
        $info = $this->checkAddParam($info);
        $original = $_FILES['file']['tmp_name'];
        $signature = md5_file($original);
        $accountId = $this->getGdtAccountId();
        $saveName = $accountId . '_' . time();
        $videoData = MobgiMarket_Service_MaterialModel::getVideoBySignature($accountId, $signature);
        $attachroot = Yaf_Application::app()->getConfig()->attachroot;
        $attachPath = Common::getConfig ( 'siteConfig', 'attachPath' );
        $attachUrl = sprintf ( '%s/%s', $attachroot, 'attachs' );
        if($videoData){
            $this->output(0, '该视频已经上传过', [ 'video_id'=> $videoData['video_id'] , 'preview_url' =>  $attachUrl . $videoData['preview_url'] ]);
        }
        # 保存文件到服务器，待使用
        $filePath = sprintf ( '%s/%s/%s', 'marketing', 'video', date ( 'Ym' ) );
        $savePath = sprintf ( '%s/%s', $attachPath, $filePath );
        $uploader = new Util_Upload ( array('maxSize'=>2048,'allowFileType'=>array('mp4','jpeg')) );
        $ret = $uploader->upload('file', $saveName, $savePath);
        if(!$ret){
            $this->output(-1, '上传失败');
        }
        $fileSource = realpath($ret['source']);
        $cfile = new \CURLFile($fileSource);
        $data = array(
            'video_file'=> $cfile,
            'signature'=> $signature,
            'description'=>$info['description'],
        );
        $result = $this->send($data, 'add', 'videos', 'filepost');
        $resultArr = json_decode($result, TRUE);
        if($resultArr['code'] == 0){
            # 获取视频信息，存放本地
            $videoGetData = $this->send(['video_id'=>$resultArr['data']['video_id']], 'get', 'videos');
            $videoGetResult = json_decode($videoGetData, true);
            $params = array(
                'account_id' => $accountId,
                'video_id' => $videoGetResult['data']['video_id'],
                'signature' => $signature,
                'type' => $videoGetResult['data']['type'],
                'height' => $videoGetResult['data']['height'],
                'width' => $videoGetResult['data']['width'],
                'file_size' => $videoGetResult['data']['file_size'],
                'preview_url' => sprintf ( '/%s/%s', $filePath, $ret['newName'] ),
            );
            MobgiMarket_Service_MaterialModel::addVideo($params);
            $resultArr['message'] = !empty($resultArr['message']) ? : '上传成功';
            $resultArr['data']['preview_url'] = sprintf ( '%s/%s/%s', $attachUrl, $filePath, $ret['newName'] );
        }
        $this->output($resultArr['code'], $resultArr['message'], $resultArr['data']);
    }

    /**
     * 检查创建推广计划的参数
     * @param type $info
     * @return type
     */
    private function checkAddParam($info){
        //检测文件格式
        $ext = strtolower(substr(strrchr($_FILES['file']['name'], '.'), 1));
        if(!in_array($ext, ['mp4','avi','mov','flv'])){
            $this->output(-1, '只支持MP4/AVI/MOV/FLV文件');
        }
        //检测宽高
//        $imagesizeInfo =  getimagesize($_FILES['file']['tmp_name']);
        // 视频长度小于 5s
        //检测大小
        $size = filesize($_FILES['file']['tmp_name']);
        $sizeMb = $size / 1024 /1024.0;
        if($sizeMb > 50){
            $this->output(1, '小于等于 50M');
        }
        return $info;
    }

    /**
     * 获取视频信息（videos/get）
     */
    public function getAction(){
        $info = $this->getInput(array('video_id', 'page', 'page_size', 'video_width', 'video_height'));
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

        if(isset($info['video_id']) && $info['video_id'] ){
            $info['video_id'] = intval($info['video_id']);
            if(empty($info['video_id'])){
                $this->output(1, '视频id必须是整数');
            }
        }else{
            unset($info['video_id']);
        }
        return $info;
    }


}