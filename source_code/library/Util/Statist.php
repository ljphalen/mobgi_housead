<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
/**
 * Json key constants
 *
 * @package utility
 */
class Util_Statist {
    const INTERSRC = 'intersrc';
    const T_BI = 't_bi';
    
    private static $downloadLevel = 2;
    
    public static function getSubjectListUrl($src = '') {
        $intersrc = self::getIntersrc('SUBJlist', $src);
        return '/subject/index?'.self::getStatistStr($intersrc);
    }
    
    public static function getSubjectDetailUrl($id, $src = '') {
        $intersrc = self::getIntersrc('SUBJdetail'.$id, $src);
        return '/subject/detail?id='.$id.'&'.self::getStatistStr($intersrc);
    }
    
    public static function getCategoryDetailUrl($id, $pid, $src = '') {
        if ($id) {
        	$object = 'CATG'.$pid.'sub'.$id;
        } else {
            $object = 'mainCATG'.$pid;
        }
        $intersrc = self::getIntersrc($object, $src);
        return '/category/detail/?id='.$id.'&pid='.$pid.'&'.self::getStatistStr($intersrc);
    }
    
    public static function getHomeUrl() {
        $intersrc = self::getIntersrc('home');
        return '/index?'.self::getStatistStr($intersrc);
    }
    
    public static function getCategoryListUrl() {
        $intersrc = self::getIntersrc('CATGlist');
        return '/Category/index?'.self::getStatistStr($intersrc);
    }
    
    public static function getOlGameListUrl() {
        $intersrc = self::getIntersrc('olglist');
        return '/Channel/webgame?'.self::getStatistStr($intersrc);
    }
    
    public static function getPcGameListUrl() {
        $intersrc = self::getIntersrc('pcglist');
        return '/Channel/singlegame?'.self::getStatistStr($intersrc);
    }
    
    public static function getRankListUrl() {
        $intersrc = self::getIntersrc('ranklist');
        return '/rank/index?'.self::getStatistStr($intersrc);
    }
    
    public static function getAboutUrl() {
        $intersrc = self::getIntersrc('about');
        return '/Contact/index?'.self::getStatistStr($intersrc);
    }
    
    public static function getFeedbackUrl() {
        $intersrc = self::getIntersrc('feedback');
        return '/Feedback/index?'.self::getStatistStr($intersrc);
    }
    
    public static function getClientUrl() {
        $intersrc = self::getIntersrc('client');
        return '/Contact/client?'.self::getStatistStr($intersrc);
    }
    
    public static function getClientDlUrl($link) {
        if (!trim($link)) {
        	return '';
        }
        $intersrc = self::getIntersrc('clientdl');
        return '/index/linkRedirect/?url='.urlencode($link).'&'.self::getStatistStr($intersrc);
    }
    
    public static function getFaqUrl() {
        $intersrc = self::getIntersrc('faq');
        return '/Feedback/faq?'.self::getStatistStr($intersrc);
    }
    
    /**
     * 下载链接，需要重定向
     */
    public static function getDownloadUrl($gameId, $url, $index = null, $src = '') {
        if (!trim($url)) {
            return '';
        }
        if ($index) {
            $object = 'I'.$index.'_startdlGID'.$gameId;
        } else {
            $object = 'startdlGID'.$gameId;
        }
        $intersrc = self::getIntersrc($object, $src, self::$downloadLevel);
        return '/index/linkRedirect/?url='.urlencode($url).'&'.self::getStatistStr($intersrc);
    }
    
    /**
     * 下载链接，需要重定向
     */
    public static function getDownloadTSearchUrl($gameId, $url) {
        if (!trim($url)) {
            return '';
        }
        $intersrc = 'tsearch_startdlGID'.$gameId;
        return '/index/linkRedirect/?url='.urlencode($url).'&'.self::getStatistStr($intersrc);
    }
    
