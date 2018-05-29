<?php

/**
 * @Encoding      :   UTF-8
 * @Author       :   hunter.fang
 * @Email         :   782802112@qq.com
 * @Time          :   2016-12-22 10:00:22
 * $Id: Video.php 62100 2016-12-22 10:00:22Z hunter.fang $
 */

if (!defined('BASE_PATH')) exit('Access Denied!');

class Sona_V1_VideoController extends Api_BaseController {
    
    /**
     * 
     * 1.文件方式创建视频
     * video/create
     * method: post
     * 特别注意：请求头中需声明Content-Type为multipart/form-data
     * curl 'http://rock.advertiser.housead.com/v1/video/create' -H 'Authorization: Bearer NCwxNDgyNDgzMTM2LDZlMTZkYWU0ZWVjN2E5YWM4MzYwNWE5N2Q2OTUzYjhhM2ZhYTIwNzA=' -H 'Content-Type: multipart/form-data' -F 'advertiser_id=4' -F 'video_file=@test.mp4' -F 'video_signature=0a5da25e140c1f782fa3f4d04f4854b7' -F 'outer_video_id=2'
     * {"code":0,"msg":"","data":{"video_id":"4:0a5da25e140c1f782fa3f4d04f4854b7","width":640,"height":360,"frames":30,"size":472106,"file_format":"mp4","video_signature":"0a5da25e140c1f782fa3f4d04f4854b7","outer_video_id":2}}
     */
    public function createAction() {
        $advertiser_id = $this->getPost('advertiser_id');
        if(empty($advertiser_id)){
            $this->sonaOutput(31000, 'advertiser_id:lack required parameters');
        }
        $this->checkSonaToken($advertiser_id);
        
        if(empty($_FILES['video_file'])){
            $this->sonaOutput(31000, 'video_file:lack required parameters');
        }
        
        $video_signature = $this->getPost('video_signature');
        if(empty($video_signature)){
            $this->sonaOutput(31000, 'video_signature:lack required parameters');
        }
        
        if(!is_file($_FILES['video_file']['tmp_name'])){
            $this->sonaOutput(31000, 'video_file tmp_name:lack required parameters');
        }
        
        $check_sign = md5_file($_FILES['video_file']['tmp_name']);
        
        if($check_sign != $video_signature){
            $this->sonaOutput(31014, 'video_signature:file signature check failed  ');
        }
        
        $video_id = $advertiser_id . ":" . $check_sign;
        $videoInfo = Advertiser_Service_VideoModel::getByVideoid($video_id);
        if($videoInfo){
            $this->sonaOutput(0, '',array(
                'video_id'=>$videoInfo['video_id'],
                'width'=>intval($videoInfo['width']), 
                'height'=>intval($videoInfo['height']), 
                'frames'=>intval($videoInfo['frames']),  
                'size'=>intval($videoInfo['size']), 
                'file_format'=>$videoInfo['file_format'], 
                'video_signature'=>$videoInfo['signature'], 
                'outer_video_id'=>intval($videoInfo['outer_video_id'])
            ));
        }
        
        //调用方投放单元id
        $outer_video_id = intval($this->getPost('outer_video_id'));
        if($outer_video_id){
            if(Advertiser_Service_VideoModel::getBy(array('advertiser_uid'=>$advertiser_id, 'outer_video_id'=>$outer_video_id))){
                $this->sonaOutput(31027, 'outer_video_id:client outer key mapping created before service ');
            }
        }
        
        
        list($videoInfo, ) = Common::video_info($_FILES['video_file']['tmp_name']);
        $videowidth = intval($videoInfo['width']);
        $videoheight = intval($videoInfo['height']);
        $videoframes = intval($videoInfo['frames']);
        $size = filesize($_FILES['video_file']['tmp_name']);
        $ext = strtolower(substr(strrchr($_FILES['video_file']['name'], '.'), 1));
        
        $ret = Common::upload('video_file', 'sonadelivery', array());
        if($ret['code'] != 0){
            $this->sonaOutput(30000, 'unknown internal error, please try again later');
        }
        
        $info = array();
        $info['advertiser_uid'] = $advertiser_id;
        $info['video_id'] = $video_id;
        $info['video_name'] = $_FILES['video_file']['name'];
        $info['url'] = $ret['data'];
        $info['signature'] = $check_sign;
        $info['width'] = $videowidth;
        $info['height'] = $videoheight;
        $info['frames'] = $videoframes;
        $info['size'] = $size;
        $info['file_format'] = $ext;
        $info['outer_video_id'] = $outer_video_id;
        $id = Advertiser_Service_VideoModel::addVideo($info);
        
        if (!$id){
	        $this->sonaOutput(30000, 'unknown internal error, please try again later');
	    }
        
        $this->sonaOutput(0, '',array('video_id'=>$video_id,'width'=>$videowidth, 'height'=>$videoheight, 'frames'=>$videoframes,  'size'=>$size, 'file_format'=>$ext, 'video_signature'=>$check_sign, 'outer_video_id'=>$outer_video_id));
    }
    
    /**
     * 2.读取视频信息
     * video/read
     * method: get
     * curl -G 'http://rock.advertiser.housead.com/v1/video/read' -H 'Authorization: Bearer NCwxNDgyNDgzMTM2LDZlMTZkYWU0ZWVjN2E5YWM4MzYwNWE5N2Q2OTUzYjhhM2ZhYTIwNzA=' -d 'advertiser_id=4' -d 'video_id=4:0a5da25e140c1f782fa3f4d04f4854b7'
     * {"code":0,"msg":"","data":{"video_id":"4:0a5da25e140c1f782fa3f4d04f4854b7","width":640,"height":360,"frames":30,"size":472106,"file_format":"mp4","video_signature":"0a5da25e140c1f782fa3f4d04f4854b7","outer_video_id":2}}
     */
    public function readAction() {
        $advertiser_id = $this->getGet('advertiser_id');
        if(empty($advertiser_id)){
            $this->sonaOutput(31000, 'advertiser_id:lack required parameters');
        }
        $this->checkSonaToken($advertiser_id);
        
        $video_id = $this->getGet('video_id');
        if(empty($video_id)){
            $this->sonaOutput(31000, 'video_id:lack required parameters');
        }
        
        $videoInfo = Advertiser_Service_VideoModel::getBy(array('advertiser_uid'=>$advertiser_id, 'video_id'=>$video_id));
        if(empty($videoInfo)){
            $this->sonaOutput(31010, 'video_id:object operated not exist');
        }
        
        $outerImageInfo = array();
        $outerImageInfo['video_id'] = $videoInfo['video_id'];
        $outerImageInfo['width'] = intval($videoInfo['width']);
        $outerImageInfo['height'] = intval($videoInfo['height']);
        $outerImageInfo['frames'] = intval($videoInfo['frames']);
        $outerImageInfo['size'] = intval($videoInfo['size']);
        $outerImageInfo['file_format'] = $videoInfo['file_format'];
        $outerImageInfo['video_signature'] = $videoInfo['signature'];
        $outerImageInfo['outer_video_id'] = intval($videoInfo['outer_video_id']);
        $this->sonaOutput(0, '', $outerImageInfo);
    }
    
}


