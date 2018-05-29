<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
/**
 * Created by PhpStorm.
 * User: kyle.ke
 * Date: 2018/3/19
 * Time: 14:41
 */
class MobgiMarket_Service_BaseModel extends Common_Service_Base{

    public static function getUserAuthById($id)
    {
        $dao = self::getMarketDao('UserAuth');
        $result = $dao->get($id);
        return $result;
    }

    public static function getUserAuthByUserId($userId)
    {
        $dao = self::getMarketDao('UserAuth');
        $where['user_id'] = $userId;
        $result = $dao->getsBy($where);
        return $result;
    }

    public static function getImageByImageId($imageId)
    {
        $dao = self::getMarketDao('Image');
        $where['image_id'] = $imageId;
        $result = $dao->getBy($where);
        return $result;
    }

    public static function getVideoByVideoId($videoId)
    {
        $dao = self::getMarketDao('Video');
        $where['video_id'] = $videoId;
        $result = $dao->getBy($where);
        return $result;
    }
}