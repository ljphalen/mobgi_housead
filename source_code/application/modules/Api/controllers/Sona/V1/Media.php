<?php

/**
 * @Encoding      :   UTF-8
 * @Author       :   hunter.fang
 * @Email         :   782802112@qq.com
 * @Time          :   2016-12-19 17:14:55
 * $Id: Media.php 62100 2016-12-19 17:14:55Z hunter.fang $
 */

if (!defined('BASE_PATH')) exit('Access Denied!');

class Sona_V1_MediaController extends Api_BaseController {
    
    /**
     * 
     * 1.文件方式创建图片
     * image/create
     * method: post
     * curl 'http://rock.advertiser.housead.com/v1/image/create' -H 'Authorization: Bearer NCwxNDgyMTM3ODA3LDZjZTc1MzJkMDNhZjBlY2IwZDFiODMxOTBjYmYyY2Q0MTUxZTNiMjI=' -H 'Content-Type: multipart/form-data' -F 'advertiser_id=4' -F 'image_file=@test.jpg' -F 'image_signature=4f71a5b6e71c6abf67a2b16c0b9017a8'
     * {"code":0,"msg":"","data":{"unit_id":"24","outer_unit_id":4}}
     */
    public function createAction() {
//        $advertiser_id = $this->getPost('advertiser_id');
//        if(empty($advertiser_id)){
//            $this->sonaOutput(31000, 'advertiser_id:lack required parameters');
//        }
//        $this->checkSonaToken($advertiser_id);
//        
//        var_dump($_POST);
//        var_dump($_FILES);
    }
    
}

