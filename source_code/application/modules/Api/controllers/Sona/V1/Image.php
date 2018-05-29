<?php

/**
 * @Encoding      :   UTF-8
 * @Author       :   hunter.fang
 * @Email         :   782802112@qq.com
 * @Time          :   2016-12-19 15:35:52
 * $Id: Image.php 62100 2016-12-19 15:35:52Z hunter.fang $
 */

if (!defined('BASE_PATH')) exit('Access Denied!');

class Sona_V1_ImageController extends Api_BaseController {
    
    /**
     * 
     * 1.文件方式创建图片
     * image/create
     * method: post
     * 特别注意：请求头中需声明Content-Type为multipart/form-data
     * curl 'http://rock.advertiser.housead.com/v1/image/create' -H 'Authorization: Bearer NCwxNDgyNDgzMTM2LDZlMTZkYWU0ZWVjN2E5YWM4MzYwNWE5N2Q2OTUzYjhhM2ZhYTIwNzA=' -H 'Content-Type: multipart/form-data' -F 'advertiser_id=4' -F 'image_file=@test.jpg' -F 'image_signature=9c763b2153af834585d57902859b1ca2' -F 'outer_image_id=23'
     * {"code":0,"msg":"","data":{"image_id":"4:9c763b2153af834585d57902859b1ca2","width":801,"height":532,"size":69419,"file_format":"jpg","image_signature":"9c763b2153af834585d57902859b1ca2","outer_image_id":23}}
     * curl 'http://rock.advertiser.housead.com/v1/image/create' -H 'Authorization: Bearer NCwxNDgyNDgzMTM2LDZlMTZkYWU0ZWVjN2E5YWM4MzYwNWE5N2Q2OTUzYjhhM2ZhYTIwNzA=' -H 'Content-Type: multipart/form-data' -F 'advertiser_id=4' -F 'image_file=@960_640.jpg' -F 'image_signature=601690dfb3a9d5ccb367fc85f7ee2296'
     * curl 'http://rock.advertiser.housead.com/v1/image/create' -H 'Authorization: Bearer NCwxNDgyNDgzMTM2LDZlMTZkYWU0ZWVjN2E5YWM4MzYwNWE5N2Q2OTUzYjhhM2ZhYTIwNzA=' -H 'Content-Type: multipart/form-data' -F 'advertiser_id=4' -F 'image_file=@640_960.jpg' -F 'image_signature=9da12013a6a18271f97f50021a349387'
     * curl 'http://rock.advertiser.housead.com/v1/image/create' -H 'Authorization: Bearer NCwxNDgyNDgzMTM2LDZlMTZkYWU0ZWVjN2E5YWM4MzYwNWE5N2Q2OTUzYjhhM2ZhYTIwNzA=' -H 'Content-Type: multipart/form-data' -F 'advertiser_id=4' -F 'image_file=@100_100.jpg' -F 'image_signature=4d7d24782ac1e2df763d3658a9209212'
     */
    public function createAction() {
        $advertiser_id = $this->getPost('advertiser_id');
        if(empty($advertiser_id)){
            $this->sonaOutput(31000, 'advertiser_id:lack required parameters');
        }
        $this->checkSonaToken($advertiser_id);
        
        if(empty($_FILES['image_file'])){
            $this->sonaOutput(31000, 'image_file:lack required parameters');
        }
        
        $image_signature = $this->getPost('image_signature');
        if(empty($image_signature)){
            $this->sonaOutput(31000, 'image_signature:lack required parameters');
        }
        
        if(!is_file($_FILES['image_file']['tmp_name'])){
            $this->sonaOutput(31000, 'image_file tmp_name:lack required parameters');
        }
        
        $check_sign = md5_file($_FILES['image_file']['tmp_name']);
        
        if($check_sign != $image_signature){
            $this->sonaOutput(31014, 'image_signature:file signature check failed  ');
        }
        
        $image_id = $advertiser_id.":".$check_sign;
        $imageInfo = Advertiser_Service_ImageModel::getByImageid($image_id);
        if($imageInfo){
            $this->sonaOutput(0, '',array(
                'image_id'=>$imageInfo['image_id'],
                'width'=>  intval($imageInfo['width']), 
                'height'=>intval($imageInfo['height']), 
                'size'=>intval($imageInfo['size']), 
                'file_format'=>$imageInfo['file_format'], 
                'image_signature'=>$imageInfo['signature'], 
                'outer_image_id'=>intval($imageInfo['outer_image_id'])
            ));
        }
        
        //调用方投放单元id
        $outer_image_id = intval($this->getPost('outer_image_id'));
        if($outer_image_id){
            if(Advertiser_Service_ImageModel::getBy(array('advertiser_uid'=>$advertiser_id, 'outer_image_id'=>$outer_image_id))){
                $this->sonaOutput(31027, 'outer_image_id:client outer key mapping created before service ');
            }
        }
        
        $imagesizeInfo =  getimagesize($_FILES['image_file']['tmp_name']);
        $imagewidth = $imagesizeInfo[0];
        $imageheight = $imagesizeInfo[1];
        $size = filesize($_FILES['image_file']['tmp_name']);
        $ext = strtolower(substr(strrchr($_FILES['image_file']['name'], '.'), 1));
        
        $ret = Common::upload('image_file', 'sonadelivery', array());
        if($ret['code'] != 0){
            $this->sonaOutput(30000, 'unknown internal error, please try again later');
        }
        
        $info = array();
        $info['advertiser_uid'] = $advertiser_id;
        $info['image_id'] = $image_id;
        $info['image_name'] = $_FILES['image_file']['name'];
        $info['url'] = $ret['data'];
        $info['signature'] = $check_sign;
        $info['width'] = $imagewidth;
        $info['height'] = $imageheight;
        $info['size'] = $size;
        $info['file_format'] = $ext;
        $info['outer_image_id'] = $outer_image_id;
        
        $id = Advertiser_Service_ImageModel::addImage($info);
        
        if (!$id){
	        $this->sonaOutput(30000, 'unknown internal error, please try again later');
	    }
        
        $this->sonaOutput(0, '',array('image_id'=>$image_id,'width'=>$imagewidth, 'height'=>$imageheight, 'size'=>$size, 'file_format'=>$ext, 'image_signature'=>$check_sign, 'outer_image_id'=>$outer_image_id));
    }
    