    public static function getRecommendGameList($id, $src) {
        $intersrc = self::getIntersrc('RCMDlist'.$id, $src);
        return '/channel/recommendGame?id='.$id.'&'.self::getStatistStr($intersrc);
    }


    /**
     * 外链，需要重定向
     */
    public static function getOutLinkUrl($link, $src = '') {
        if (!trim($link)) {
            return '';
        }
        $intersrc = self::getIntersrc('link', $src);
        return '/index/linkRedirect/?url=' . urlencode($link) . '&'.self::getStatistStr($intersrc);
    }
    
    public static function geSearchUrl($src='') {
        $intersrc = self::getIntersrc('search', $src);
        return '/Search/index?'.self::getStatistStr($intersrc);
    }
    
    public static function getGameDetailUrl($gameId, $src='', $index = null) {
        if ($index) {
        	$object = 'I'.$index.'_gamedetail'.$gameId;
        } else {
            $object = 'gamedetail'.$gameId;
        }
        $intersrc = self::getIntersrc($object, $src);
        return '/index/detail?id='.$gameId.'&'.self::getStatistStr($intersrc);
    }
    
    public static function getActivityDetailUrl($id, $src='') {
        $intersrc = self::getIntersrc('eventdetail'.$id, $src);
        return '/activity/detail?id='.$id.'&'.self::getStatistStr($intersrc);
    }
    
    public static function getActivityListUrl($src) {
        $intersrc = self::getIntersrc('eventlist', $src);
        return '/activity/index?'.self::getStatistStr($intersrc);
    }
    
    public static function getZXListUrl($src) {
        $intersrc = self::getIntersrc('ZXlist', $src);
        return '/news/index?'.self::getStatistStr($intersrc);
    }
    
    public static function getZXDetailUrl($id) {
        $intersrc = self::getIntersrc('ZX'.$id);
        return '/News/detail/?id='.$id.'&'.self::getStatistStr($intersrc);
    }
    
    public static function getRankDetailUrl($rankType, $src) {
        $intersrc = self::getIntersrc($rankType.'detail', $src);
        return '/rank/detail/?rankType='.$rankType.'&'.self::getStatistStr($intersrc);
    }
    
    private static function getStatistStr($intersrc) {
        $intersrcArr = explode('^^', $intersrc);
        if ($intersrcArr[1]) {
        	return self::INTERSRC.'='.$intersrcArr[0];
        } else {
            $tbi = Util_Cookie::get('GAME-SOURCE', false);
            return self::INTERSRC.'='.$intersrcArr[0].'&'.self::T_BI.'='.$tbi;
        }
    }
    
    public static function getCurStatistStr($object = '') {
        $intersrc = Util_Filter::get(self::INTERSRC);
        if ($object) {
        	$intersrc = $intersrc.'_'.$object;
        }
        $tbi = Util_Cookie::get('GAME-SOURCE', false);
        return self::INTERSRC.'='.$intersrc.'&'.self::T_BI.'='.$tbi;
    }
    
    private static function getIntersrc($object, $src='', $level = 1) {
        $spit = '_';
        if (substr($object, 0, 1) == 'I') {
            $spit = '';
        }
        
        if ($src) {
            if ($src != 'home_tooltip' && substr($src, 0, 4) == 'home') {
                return $src.$spit.$object.'^^1';
            }
        	return $src.$spit.$object;
        }
        
        $curIntersrc = Util_Filter::get(self::INTERSRC);
        if (!$curIntersrc) {
        	return 'home'.$spit.$object;
        }

        if ($level == self::$downloadLevel) {
        	return $curIntersrc.$spit.$object;
        }
        
        $lastPos = null;
        $off = null;
        do{
            $lastPos = strrpos($curIntersrc, "_", $off);
            if ($lastPos === false) {
                break;
            }
            $off = $lastPos - strlen($curIntersrc) - 1;
            $level --;
        } while ($level);
        if ($lastPos !== false) {
            return substr($curIntersrc, $lastPos + 1).$spit.$object;
        }
        return $curIntersrc.$spit.$object;
    }
}
