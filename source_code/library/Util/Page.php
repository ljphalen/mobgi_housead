<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
/**
 * 
 * 分页类
 *
 * the last known user to change this file in the repository  <$LastChangedBy: wangsc $>
 * @author wangsc <igglonely@gmail.com>
 * @version $Id$
 * @package
 */
class Util_Page {
    
	
    /**
     * 
     * 分页方法
     * @param int $count
     * @param int $page
     * @param int $maxPage
     * @param string $url
     * @param int $max
     * @param string $ajaxCallBack
     * @return string
     */
	
    static public function show_page($count, $page, $perPage, $url, $split='=', $ajaxCallBack = '') {
        list($count, $page, $perPage) = array(intval($count), intval($page), intval($perPage));
        (!$page || $page < 1) && $page = 1; 
        if ($perPage < 1 || $count <= $perPage) return '';
        $maxPage = ceil($count / $perPage);
        $page > $maxPage && $page = $maxPage;
       
        $ajaxurl = $ajaxCallBack ? " onclick=\"return $ajaxCallBack(this.href);\"" : '';
        $mao = '';
        if (stripos($url, '#')){
	        list($url, $mao) = explode('#', $url);
	        $mao && $mao = '#' . $mao;
	    }
        $pages = '<div class="pages">';
        $preArrow = $nextArrow = $firstPage = $lastPage = '';
        if ($maxPage > 7) {
            list($pre, $next) = array($page - 1, $page + 1);
            $page > 1 && $preArrow = "<a class=\"pages_pre\" href=\"{$url}page{$split}{$pre}$mao\"{$ajaxurl}>&#x4E0A;&#x4E00;&#x9875;</a>";
            $page < $maxPage && $nextArrow = "<a class=\"pages_next\" href=\"{$url}page{$split}{$next}$mao\"{$ajaxurl}>&#x4E0B;&#x4E00;&#x9875;</a>";      
        }
        $page != 1 && $firstPage = "<a href=\"{$url}page{$split}1$mao\"{$ajaxurl}>" . (($maxPage > 7 && $page - 3 > 1) ? '1...</a>' : '1</a>');
        $page != $maxPage && $lastPage = "<a href=\"{$url}page{$split}{$maxPage}$mao\"{$ajaxurl}>" . (($maxPage > 7 && $page + 3 < $maxPage) ? "...$maxPage</a>" : "$maxPage</a>");
        
        list($tmpPages, $preFlag, $nextFlag) = array('', 0, 0);
        $leftStart = ($maxPage - $page >= 3) ? $page - 2 : $page - (5 - ($maxPage - $page));
        for ($i = $leftStart; $i < $page; $i++) {
            if ($i <= 1) continue;
            $tmpPages .= "<a href=\"{$url}page{$split}$i$mao\"{$ajaxurl}>$i</a>";
            $preFlag++;
        }
        $tmpPages .= "<b>$page</b>";
        $nextFlag = 4 - $preFlag + (!$firstPage ? 1 : 0);
        if ($page < $maxPage) {
            for ($i = $page + 1; $i < $maxPage && $i <= $page + $nextFlag; $i++) {
                $tmpPages .= "<a href=\"{$url}page{$split}$i$mao\"{$ajaxurl}>$i</a>";
            }
        }
        $pages .= $preArrow . $firstPage . $tmpPages . $lastPage . $nextArrow;
        $jsString = "var page=(value>$maxPage) ? $maxPage : value; " . ($ajaxurl ? "$ajaxCallBack('{$url}page{$split}'+page);" : " location='{$url}page{$split}'+page+'{$mao}';") . " return false;";
        $maxPage > 7 && $pages .= "<div class=\"fl\">&#x5230;&#x7B2C;</div><input type=\"text\" size=\"3\" onkeydown=\"javascript: if(event.keyCode==13){var value = parseInt(this.value); $jsString}\"><div class=\"fl\">&#x9875;</div><button onclick=\"javascript:var value = parseInt(this.previousSibling.previousSibling.value); $jsString\">&#x786E;&#x8BA4;</button>";
        $pages .= '</div>';
        return $pages;
    }
  
}