    /**
     * 2.读取图片信息
     * image/read
     * method: get
     * curl -G 'http://rock.advertiser.housead.com/v1/image/read' -H 'Authorization: Bearer NCwxNDgyNDgzMTM2LDZlMTZkYWU0ZWVjN2E5YWM4MzYwNWE5N2Q2OTUzYjhhM2ZhYTIwNzA=' -d 'advertiser_id=4' -d 'image_id=4:9c763b2153af834585d57902859b1ca2'
     * {"code":0,"msg":"","data":{"image_id":"4:9c763b2153af834585d57902859b1ca2","width":801,"height":532,"size":69419,"file_format":"jpg","image_signature":"9c763b2153af834585d57902859b1ca2","outer_image_id":23}}
     */
    public function readAction() {
        $advertiser_id = $this->getGet('advertiser_id');
        if(empty($advertiser_id)){
            $this->sonaOutput(31000, 'advertiser_id:lack required parameters');
        }
        $this->checkSonaToken($advertiser_id);
        
        $image_id = $this->getGet('image_id');
        if(empty($image_id)){
            $this->sonaOutput(31000, 'image_id:lack required parameters');
        }
        
        $imageInfo = Advertiser_Service_ImageModel::getBy(array('advertiser_uid'=>$advertiser_id, 'image_id'=>$image_id));
        if(empty($imageInfo)){
            $this->sonaOutput(31010, 'image_id:object operated not exist');
        }
        
        $outerImageInfo = array();
        $outerImageInfo['image_id'] = $imageInfo['image_id'];
        $outerImageInfo['width'] = intval($imageInfo['width']);
        $outerImageInfo['height'] = intval($imageInfo['height']);
        $outerImageInfo['size'] = intval($imageInfo['size']);
        $outerImageInfo['file_format'] = $imageInfo['file_format'];
        $outerImageInfo['image_signature'] = $imageInfo['signature'];
        $outerImageInfo['outer_image_id'] = intval($imageInfo['outer_image_id']);
        $this->sonaOutput(0, '', $outerImageInfo);
    }
}

