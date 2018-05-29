<?php
/**
 * Created by PhpStorm.
 * User: kyle.ke
 * Date: 2017/3/21
 * Time: 14:44
 */
if (!defined('BASE_PATH')) exit('Access Denied!');
class Advertiser_Service_CheckUploadResourceModel{

    /**
     * 上传到广通之前对上传的图片，视频先做图片大小，长，宽，后缀名校验．　视频大小，后缀名校验．
     * @param type $template_id
     * @param string $type
     * @param $file_detail
     * @return string
     */
    public static function checkGdtImage($template_id, $type, $file_detail){
        if(empty($type)){
            $type = 'image';
        }
        $creative_template_refs  = Common::getConfig('deliveryConfig','creative_template_refs');
        $template_ref= $creative_template_refs[$template_id];
        if( ($type=='image'||$type=='image2') && ($template_id == 65 || $template_id==271 || $template_id==351) ){
            //检测文件格式
            $ext = strtolower(substr(strrchr($file_detail['name'], '.'), 1));
            if(!in_array($ext, $template_ref[$type]['file_format'])){
                $response['message'] = '图片格式必须为：'.implode(' ', $template_ref[$type]['file_format']);
                return false;
            }
            //检测宽高
            $imagesizeInfo =  getimagesize($file_detail['tmp_name']);
            $imagewidth = $imagesizeInfo[0];
            $imageheight = $imagesizeInfo[1];
            if($imagewidth != $template_ref[$type]['width']){
                $response['message'] = '图片宽度必须是'.$template_ref[$type]['width'];
                return false;
            }
            if($imageheight != $template_ref[$type]['height']){
                $response['message'] = '图片高度必须是'.$template_ref[$type]['height'];
                return false;
            }
            //检测大小
            $size = filesize($file_detail['tmp_name']);
            $sizekb = $size/1024.0;
            if($sizekb>$template_ref[$type]['file_size_KB_limit']){
                $response['message'] = '图片大小必须 '.$template_ref[$type]['file_size_KB_limit']."k 以内";
                return false;
            }
        }
        return true;
    }

    /**
     * 上传到广通之前对上传的图片，视频先做图片大小，长，宽，后缀名校验．　视频大小，后缀名校验．
     * @param type $template_id
     * @param string $type
     * @param $file_detail
     * @return string
     */
    public static function checkGdtVideo($template_id, $type, $file_detail){
        if(empty($type)){
            $type = 'video';
        }
        $creative_template_refs  = Common::getConfig('deliveryConfig','creative_template_refs');
        $template_ref= $creative_template_refs[$template_id];
        if($type =='video' && $template_id==351){
            //检测文件格式
            $ext = strtolower(substr(strrchr($file_detail['name'], '.'), 1));
            if(!in_array($ext, $template_ref[$type]['file_format'])){
                $response['message'] = '上传格式必须为：'.implode(' ', $template_ref[$type]['file_format']);
                return false;
            }
            //检测大小
            $size = filesize($file_detail['tmp_name']);
            $sizemb = $size/1024.0/1024.0;
            $limitMb = $template_ref[$type]['file_size_KB_limit']/1024.0;
            if($sizemb>$limitMb){
                $response['message'] = '视频大小必须 '.$limitMb."M 以内";
                return false;
            }
        }
        return true;
    }

    /**
     *
     * @param unknown_type $file_detail
     * @param unknown_type $dir
     * @return multitype:unknown_type    默认2M
     * @param array $params array('maxSize' => 文件上传最大,'allowFileType' => 允许上传的文件类型)
     */
    public static function upload($file_detail, $dir, $uploadParams=array('maxSize'=>2048,'allowFileType'=>array('gif','jpeg','jpg','png','bmp','swf', 'txt','csv','apk','mp4','rar','zip','mp3')), $synctocdn = true) {
        $attachPath = Common::getConfig('siteConfig', 'attachPath');

        if ($file_detail['error']) {
            return Common::formatMsg(-1, '上传失败:' . $file_detail['error']);
        }

        $allowType = array();
        if( isset($uploadParams['allowFileType']) && is_array($uploadParams['allowFileType']) ){
            $allowType['allowFileType'] = $uploadParams['allowFileType'];
        }
        if( isset($uploadParams['maxSize']) && $uploadParams['maxSize'] ){
            $allowType['maxSize'] = $uploadParams['maxSize'];
        }
        $savePath = sprintf('%s/%s/%s', $attachPath, $dir, date('Ym'));
        $uploader = new Util_MultipleUpload($allowType);
        $ret = $uploader->upload($file_detail, uniqid(), $savePath);

        if ($ret < 0) {
            $msg = $uploader->errorMsgArr[$ret];
            return Common::formatMsg($ret, '上传失败:'.$msg);
        }
        $filepath = sprintf('/%s/%s/%s', $dir, date('Ym'), $ret['newName']);
        $ext = strtolower(substr(strrchr($file_detail['name'], '.'), 1));
        //if($ext != 'gif') image2webp($attachPath.$filepath, $attachPath.$filepath.".webp");
        if($synctocdn){
            Common::syncToCdn($filepath);
        }
        return Common::formatMsg(0, '', $filepath);
    }
}